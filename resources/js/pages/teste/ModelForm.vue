<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Skeleton from 'primevue/skeleton';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { computed, nextTick, ref } from 'vue';
import type { ComponentPublicInstance } from 'vue';
import TesteController from '@/actions/App/Http/Controllers/TesteController';

/**
 * FORMULÁRIO EM MODAL — par frontend de `TesteController::getModelForm()`.
 *
 * Ao abrir, busca o payload do formulário no backend (`{ registro, statusOptions }`) em vez de
 * herdar a linha do DataTable: a modal sempre mostra o dado atual, mesmo que a listagem esteja
 * defasada, e o mesmo componente serve cadastro (sem id) e edição (com id).
 *
 * O envio é sempre `postForm` — é o `id` no payload que decide entre inserir e atualizar.
 */

interface FormPayload {
    registro: {
        id: number | null;
        nome: string;
        descricao: string | null;
        status: boolean;
    };
}

const emit = defineEmits<{
    saved: [];
}>();

const visible = ref(false);
const carregando = ref(false);
const erroCarregamento = ref<string | null>(null);
const nomeInputRef = ref<ComponentPublicInstance | null>(null);

const form = useForm<{
    id: number | null;
    nome: string;
    descricao: string;
    status: boolean;
}>({
    id: null,
    nome: '',
    descricao: '',
    status: true,
});

const isEdit = computed(() => form.id !== null);

/**
 * Abre a modal. Sem argumento → cadastro; com o id da linha → edição.
 *
 * Os campos são atribuídos explicitamente em vez de `form.reset()`: o `useForm` do Inertia
 * redefine os defaults a cada `onSuccess`, então após um salvamento o `reset()` voltaria para
 * o último registro salvo, e não para um formulário limpo.
 */
const open = async (id?: number | null) => {
    form.clearErrors();
    erroCarregamento.value = null;
    carregando.value = true;
    visible.value = true;

    try {
        const response = await fetch(
            TesteController.getModelForm(id ?? undefined).url,
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        );

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data: FormPayload = await response.json();

        form.id = data.registro.id;
        form.nome = data.registro.nome;
        form.descricao = data.registro.descricao ?? '';
        form.status = data.registro.status;
    } catch (err) {
        console.error('Erro ao carregar o formulário:', err);
        erroCarregamento.value =
            'Não foi possível carregar o formulário. Feche e tente novamente.';
    } finally {
        carregando.value = false;
        // O `$el` do InputText do PrimeVue É o <input> nativo (raiz do template do componente).
        nextTick(() =>
            (nomeInputRef.value?.$el as HTMLInputElement | undefined)?.focus(),
        );
    }
};

const close = () => {
    visible.value = false;
};

const submit = () => {
    form.post(TesteController.postForm.url(), {
        preserveScroll: true,
        // Mantém vivo o estado local da listagem (feed já carregado) após o redirect.
        preserveState: true,
        onSuccess: () => {
            close();
            emit('saved');
        },
        // onError: a modal fica aberta para o usuário corrigir os campos.
    });
};

defineExpose({ open });
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        :style="{ width: '480px' }"
        :header="isEdit ? 'Editar registro' : 'Novo registro'"
    >
        <div v-if="carregando" class="flex flex-col gap-4">
            <Skeleton height="2.5rem" />
            <Skeleton height="5rem" />
            <Skeleton width="6rem" height="1.5rem" />
        </div>

        <p v-else-if="erroCarregamento" class="text-sm text-destructive">
            {{ erroCarregamento }}
        </p>

        <div v-else class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <label
                    for="teste-nome"
                    class="text-[13px] font-semibold text-muted-foreground"
                    >Nome</label
                >
                <InputText
                    id="teste-nome"
                    ref="nomeInputRef"
                    v-model="form.nome"
                    class="flex-auto"
                    autocomplete="off"
                    autofocus
                    :invalid="!!form.errors.nome"
                />
                <small v-if="form.errors.nome" class="text-destructive">{{
                    form.errors.nome
                }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label
                    for="teste-descricao"
                    class="text-[13px] font-semibold text-muted-foreground"
                >
                    Descrição
                </label>
                <Textarea
                    id="teste-descricao"
                    v-model="form.descricao"
                    rows="3"
                    auto-resize
                    :invalid="!!form.errors.descricao"
                />
                <small v-if="form.errors.descricao" class="text-destructive">
                    {{ form.errors.descricao }}
                </small>
            </div>

            <div class="flex flex-col gap-2">
                <label
                    for="teste-status"
                    class="text-[13px] font-semibold text-muted-foreground"
                    >Status</label
                >
                <ToggleSwitch
                    id="teste-status"
                    v-model="form.status"
                    size="small"
                />
                <small v-if="form.errors.status" class="text-destructive">{{
                    form.errors.status
                }}</small>
            </div>
        </div>

        <template #footer>
            <div class="flex w-full items-center justify-between gap-2">
                <Button
                    type="button"
                    label="Cancelar"
                    severity="secondary"
                    class="h-[42px] px-4"
                    @click="close"
                />
                <Button
                    type="button"
                    label="Salvar"
                    :loading="form.processing"
                    :disabled="carregando || !!erroCarregamento"
                    class="h-[42px] px-4"
                    @click="submit"
                />
            </div>
        </template>
    </Dialog>
</template>
