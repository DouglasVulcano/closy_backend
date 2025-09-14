<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stripe\StripePlanRequest;
use App\Http\Requests\Stripe\StripePortalRequest;
use App\Models\User;
use App\Services\PlanService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function __construct(private PlanService $planService, private UserService $userService) {}

    /**
     * Create checkout session
     */
    public function checkout(StripePlanRequest $request): JsonResponse
    {
        $user = $this->userService->findById($request->validated()['user_id']);
        $plan = $this->planService->findById($request->validated()['plan_id']);

        if (!$plan->active)
            return response()->json(['message' => 'Plano não está ativo'], 400);

        try {
            $subscription = $user->newSubscription('default', $plan->stripe_price_id);

            // Add trial period if plan has trial days
            if ($plan->trial_days > 0) {
                $subscription->trialDays($plan->trial_days);
            }

            $checkout = $subscription->checkout([
                'success_url' => config('app.frontend_url') . '/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/cancel',
                'metadata' => [
                    'plan_id' => $plan->id,
                    'user_id' => $user->id,
                ],
            ]);

            return response()->json([
                'checkout_url' => $checkout->url
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe checkout error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao criar checkout'
            ], 500);
        }
    }

    /**
     * Create customer portal session
     */
    public function portal(StripePortalRequest $request): JsonResponse
    {
        $user = $this->userService->findById($request->validated()['user_id']);

        if (!$user->subscribed('default'))
            return response()->json(['message' => 'Usuário não possui assinatura ativa'], 400);

        try {
            $url = $user->billingPortalUrl($request->validated()['return_url']);
            return response()->json(['portal_url' => $url]);
        } catch (\Exception $e) {
            Log::error('Stripe portal error: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao criar portal'], 500);
        }
    }

    /**
     * Handle Stripe webhooks
     */
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('stripe.webhook.secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook invalid payload: ' . $e->getMessage());
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook invalid signature: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received: ' . $event->type . ' [' . $event->id . ']');

        // Handle the event
        switch ($event->type) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                Log::info('Processing subscription event: ' . $event->type);
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'checkout.session.completed':
                Log::info('Processing checkout session completed event');
                $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'customer.subscription.deleted':
                Log::info('Processing subscription deleted event');
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                Log::info('Processing payment succeeded event');
                $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                Log::info('Processing payment failed event');
                $this->handlePaymentFailed($event->data->object);
                break;

            default:
                Log::info('Unhandled webhook event type: ' . $event->type);
        }

        Log::info('Stripe webhook processed successfully: ' . $event->type);
        return response('OK', 200);
    }

    /**
     * Handle subscription created/updated
     */
    private function handleSubscriptionUpdated($subscription)
    {
        Log::info('handleSubscriptionUpdated called with subscription: ' . $subscription->id);
        Log::info('Subscription customer: ' . $subscription->customer);
        Log::info('Subscription status: ' . $subscription->status);

        $user = User::where('stripe_id', $subscription->customer)->first();

        if (!$user) {
            Log::error('User not found for customer: ' . $subscription->customer);
            return;
        }

        Log::info('User found: ' . $user->id . ' (current role: ' . $user->role . ')');
        Log::info('Subscription price ID: ' . $subscription->items->data[0]->price->id);

        // Get the plan based on the price ID
        $plan = $this->planService->findByStripePriceId($subscription->items->data[0]->price->id);

        if ($plan) {
            Log::info('Plan found: ' . $plan->name . ' (role: ' . $plan->role . ')');

            if (in_array($subscription->status, ['active', 'trialing'])) {
                // Sync subscription with Cashier
                $cashierSubscription = $user->subscriptions()->updateOrCreate(
                    ['stripe_id' => $subscription->id],
                    [
                        'type' => 'default',
                        'stripe_status' => $subscription->status,
                        'stripe_price' => $subscription->items->data[0]->price->id,
                        'quantity' => $subscription->items->data[0]->quantity ?? 1,
                        'trial_ends_at' => $subscription->trial_end ? \Carbon\Carbon::createFromTimestamp($subscription->trial_end) : null,
                        'ends_at' => null,
                    ]
                );

                // Sync subscription items
                foreach ($subscription->items->data as $item) {
                    $cashierSubscription->items()->updateOrCreate(
                        ['stripe_id' => $item->id],
                        [
                            'stripe_product' => $item->price->product,
                            'stripe_price' => $item->price->id,
                            'quantity' => $item->quantity ?? 1,
                        ]
                    );
                }

                $updateData = ['role' => $plan->role];

                // Set trial_ends_at if subscription is trialing and plan has trial days
                if ($subscription->status === 'trialing' && $plan->trial_days > 0) {
                    $updateData['trial_ends_at'] = now()->addDays($plan->trial_days);
                    Log::info("Setting trial_ends_at to: " . $updateData['trial_ends_at']);
                }

                $user->update($updateData);
                Log::info("User {$user->id} role updated to {$plan->role} and subscription synced via subscription update (status: {$subscription->status})");
            } else {
                Log::warning("Subscription not in valid status, status: {$subscription->status}");
            }
        } else {
            Log::error('Plan not found for price ID: ' . $subscription->items->data[0]->price->id);
        }
    }

    /**
     * Handle subscription deleted
     */
    private function handleSubscriptionDeleted($subscription)
    {
        $user = User::where('stripe_id', $subscription->customer)->first();

        if ($user) {
            // Find and cancel the Cashier subscription
            $cashierSubscription = $user->subscriptions()->where('stripe_id', $subscription->id)->first();

            if ($cashierSubscription) {
                // Mark subscription as cancelled in Cashier
                $cashierSubscription->update([
                    'stripe_status' => 'canceled',
                    'ends_at' => now(),
                ]);
                Log::info("Cashier subscription {$cashierSubscription->id} marked as cancelled");
            }

            $user->update(['role' => 'USER', 'trial_ends_at' => null]);
            Log::info("User {$user->id} role reverted to USER and trial_ends_at cleared");
        }
    }

    /**
     * Handle checkout session completed
     */
    private function handleCheckoutCompleted($session)
    {
        Log::info('handleCheckoutCompleted called with session: ' . $session->id);
        Log::info('Session customer: ' . $session->customer);
        Log::info('Session subscription: ' . ($session->subscription ?? 'null'));

        // Get the customer from the session
        $user = User::where('stripe_id', $session->customer)->first();

        if (!$user) {
            Log::error('User not found for customer: ' . $session->customer);
            return;
        }

        Log::info('User found: ' . $user->id . ' (current role: ' . $user->role . ')');

        // Get the subscription from the session
        if ($session->subscription) {
            Log::info('Retrieving subscription details from Stripe: ' . $session->subscription);

            // Retrieve the subscription details from Stripe
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $subscription = $stripe->subscriptions->retrieve($session->subscription);

            Log::info('Subscription status: ' . $subscription->status);
            Log::info('Subscription price ID: ' . $subscription->items->data[0]->price->id);

            // Get the plan based on the price ID
            $plan = $this->planService->findByStripePriceId($subscription->items->data[0]->price->id);

            if ($plan) {
                Log::info('Plan found: ' . $plan->name . ' (role: ' . $plan->role . ')');

                if (in_array($subscription->status, ['active', 'trialing'])) {
                    // Sync subscription with Cashier
                    $cashierSubscription = $user->subscriptions()->updateOrCreate(
                        ['stripe_id' => $subscription->id],
                        [
                            'type' => 'default',
                            'stripe_status' => $subscription->status,
                            'stripe_price' => $subscription->items->data[0]->price->id,
                            'quantity' => $subscription->items->data[0]->quantity ?? 1,
                            'trial_ends_at' => $subscription->trial_end ? \Carbon\Carbon::createFromTimestamp($subscription->trial_end) : null,
                            'ends_at' => null,
                        ]
                    );

                    // Sync subscription items
                    foreach ($subscription->items->data as $item) {
                        $cashierSubscription->items()->updateOrCreate(
                            ['stripe_id' => $item->id],
                            [
                                'stripe_product' => $item->price->product,
                                'stripe_price' => $item->price->id,
                                'quantity' => $item->quantity ?? 1,
                            ]
                        );
                    }

                    $updateData = ['role' => $plan->role];

                    // Set trial_ends_at if subscription is trialing and plan has trial days
                    if ($subscription->status === 'trialing' && $plan->trial_days > 0) {
                        $updateData['trial_ends_at'] = now()->addDays($plan->trial_days);
                        Log::info("Setting trial_ends_at to: " . $updateData['trial_ends_at']);
                    }

                    $user->update($updateData);
                    Log::info("User {$user->id} role updated to {$plan->role} and subscription synced via checkout completion (status: {$subscription->status})");
                } else {
                    Log::warning("Subscription not in valid status, status: {$subscription->status}");
                }
            } else {
                Log::error('Plan not found for price ID: ' . $subscription->items->data[0]->price->id);
            }
        } else {
            Log::warning('No subscription found in checkout session');
        }
    }

    /**
     * Handle payment succeeded
     */
    private function handlePaymentSucceeded($invoice)
    {
        Log::info('Payment succeeded for invoice: ' . $invoice->id);
    }

    /**
     * Handle payment failed
     */
    private function handlePaymentFailed($invoice)
    {
        Log::warning('Payment failed for invoice: ' . $invoice->id);
    }
}
