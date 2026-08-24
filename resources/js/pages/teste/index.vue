<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import ConfirmDialog from 'primevue/confirmdialog';
import DataTable from 'primevue/datatable';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Skeleton from 'primevue/skeleton';
import Tag from 'primevue/tag';
import Toolbar from 'primevue/toolbar';
import { useConfirm } from 'primevue/useconfirm';
import { computed, onMounted, ref, watch } from 'vue';
import TesteController from '@/actions/App/Http/Controllers/TesteController';
import EmptyState from '@/components/EmptyState.vue';
import { useDataTable } from '@/composables/useDataTable';
import { confirmDialogPt } from '@/lib/confirmDialogPt';
import { formatDate } from '@/utils/formatters';
import ModelForm from './ModelForm.vue';

/**
 * LISTAGEM DE REFERÊNCIA — espelha `TesteController::getIndex()`.
 *
 * A pasta tem o nome do controller (`teste/`) e os arquivos têm o nome dos métodos:
 * `index.vue` ↔ getIndex · `form.vue` ↔ getForm · `ModelForm.vue` ↔ getModelForm · `view.vue` ↔ getView.
 *
 * As linhas NÃO vêm como prop do Inertia: o `useDataTable` busca o feed JSON de `getDataTable`,
 * então paginar/ordenar/filtrar não recarrega a página.
 */

interface Teste {
    id: number;
    nome: string;
    descricao: string | null;
    status: boolean;
    created_at: string;
}

interface StatusOption {
    label: string;
    value: string;
}

/** Espelha o default do backend (`DataTableQuery::dataTable`). */
const PER_PAGE = 10;

const props = defineProps<{
    statusOptions: StatusOption[];
}>();

const {
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
} = useDataTable<Teste>({
    url: TesteController.getDataTable().url,
    perPage: PER_PAGE,
});

const filtroNome = ref('');
const filtroStatus = ref<string | null>(null);

/**
 * Verdadeiro desde a digitação até o `load()` do debounce começar. Sem isso existe uma janela
 * em que `loading` ainda é falso com as linhas antigas já descartadas, e o estado vazio pisca
 * entre dois resultados filtrados.
 */
const filtroPendente = ref(false);

const requisicaoEmAndamento = computed(
    () => loading.value || filtroPendente.value,
);

/** `status` nulo vira '' e é ignorado pelo composable; '1'/'0' são strings, então "Inativo" passa. */
const getFiltros = (): Record<string, string> => ({
    nome: filtroNome.value,
    status: filtroStatus.value ?? '',
});

let filtroDebounce: ReturnType<typeof setTimeout> | undefined;
watch([filtroNome, filtroStatus], () => {
    filtroPendente.value = true;
    clearTimeout(filtroDebounce);
    filtroDebounce = setTimeout(() => {
        filtroPendente.value = false;
        load(1, getFiltros());
    }, 300);
});

const hasActiveFilters = computed(
    () => filtroNome.value !== '' || filtroStatus.value !== null,
);

const clearFiltros = () => {
    filtroNome.value = '';
    filtroStatus.value = null;
};

const getStatusLabel = (status: boolean): string =>
    props.statusOptions.find(
        (option) => option.value === String(Number(status)),
    )?.label ?? '';

const formRef = ref<InstanceType<typeof ModelForm> | null>(null);
const confirm = useConfirm();
const deleteForm = useForm<{ id: number | null }>({ id: null });
const registroParaExcluir = ref<string>('');

const openCreate = () => formRef.value?.open();
const openEdit = (teste: Teste) => formRef.value?.open(teste.id);

const confirmDelete = (teste: Teste) => {
    registroParaExcluir.value = teste.nome;
    confirm.require({
        header: 'Excluir registro?',
        message: `Tem certeza que deseja excluir "${teste.nome}"? Essa ação não poderá ser desfeita.`,
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Excluir',
        rejectProps: { severity: 'secondary', outlined: true },
        acceptProps: { severity: 'danger' },
        accept: () => {
            deleteForm.id = teste.id;
            deleteForm.post(TesteController.postDelete.url(), {
                preserveScroll: true,
                onSuccess: () => reload(),
            });
        },
    });
};

onMounted(() => load());
</script>

