<?php

namespace App\Http\Requests\Azmmon;

use Illuminate\Foundation\Http\FormRequest;

class startAzmmonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quiz_id' => ['required', 'integer'],
            'duration_minutes' => ['required', 'integer']
        ];
    }
}
