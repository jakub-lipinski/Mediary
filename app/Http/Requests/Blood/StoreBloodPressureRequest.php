<?php

namespace App\Http\Requests\Blood;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBloodPressureRequest extends FormRequest
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
            'systolic' => ['required', 'integer', 'min:40', 'max:300'],
            'diastolic' => ['required', 'integer', 'min:30', 'max:200'],
            'date' => ['required', 'date'],
        ];
    }
}
