<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roleId = $this->route('role')->id;

        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $roleId,
            'description' => 'nullable|string|max:500',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'description' => 'descrição',
            'permissions' => 'permissões',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.unique' => 'Já existe um role com este nome.',
            'name.max' => 'O nome deve ter no máximo 255 caracteres.',
            'description.max' => 'A descrição deve ter no máximo 500 caracteres.',
            'permissions.array' => 'As permissões devem ser uma lista.',
            'permissions.*.exists' => 'Uma ou mais permissões selecionadas são inválidas.',
        ];
    }
}
