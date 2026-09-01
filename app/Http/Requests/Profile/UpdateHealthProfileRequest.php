<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHealthProfileRequest extends FormRequest
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
            'gender' => ['nullable', Rule::in(['Kobieta', 'Mężczyzna', 'Wole nie podawac', 'Wolę nie podawać'])],
            'weight' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'height' => ['nullable', 'numeric', 'min:30', 'max:260'],
            'birthday' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'diseases' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
