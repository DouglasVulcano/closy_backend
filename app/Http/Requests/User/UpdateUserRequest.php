<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseAuthenticatedRequest;

class UpdateUserRequest extends BaseAuthenticatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'sometimes|string|max:50',
            'celular' => 'sometimes|string|max:20',
            'profile_picture' => 'sometimes|string|max:255',
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
