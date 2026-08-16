<script setup lang="ts">
defineProps<{
    modelValue: string;
    label: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    change: [value: string];
}>();

const onChange = (event: Event) => {
    const value = (event.target as HTMLSelectElement).value;

    emit('update:modelValue', value);
    emit('change', value);
};
</script>

<template>
    <select
        :value="modelValue"
        class="h-10 rounded-md border border-input bg-background px-3 text-sm text-foreground shadow-sm transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/40 focus-visible:outline-none"
        :aria-label="label"
        @change="onChange"
    >
        <slot />
    </select>
</template>
