<?php

namespace App\Http\Requests\Note;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'mood' => ['required', 'string', 'max:64'],
            'energy_level' => ['required', 'integer', 'min:1', 'max:10'],
            'stress_level' => ['required', 'integer', 'min:1', 'max:10'],
            'sleep_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'water_intake' => ['required', 'numeric', 'min:0', 'max:20'],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
