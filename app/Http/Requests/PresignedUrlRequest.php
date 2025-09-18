<?php

namespace App\Http\Requests;

class PresignedUrlRequest extends BaseAuthenticatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file_name' => 'required|string|max:255',
            'content_type' => 'required|string|max:100',
            'directory' => 'nullable|string|max:255',
        ];
    }
}
