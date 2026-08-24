import { ref } from 'vue';

interface DataTableOptions {
    /** URL do feed JSON — sempre via Wayfinder, ex.: `TesteController.getDataTable().url`. */
    url: string;
    perPage?: number;
}

/**
 * Estado e busca de uma listagem paginada do painel.
 *
 * O feed é buscado por `fetch` (fora do ciclo do Inertia) para que paginar, ordenar ou filtrar
 * não remonte a página nem empilhe entradas no histórico. O contrato do backend é o paginate
 * padrão do Laravel (`{ data, total }`) — ver `App\Traits\DataTableQuery`.
 */
export function useDataTable<T>(options: DataTableOptions) {
    const { url, perPage = 10 } = options;

    const rows = ref<T[]>([]);
    const loading = ref(false);
    const totalRecords = ref(0);
    const first = ref(0);
    const sortField = ref<string | null>(null);
    const sortOrder = ref<number>(0);
    const error = ref<string | null>(null);
    /** Falso até a PRIMEIRA busca terminar — evita o estado vazio piscar antes do primeiro dado. */
    const hasLoadedOnce = ref(false);

    // Guardam a última busca para que `reload()` repita a PÁGINA ATUAL com os mesmos filtros.
    const currentPage = ref(1);
    const lastExtraParams = ref<Record<string, string>>({});

    const buildUrl = (page = 1, extraParams: Record<string, string> = {}) => {
        const params = new URLSearchParams();
        params.set('page', String(page));

        if (sortField.value) {
            params.set('sortField', sortField.value);
        }

        if (sortOrder.value === 1) {
            params.set('sortOrder', 'asc');
        }

        if (sortOrder.value === -1) {
            params.set('sortOrder', 'desc');
        }

        Object.entries(extraParams).forEach(([key, value]) => {
            if (value) {
                params.set(key, value);
            }
        });

        return `${url}?${params.toString()}`;
    };

    const load = async (page = 1, extraParams: Record<string, string> = {}) => {
        loading.value = true;
        error.value = null;
        currentPage.value = page;
        lastExtraParams.value = extraParams;

        try {
            const response = await fetch(buildUrl(page, extraParams), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            rows.value = data.data;
            totalRecords.value = data.total;
            first.value = (page - 1) * perPage;
        } catch (err) {
            console.error('Erro ao carregar dados:', err);
            error.value =
                'Não foi possível carregar os dados. Tente novamente.';
        } finally {
            loading.value = false;
            hasLoadedOnce.value = true;
        }
    };

    /** Repete a página atual com os últimos filtros — usar após salvar ou excluir. */
    const reload = () => load(currentPage.value, lastExtraParams.value);

    const onPage = (event: any, extraParams: Record<string, string> = {}) => {
        load(event.page + 1, extraParams);
    };

    const onSort = (event: any, extraParams: Record<string, string> = {}) => {
        sortField.value = event.sortField;
        sortOrder.value = event.sortOrder;
        load(1, extraParams);
    };

    return {
        rows,
        loading,
        totalRecords,
        first,
        sortField,
        sortOrder,
        error,
        hasLoadedOnce,
        load,
        reload,
        onPage,
        onSort,
    };
}
