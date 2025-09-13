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
            'name' => 'required|string|max:50',
            'celular' => 'required|string|max:20',
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
