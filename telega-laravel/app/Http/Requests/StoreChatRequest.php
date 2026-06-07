<?php

namespace App\Http\Requests;

use App\Models\Chat;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'type' => [
                'required',
                'string',
                Rule::in(Chat::TYPES),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название чата обязательно',
            'title.string' => 'Название должно быть текстом',
            'title.min' => 'Название слишком короткое (мин. :min символ)',
            'title.max' => 'Название слишком длинное (макс. :max символов)',
            'type.required' => 'Тип чата обязателен',
            'type.in' => 'Недопустимый тип чата',
        ];
    }
}
