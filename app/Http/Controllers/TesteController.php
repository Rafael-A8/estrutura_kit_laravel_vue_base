<?php

namespace App\Http\Controllers;

use App\Enums\StatusTeste;
use App\Http\Requests\TesteDeleteRequest;
use App\Http\Requests\TesteRequest;
use App\Models\Teste;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CONTROLLER DE REFERÊNCIA — copie este arquivo ao criar um módulo novo do painel.
 *
 * As rotas nascem do NOME DO MÉTODO via `AdvancedRoute::controllers()` (routes/route_painel.php).
 * O prefixo é o verbo HTTP e o resto vira o segmento da URL:
 *
 * | Método          | URL                        | Rota nomeada         | Papel                              |
 * |-----------------|----------------------------|----------------------|------------------------------------|
 * | getIndex        | GET  painel/teste          | teste.get.index      | listagem (DataTable)               |
 * | getDataTable    | GET  painel/teste/data-table | teste.get.datatable | feed JSON da listagem             |
 * | getView         | GET  painel/teste/view/{id}| teste.get.view       | visualização de um registro        |
 * | getForm         | GET  painel/teste/form/{id?}| teste.get.form      | formulário em PÁGINA (criar/editar)|
 * | getModelForm    | GET  painel/teste/model-form/{id?} | teste.get.modelform | payload do formulário em MODAL |
 * | postForm        | POST painel/teste/form     | teste.post.form      | grava o cadastro E a edição        |
 * | postDelete      | POST painel/teste/delete   | teste.post.delete    | exclusão                           |
 *
 * PEGADINHA: só vira segmento de URL o parâmetro SEM type-hint. `getView($id)` gera `{id}`;
 * `getView(int $id)` seria tratado como injeção de container e a rota ficaria sem parâmetro.
 * Por isso os ids chegam sem tipo e são convertidos com cast explícito.
 *
 * O controller é MAGRO: valida via FormRequest, chama a Model e devolve a resposta. Qualquer
 * regra de negócio pertence a `App\Models\Teste`.
 */
class TesteController extends Controller
{
    /**
     * Listagem. As linhas NÃO vêm por aqui — a tela busca o feed em `getDataTable()`, então
     * este render carrega só o que a página precisa para montar (opções de filtro, etc).
     */
    public function getIndex(): Response
    {
        return Inertia::render('teste/index', [
            'statusOptions' => StatusTeste::options(),
        ]);
    }

    /**
     * Feed JSON consumido pelo composable `useDataTable` (paginação, ordenação e filtros).
     */
    public function getDataTable(Request $request): JsonResponse
    {
        return (new Teste)->listaTestes();
    }

    /**
     * Visualização somente leitura de um registro.
     */
    public function getView($id): Response
    {
        return Inertia::render('teste/view', [
            'registro' => Teste::query()->findOrFail((int) $id),
        ]);
    }

    /**
     * Formulário em PÁGINA — mesmo formulário para cadastrar (sem id) e editar (com id).
     * Use esta variante quando o formulário for grande demais para uma modal.
     */
    public function getForm($id = null): Response
    {
        return Inertia::render('teste/form', Teste::dadosDoFormulario($id !== null ? (int) $id : null));
    }

    /**
     * Payload do formulário em MODAL — o par backend do componente `ModelForm.vue`, que faz
     * fetch ao abrir. Mesmo contrato do `getForm()`, entregue como JSON em vez de página.
     */
    public function getModelForm($id = null): JsonResponse
    {
        return response()->json(Teste::dadosDoFormulario($id !== null ? (int) $id : null));
    }

    /**
     * Grava cadastro E edição: a presença de `id` no payload validado decide qual dos dois é.
     */
    public function postForm(TesteRequest $request): RedirectResponse
    {
        Teste::salvar($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Registro salvo com sucesso.',
        ]);

        return to_route('teste.get.index');
    }

    /**
     * Exclusão. O bloqueio é decidido pela Model (`podeSerExcluido`), nunca aqui.
     */
    public function postDelete(TesteDeleteRequest $request): RedirectResponse
    {
        $teste = Teste::query()->findOrFail($request->validated('id'));

        if (! $teste->podeSerExcluido()) {
            Inertia::flash('toast', [
                'type' => 'warning',
                'message' => 'Registros ativos não podem ser excluídos. Inative antes de excluir.',
            ]);

            return to_route('teste.get.index');
        }

        $teste->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Registro excluído com sucesso.',
        ]);

        return to_route('teste.get.index');
    }
}
