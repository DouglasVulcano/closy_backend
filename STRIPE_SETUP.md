# Configuração do Stripe - Sistema de Assinatura

## Variáveis de Ambiente Necessárias

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Stripe Configuration
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
STRIPE_WEBHOOK_TOLERANCE=300

# Cashier Configuration
CASHIER_CURRENCY=brl
CASHIER_CURRENCY_LOCALE=pt_BR
CASHIER_LOGGER=
```

## Configuração no Stripe Dashboard

### 1. Criar Produtos e Preços

No Stripe Dashboard, crie os seguintes produtos:

#### Plano Starter
- Nome: "Starter"
- Preço: R$ 29,90/mês
- ID do Preço: `price_starter_monthly` (substitua no seeder)

#### Plano Pro
- Nome: "Pro"
- Preço: R$ 59,90/mês
- ID do Preço: `price_pro_monthly` (substitua no seeder)

### 2. Configurar Webhooks

1. Acesse a seção "Webhooks" no Stripe Dashboard
2. Clique em "Add endpoint"
3. URL do endpoint: `https://seu-dominio.com/api/v1/stripe/webhook`
4. Selecione os eventos:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`

## Comandos para Configuração

```bash
# Executar migrations
php artisan migrate

# Popular tabela de planos
php artisan db:seed --class=PlanSeeder

# Publicar configurações do Cashier (opcional)
php artisan vendor:publish --tag="cashier-config"
```

## Estrutura das Tabelas

### Tabela `plans`
- `id`: ID único do plano
- `name`: Nome do plano (Starter, Pro)
- `price`: Preço em decimal
- `stripe_price_id`: ID do preço no Stripe
- `role`: Role associada (STARTER, PRO)
- `active`: Se o plano está ativo
- `description`: Descrição do plano
- `features`: JSON com as funcionalidades

### Tabela `users` (colunas adicionadas)
- `stripe_id`: ID do cliente no Stripe
- `pm_type`: Tipo do método de pagamento
- `pm_last_four`: Últimos 4 dígitos do cartão
- `trial_ends_at`: Data de fim do período de teste

### Tabela `subscriptions`
- Gerenciada pelo Laravel Cashier
- Armazena informações das assinaturas

## Rotas Disponíveis

### Planos
- `GET /api/v1/plans` - Listar planos disponíveis
- `GET /api/v1/plans/{id}` - Detalhes de um plano

### Assinaturas (requer autenticação)
- `GET /api/v1/subscriptions/current` - Assinatura atual do usuário
- `POST /api/v1/subscriptions/cancel` - Cancelar assinatura
- `POST /api/v1/subscriptions/resume` - Reativar assinatura

### Stripe (checkout e portal requerem autenticação)
- `POST /api/v1/stripe/checkout` - Criar sessão de checkout
- `POST /api/v1/stripe/portal` - Acessar portal do cliente
- `POST /api/v1/stripe/webhook` - Webhook do Stripe (sem autenticação)

## Middleware de Roles

O middleware `role` foi registrado e pode ser usado nas rotas:

```php
Route::middleware(['auth:sanctum', 'role:PRO'])->group(function () {
    // Rotas apenas para usuários PRO
});
```

## Próximos Passos

1. Configure as chaves do Stripe no arquivo `.env`
2. Atualize os IDs dos preços no `PlanSeeder.php`
3. Configure os webhooks no Stripe Dashboard
4. Teste o fluxo de assinatura
5. Implemente a lógica de negócio específica para cada role