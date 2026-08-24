<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Um único FormRequest cobre cadastro e edição: `id` presente significa edição — é o que também
 * ajusta a regra `unique`. Validação NUNCA é inline no controller.
 */
class TesteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', Rule::exists('testes', 'id')],
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('testes', 'nome')->ignore($this->input('id')),
            ],
            'descricao' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.integer' => 'O identificador do registro é inválido.',
            'id.exists' => 'O registro informado não foi encontrado.',
            'nome.required' => 'O nome é obrigatório.',
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome não pode ultrapassar 255 caracteres.',
            'nome.unique' => 'Já existe um registro com este nome.',
            'descricao.string' => 'A descrição deve ser um texto.',
            'descricao.max' => 'A descrição não pode ultrapassar 1000 caracteres.',
            'status.required' => 'O status é obrigatório.',
            'status.boolean' => 'O status é inválido.',
        ];
    }
}
