<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:2000'],
            'conversation_id' => ['nullable', 'string', 'uuid', 'max:36'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Wpisz wiadomość.',
            'message.max' => 'Wiadomość może mieć maksymalnie 2000 znaków.',
        ];
    }
}
