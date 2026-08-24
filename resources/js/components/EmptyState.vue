<script setup lang="ts">
import Button from 'primevue/button';

/**
 * Bloco de estado vazio das listagens (slot `#empty` do DataTable). Cobre os dois casos com a
 * mesma forma: nada cadastrado ainda, ou nenhum resultado para os filtros ativos.
 */
type Props = {
    icon: string;
    title: string;
    description: string;
    actionLabel?: string;
    actionIcon?: string;
};

withDefaults(defineProps<Props>(), {
    actionLabel: undefined,
    actionIcon: undefined,
});

defineEmits<{
    action: [];
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center gap-1.5 px-6 py-16 text-center"
    >
        <span
            class="mb-2 flex size-14 items-center justify-center rounded-full bg-muted"
        >
            <i :class="['pi', icon, 'text-2xl text-muted-foreground']" />
        </span>
        <p class="text-base font-semibold text-foreground">{{ title }}</p>
        <p class="max-w-sm text-sm text-muted-foreground">{{ description }}</p>
        <Button
            v-if="actionLabel"
            class="mt-4"
            :label="actionLabel"
            :icon="actionIcon ? `pi ${actionIcon}` : undefined"
            size="small"
            @click="$emit('action')"
        />
    </div>
</template>
