<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { computed } from 'vue';
import TesteController from '@/actions/App/Http/Controllers/TesteController';

/**
 * FORMULÁRIO EM PÁGINA — par frontend de `TesteController::getForm()`.
 *
 * Mesmo contrato da modal (`{ registro, statusOptions }`), só que os dados chegam como props do
 * Inertia. Use esta variante quando o formulário for grande demais para uma modal (muitos
 * campos, abas, upload); para cadastros curtos prefira `ModelForm.vue`.
 */

const props = defineProps<{
    registro: {
        id: number | null;
        nome: string;
        descricao: string | null;
        status: boolean;
    };
}>();

const form = useForm({
    id: props.registro.id,
    nome: props.registro.nome,
    descricao: props.registro.descricao ?? '',
    status: props.registro.status,
});

const isEdit = computed(() => props.registro.id !== null);

const submit = () => {
    form.post(TesteController.postForm.url(), { preserveScroll: true });
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-5 px-8 pt-7 pb-14">
        <Head :title="isEdit ? 'Editar registro' : 'Novo registro'" />

        <Link
            :href="TesteController.getIndex().url"
            class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
        >
            <i class="pi pi-arrow-left text-xs" />
            Voltar para a listagem
        </Link>

        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-foreground">
                {{ isEdit ? 'Editar registro' : 'Novo registro' }}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                O mesmo formulário atende cadastro e edição — o backend decide
                pelo campo `id`.
            </p>
        </div>

        <form
            class="flex max-w-2xl flex-col gap-4 rounded-lg border border-border bg-card p-6"
            @submit.prevent="submit"
        >
            <div class="flex flex-col gap-2">
                <label
                    for="nome"
                    class="text-[13px] font-semibold text-muted-foreground"
                    >Nome</label
                >
                <InputText
                    id="nome"
                    v-model="form.nome"
                    autocomplete="off"
                    :invalid="!!form.errors.nome"
                />
                <small v-if="form.errors.nome" class="text-destructive">{{
                    form.errors.nome
                }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label
                    for="descricao"
                    class="text-[13px] font-semibold text-muted-foreground"
                    >Descrição</label
                >
                <Textarea
                    id="descricao"
                    v-model="form.descricao"
                    rows="4"
                    auto-resize
                    :invalid="!!form.errors.descricao"
                />
                <small v-if="form.errors.descricao" class="text-destructive">{{
                    form.errors.descricao
                }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label
                    for="status"
                    class="text-[13px] font-semibold text-muted-foreground"
                    >Status</label
                >
                <ToggleSwitch id="status" v-model="form.status" size="small" />
                <small v-if="form.errors.status" class="text-destructive">{{
                    form.errors.status
                }}</small>
            </div>

            <div class="mt-2 flex items-center justify-between gap-2">
                <Link :href="TesteController.getIndex().url">
                    <Button
                        type="button"
                        label="Cancelar"
                        severity="secondary"
                        class="h-[42px] px-4"
                    />
                </Link>
                <Button
                    type="submit"
                    label="Salvar"
                    :loading="form.processing"
                    class="h-[42px] px-4"
                />
            </div>
        </form>
    </div>
</template>
