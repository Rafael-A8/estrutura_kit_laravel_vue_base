<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import TesteController from '@/actions/App/Http/Controllers/TesteController';
import { formatDate } from '@/utils/formatters';

/**
 * VISUALIZAÇÃO — par frontend de `TesteController::getView()`. Somente leitura: nenhuma
 * escrita parte desta tela, as ações levam ao formulário.
 */

defineProps<{
    registro: {
        id: number;
        nome: string;
        descricao: string | null;
        status: boolean;
        created_at: string;
        updated_at: string;
    };
}>();
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-5 px-8 pt-7 pb-14">
        <Head :title="registro.nome" />

        <Link
            :href="TesteController.getIndex().url"
            class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
        >
            <i class="pi pi-arrow-left text-xs" />
            Voltar para a listagem
        </Link>

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1
                    class="text-xl font-extrabold tracking-tight text-foreground"
                >
                    {{ registro.nome }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Registro #{{ registro.id }}
                </p>
            </div>
            <Link :href="TesteController.getForm(registro.id).url">
                <Button label="Editar" icon="pi pi-pencil" class="h-10 px-4" />
            </Link>
        </div>

        <dl
            class="grid max-w-2xl gap-5 rounded-lg border border-border bg-card p-6 sm:grid-cols-2"
        >
            <div>
                <dt class="text-[13px] font-semibold text-muted-foreground">
                    Status
                </dt>
                <dd class="mt-1">
                    <Tag :severity="registro.status ? 'success' : 'secondary'">
                        {{ registro.status ? 'Ativo' : 'Inativo' }}
                    </Tag>
                </dd>
            </div>
            <div>
                <dt class="text-[13px] font-semibold text-muted-foreground">
                    Criado em
                </dt>
                <dd class="mt-1 text-sm text-foreground">
                    {{ formatDate(registro.created_at) }}
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-[13px] font-semibold text-muted-foreground">
                    Descrição
                </dt>
                <dd class="mt-1 text-sm text-foreground">
                    {{ registro.descricao ?? '—' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
