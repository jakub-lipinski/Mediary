<?php

namespace App\Http\Requests\Diet;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDietRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:64'],
            'type' => ['required', Rule::in(['klasyczna', 'wegetariańska', 'wegańska', 'bezglutenowa'])],
            'calories' => ['required', 'integer', Rule::in([1000, 1500, 2000, 2500])],
            'meals' => ['required', 'integer', Rule::in([3, 4, 5])],
            'like' => ['nullable', 'string', 'max:1000'],
            'dislike' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'documents' => ['sometimes', 'boolean'],
        ];
    }
}
