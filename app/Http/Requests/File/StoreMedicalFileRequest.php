<?php

namespace App\Http\Requests\File;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreMedicalFileRequest extends FormRequest
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
            'file' => [
                'required',
                File::types(['pdf', 'docx'])->max(10 * 1024),
                'extensions:pdf,docx',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Plik jest wymagany.',
            'file.file' => 'Plik jest wymagany.',
            'file.mimes' => 'Plik musi być w formacie .pdf lub .docx.',
            'file.mimetypes' => 'Plik musi być poprawnym dokumentem PDF lub DOCX.',
            'file.extensions' => 'Rozszerzenie pliku musi być zgodne z formatem PDF lub DOCX.',
            'file.max' => 'Plik nie może być większy niż 10MB.',
        ];
    }
}
