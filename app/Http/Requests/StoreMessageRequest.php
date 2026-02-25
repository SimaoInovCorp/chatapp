<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:4000'],
            'target_type' => ['required', 'in:room,user'],
            'target_id' => ['required', 'integer', 'min:1'],
        ];
    }
}