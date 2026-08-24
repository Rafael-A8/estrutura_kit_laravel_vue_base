<?php

namespace App\Models;

use App\Enums\StatusTeste;
use App\Traits\DataTableQuery;
use Database\Factories\TesteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

/**
 * Model de referência do projeto (Model-First): TODA regra de negócio, query reutilizável e
 * montagem de payload vive aqui. O controller apenas chama estes métodos e devolve a resposta.
 *
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nome', 'descricao', 'status'])]
class Teste extends Model
{
    use DataTableQuery;

    /** @use HasFactory<TesteFactory> */
    use HasFactory;

    protected $table = 'testes';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * Feed JSON do DataTable: aplica os filtros da tela, protege a ordenação com uma whitelist
     * e devolve o paginate. Chamado por `TesteController::getDataTable()`.
     */
    public function listaTestes(): JsonResponse
    {
        $request = request();

        $query = $this->query();

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%'.$request->query('nome').'%');
        }

        if ($request->filled('status')) {
            $query->where('status', (int) $request->query('status'));
        }

        $allowedFields = ['id', 'nome', 'status', 'created_at'];

        $sortField = $request->query('sortField');
        if (! ($sortField && in_array($sortField, $allowedFields, true))) {
            $query->orderByDesc('id');
        }

        return $this->dataTable($request, $query, $allowedFields);
    }

    /**
     * Cria ou atualiza a partir do payload já validado pelo FormRequest. A presença de `id`
     * é o que distingue edição de cadastro — o formulário é o mesmo nos dois casos.
     *
     * @param  array{id?: int|null, nome: string, descricao?: string|null, status: bool}  $dados
     */
    public static function salvar(array $dados): self
    {
        $teste = isset($dados['id'])
            ? static::query()->findOrFail($dados['id'])
            : new self;

        $teste->nome = $dados['nome'];
        $teste->descricao = $dados['descricao'] ?? null;
        $teste->status = $dados['status'];
        $teste->save();

        return $teste;
    }

    /**
     * Payload que abastece o formulário (modal ou página): o registro em edição — ou os defaults
     * de cadastro — junto das opções dos selects. Uma única fonte para os dois modos, então a
     * tela nunca precisa adivinhar o formato de `registro`.
     *
     * @return array{registro: array{id: int|null, nome: string, descricao: string|null, status: bool}, statusOptions: array<int, array{label: string, value: string}>}
     */
    public static function dadosDoFormulario(?int $id = null): array
    {
        $teste = $id ? static::query()->findOrFail($id) : null;

        return [
            'registro' => [
                'id' => $teste?->id,
                'nome' => $teste?->nome ?? '',
                'descricao' => $teste?->descricao,
                'status' => $teste?->status ?? true,
            ],
            'statusOptions' => StatusTeste::options(),
        ];
    }

    /**
     * Regra de domínio de exemplo: registro ativo não é excluído, é inativado antes. Bloqueios
     * de exclusão (vínculos, estado, permissão) moram SEMPRE aqui — nunca dentro do controller.
     */
    public function podeSerExcluido(): bool
    {
        return ! $this->status;
    }
}
