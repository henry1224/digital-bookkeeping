<script setup lang="ts">
import { Search, X } from '@lucide/vue';
import { Input } from '@/components/ui/input';

withDefaults(
    defineProps<{
        modelValue: string;
        placeholder?: string;
        label?: string;
    }>(),
    {
        placeholder: 'Cari data',
        label: 'Cari data',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
    clear: [];
}>();
</script>

<template>
    <div class="relative w-full sm:max-w-sm">
        <Search class="absolute top-2.5 left-3 size-4 text-muted-foreground" />
        <Input
            :model-value="modelValue"
            class="h-10 rounded-md pr-10 pl-9"
            :placeholder="placeholder"
            :aria-label="label"
            @update:model-value="emit('update:modelValue', String($event))"
        />
        <button
            v-if="modelValue"
            type="button"
            class="absolute top-1.5 right-1.5 flex size-7 items-center justify-center rounded-md text-muted-foreground transition hover:bg-muted hover:text-foreground"
            title="Bersihkan pencarian"
            aria-label="Bersihkan pencarian"
            @click="emit('clear')"
        >
            <X class="size-4" />
        </button>
    </div>
</template>
