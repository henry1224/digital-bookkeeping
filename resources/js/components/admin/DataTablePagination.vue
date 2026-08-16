<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type PageLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    from: number | null;
    to: number | null;
    total: number;
    links: PageLink[];
}>();

const pageLabel = (label: string) =>
    label
        .replace('&laquo; Previous', 'Sebelumnya')
        .replace('Next &raquo;', 'Berikutnya');
</script>

<template>
    <div
        class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
    >
        <p class="text-sm text-muted-foreground">
            Menampilkan {{ from ?? 0 }}-{{ to ?? 0 }} dari {{ total }} data
        </p>
        <div class="flex flex-wrap gap-2">
            <Link
                v-for="link in links"
                :key="link.label"
                :href="link.url || '#'"
                class="rounded-md border px-3 py-1.5 text-sm"
                :class="[
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'hover:bg-muted',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
                preserve-scroll
                preserve-state
            >
                {{ pageLabel(link.label) }}
            </Link>
        </div>
    </div>
</template>
