<script setup lang="ts">
defineProps<{
    eyebrow?: string;
    title: string;
    description?: string;
}>();
</script>

<template>
    <section
        class="rounded-lg border border-sidebar-border/70 bg-gradient-to-br from-emerald-50 via-white to-cyan-50 p-6 shadow-sm dark:from-emerald-950/30 dark:via-background dark:to-cyan-950/20"
    >
        <p
            v-if="eyebrow"
            class="text-sm font-medium text-emerald-700 dark:text-emerald-300"
        >
            {{ eyebrow }}
        </p>
        <div
            class="mt-2 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
        >
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">
                    {{ title }}
                </h1>
                <p
                    v-if="description"
                    class="mt-2 max-w-2xl text-sm text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
            <div
                v-if="$slots.actions"
                class="flex w-full flex-col gap-2 md:w-auto md:flex-row"
            >
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>
