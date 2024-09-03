<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class createQuestionRequest extends FormRequest
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
            'category_id' => ['required'],
            'content' => ['required'],
            'level' => ['required'],
            'score' => ['required'],
            'options' => ['required', 'min:2', 'max:10', 
                'options.*' => ['required', 'string', 'max:255'],
            ],
       ];
    }
}
