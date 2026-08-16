<script setup lang="ts">
import { Button } from '@/components/ui/button';

type Intent = 'edit' | 'warning' | 'success' | 'danger';

withDefaults(
    defineProps<{
        label: string;
        intent?: Intent;
    }>(),
    {
        intent: 'edit',
    },
);

const intentClasses: Record<Intent, string> = {
    edit: 'border-primary/20 text-primary hover:border-primary/30 hover:bg-primary/10 hover:text-primary',
    warning:
        'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 focus-visible:ring-amber-500 dark:border-amber-900/70 dark:bg-amber-950/30 dark:text-amber-300 dark:hover:bg-amber-950/50',
    success:
        'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 focus-visible:ring-emerald-500 dark:border-emerald-900/70 dark:bg-emerald-950/30 dark:text-emerald-300 dark:hover:bg-emerald-950/50',
    danger: 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 focus-visible:ring-rose-500 dark:border-rose-900/70 dark:bg-rose-950/30 dark:text-rose-300 dark:hover:bg-rose-950/50',
};

const emit = defineEmits<{
    click: [event: MouseEvent];
}>();
</script>

<template>
    <Button
        variant="outline"
        size="icon-sm"
        class="size-9 shadow-sm focus-visible:ring-offset-2"
        :class="intentClasses[intent]"
        :title="label"
        :aria-label="label"
        @click="emit('click', $event)"
    >
        <slot />
        <span class="sr-only">{{ label }}</span>
    </Button>
</template>
