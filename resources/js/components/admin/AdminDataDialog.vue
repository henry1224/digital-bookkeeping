<script setup lang="ts">
import { computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        eyebrow?: string;
        size?: 'standard' | 'wide';
    }>(),
    {
        eyebrow: 'Informasi Data',
        size: 'standard',
    },
);

const emit = defineEmits<{ 'update:open': [open: boolean] }>();
const dialogOpen = computed({
    get: () => props.open,
    set: (open) => emit('update:open', open),
});
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogContent
            class="max-h-[90vh] gap-0 overflow-visible border-border/80 p-0 shadow-2xl"
            :class="size === 'wide' ? 'sm:max-w-2xl' : 'sm:max-w-lg'"
        >
            <DialogHeader
                class="border-b border-border/70 bg-gradient-to-br from-primary/10 via-card to-card px-6 py-5 text-left"
            >
                <p
                    class="text-[11px] font-semibold tracking-[0.16em] text-primary uppercase"
                >
                    {{ eyebrow }}
                </p>
                <DialogTitle class="text-xl tracking-tight">{{
                    title
                }}</DialogTitle>
                <DialogDescription class="max-w-2xl leading-6">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div class="max-h-[65vh] overflow-y-auto bg-card px-6 py-5">
                <slot />
            </div>

            <DialogFooter
                v-if="$slots.footer"
                class="border-t border-border/70 bg-muted/25 px-6 py-4"
            >
                <slot name="footer" />
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
