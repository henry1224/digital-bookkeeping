<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <section
        class="overflow-hidden rounded-lg border border-border/70 bg-card shadow-sm"
    >
        <div class="flex flex-col gap-4 border-b bg-muted/25 px-4 py-4">
            <div>
                <h2 class="font-semibold tracking-tight">{{ title }}</h2>
                <p v-if="description" class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <div
                v-if="$slots.filters || $slots.meta"
                class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
            >
                <div
                    v-if="$slots.filters"
                    class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center"
                >
                    <slot name="filters" />
                </div>
                <div v-if="$slots.meta" class="flex items-center gap-2">
                    <slot name="meta" />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <slot />
            </table>
        </div>

        <div v-if="$slots.footer" class="border-t px-4 py-3">
            <slot name="footer" />
        </div>
    </section>
</template>
