<?php

namespace App\Http\Requests\Blood;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBloodResultsRequest extends FormRequest
{
    private const FIELDS = [
        'wbc',
        'rbc',
        'hgb',
        'hct',
        'mcv',
        'mch',
        'mchc',
        'plt',
        'rdw_sd',
        'rdw_cv',
        'pdw',
        'mpv',
        'p_lcr',
        'pct',
        'neu',
        'lym',
        'mono',
        'eos',
        'baso',
        'tsh',
        'ast',
        'alt',
        'bilirubin',
        'alp',
        'ggtp',
        'total_cholesterol',
        'hdl_cholesterol',
        'non_hdl_cholesterol',
        'ldl_cholesterol',
        'triglycerides',
    ];

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
        return collect(self::FIELDS)
            ->mapWithKeys(fn (string $field): array => [$field => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999.999',
            ]])
            ->all();
    }
}
