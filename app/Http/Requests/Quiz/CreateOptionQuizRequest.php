<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class CreateOptionQuizRequest extends FormRequest
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
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'level' => 'required|integer',
            'score' => 'required|integer',
            'options' => 'required|array', 
            'options.*.option_number' => 'required|integer',
            'options.*.content' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->all();
        $firstError = $errors[0] ?? 'خطایی رخ داد';

        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json([
                'success' => false,
                'message' => 'ورودی‌های شما معتبر نیستند. ' . $firstError,
                'errors' => $errors,
            ], 422)
        );
    }
}
