<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create permissions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z]+(\s[a-z]+)*$/',
                Rule::unique('permissions', 'name'),
            ],
            'module' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z]+(\s[a-z]+)*$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da permissão é obrigatório.',
            'name.unique' => 'Esta permissão já existe.',
            'name.regex' => 'O nome deve conter apenas letras minúsculas e espaços.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'module.required' => 'O módulo é obrigatório.',
            'module.regex' => 'O módulo deve conter apenas letras minúsculas e espaços.',
            'module.max' => 'O módulo não pode ter mais de 100 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtolower(trim($this->name)),
            'module' => strtolower(trim($this->module)),
        ]);
    }
}