<template>
    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto px-8 pt-7 pb-14"
    >
        <Head title="Teste" />

        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1
                        class="text-xl font-extrabold tracking-tight text-foreground"
                    >
                        Teste
                    </h1>
                    <span
                        class="inline-flex h-6 items-center rounded-full bg-primary/10 px-2.5 text-[12.5px] font-bold text-primary"
                    >
                        {{ totalRecords }}
                        {{ totalRecords === 1 ? 'registro' : 'registros' }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    Módulo de referência: copie esta estrutura ao criar um
                    cadastro novo.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="TesteController.getForm().url">
                    <Button
                        label="Novo em página"
                        icon="pi pi-file-edit"
                        severity="secondary"
                        outlined
                        class="h-10 px-4"
                    />
                </Link>
                <Button
                    label="Novo registro"
                    icon="pi pi-plus"
                    class="h-10 px-4"
                    @click="openCreate"
                />
            </div>
        </div>

        <div
            v-if="error"
            class="flex items-start gap-2 rounded-md border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive"
        >
            <i class="pi pi-exclamation-circle mt-0.5" />
            <span>{{ error }}</span>
        </div>

        <Toolbar>
            <template #start>
                <div class="flex items-center gap-2">
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText
                            v-model="filtroNome"
                            placeholder="Buscar por nome"
                            aria-label="Buscar por nome"
                        />
                    </IconField>
                    <Select
                        v-model="filtroStatus"
                        :options="props.statusOptions"
                        option-label="label"
                        option-value="value"
                        placeholder="Status"
                        show-clear
                        class="w-40"
                        aria-label="Filtrar por status"
                    />
                </div>
            </template>
        </Toolbar>

        <DataTable
            :value="rows"
            :loading="loading"
            :lazy="true"
            :total-records="totalRecords"
            :first="first"
            :sort-field="sortField ?? undefined"
            :sort-order="sortOrder"
            paginator
            :rows="PER_PAGE"
            data-key="id"
            class="overflow-hidden rounded-lg border border-border bg-card"
            table-style="min-width: 50rem"
            @page="(e) => onPage(e, getFiltros())"
            @sort="(e) => onSort(e, getFiltros())"
        >
            <Column field="id" header="#" sortable style="width: 6rem" />
            <Column field="nome" header="Nome" sortable>
                <!-- O nome é o atalho de edição: abre a modal, sem sair da listagem. -->
                <template #body="{ data }">
                    <button
                        type="button"
                        class="cursor-pointer font-medium text-primary focus-visible:rounded-xs focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                        @click="openEdit(data)"
                    >
                        {{ data.nome }}
                    </button>
                </template>
            </Column>
            <Column field="status" header="Status" sortable>
                <template #body="{ data }">
                    <Tag :severity="data.status ? 'success' : 'secondary'">
                        <span class="flex items-center gap-1.5">
                            <span
                                class="size-1.5 rounded-full bg-current"
                                aria-hidden="true"
                            />
                            {{ getStatusLabel(data.status) }}
                        </span>
                    </Tag>
                </template>
            </Column>
            <Column field="created_at" header="Criado em" sortable>
                <template #body="{ data }">{{
                    formatDate(data.created_at)
                }}</template>
            </Column>
            <Column header="Ações" style="width: 9rem">
                <template #body="{ data }">
                    <div class="flex items-center gap-1">
                        <Link :href="TesteController.getView(data.id).url">
                            <Button
                                icon="pi pi-eye"
                                text
                                size="small"
                                aria-label="Visualizar"
                            />
                        </Link>
                        <Link :href="TesteController.getForm(data.id).url">
                            <Button
                                icon="pi pi-pencil"
                                text
                                size="small"
                                aria-label="Editar em página"
                            />
                        </Link>
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            size="small"
                            aria-label="Excluir"
                            @click="confirmDelete(data)"
                        />
                    </div>
                </template>
            </Column>

            <!-- O skeleton do slot `#empty` já comunica o carregamento; um `#loading` vazio
                 suprime a máscara concorrente que o PrimeVue desenha por cima das linhas. -->
            <template #loading />

            <template #empty>
                <div
                    v-if="requisicaoEmAndamento || !hasLoadedOnce"
                    class="w-full px-2 py-4"
                >
                    <div
                        v-for="row in 6"
                        :key="row"
                        class="flex w-full items-center gap-6 border-b border-border py-3 last:border-b-0"
                    >
                        <Skeleton width="3rem" height="1rem" />
                        <Skeleton width="35%" height="1rem" />
                        <Skeleton width="12%" height="1rem" />
                        <Skeleton width="15%" height="1rem" />
                        <Skeleton width="10%" height="1rem" class="ml-auto" />
                    </div>
                </div>
                <template v-else-if="!error">
                    <EmptyState
                        v-if="hasActiveFilters"
                        icon="pi-search"
                        title="Nenhum resultado encontrado"
                        description="Não encontramos registros para os filtros aplicados. Ajuste os termos ou limpe os filtros."
                        action-label="Limpar filtros"
                        action-icon="pi-filter-slash"
                        @action="clearFiltros"
                    />
                    <EmptyState
                        v-else
                        icon="pi-inbox"
                        title="Nenhum registro cadastrado"
                        description="Cadastre o primeiro registro para ver a listagem funcionando."
                        action-label="Novo registro"
                        action-icon="pi-plus"
                        @action="openCreate"
                    />
                </template>
            </template>
        </DataTable>

        <ConfirmDialog :pt="confirmDialogPt">
            <template #message>
                <span
                    class="flex size-[38px] shrink-0 items-center justify-center rounded-md bg-destructive/10"
                >
                    <i class="pi pi-trash text-base text-destructive" />
                </span>
                <span class="text-sm text-muted-foreground">
                    Tem certeza que deseja excluir
                    <strong class="font-semibold text-foreground"
                        >"{{ registroParaExcluir }}"</strong
                    >? Essa ação não poderá ser desfeita.
                </span>
            </template>
        </ConfirmDialog>

        <ModelForm ref="formRef" @saved="reload" />
    </div>
</template>
