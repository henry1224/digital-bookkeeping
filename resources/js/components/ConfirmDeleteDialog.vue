<script setup lang="ts">
import { Trash2, X } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
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
        title?: string;
        description?: string;
        subject?: string;
        note?: string;
        processing?: boolean;
        confirmLabel?: string;
        cancelLabel?: string;
    }>(),
    {
        title: 'Hapus Data',
        description:
            'Data tidak akan muncul lagi dalam daftar, tetapi riwayatnya tetap tersimpan.',
        subject: '',
        note: 'Perubahan ini akan dicatat agar riwayat data tetap dapat diperiksa.',
        processing: false,
        confirmLabel: 'Hapus',
        cancelLabel: 'Batal',
    },
);

const emit = defineEmits<{
    'update:open': [open: boolean];
    confirm: [];
}>();

const dialogOpen = computed({
    get: () => props.open,
    set: (open) => emit('update:open', open),
});
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogContent class="overflow-hidden p-0 sm:max-w-md">
            <DialogHeader
                class="border-b border-rose-200/70 bg-gradient-to-br from-rose-100 via-rose-50 to-card px-6 py-5 text-left dark:border-rose-900/70 dark:from-rose-950/50 dark:via-rose-950/20 dark:to-card"
            >
                <p
                    class="text-[11px] font-semibold tracking-[0.16em] text-rose-700 uppercase dark:text-rose-300"
                >
                    Perlu Konfirmasi
                </p>
                <DialogTitle class="text-xl tracking-tight">{{
                    title
                }}</DialogTitle>
                <DialogDescription class="leading-6">{{
                    description
                }}</DialogDescription>
            </DialogHeader>

            <div class="space-y-3 px-6 py-5">
                <div
                    class="rounded-lg border border-rose-200 bg-rose-50/70 p-4 dark:border-rose-900/70 dark:bg-rose-950/30"
                >
                    <p
                        class="text-xs font-medium text-rose-700 dark:text-rose-300"
                    >
                        Data yang dipilih
                    </p>
                    <p
                        class="mt-1 font-semibold text-rose-900 dark:text-rose-100"
                    >
                        {{ subject || 'Data ini' }}
                    </p>
                </div>
                <p class="text-sm text-muted-foreground">{{ note }}</p>
            </div>

            <DialogFooter class="border-t bg-muted/20 px-6 py-4">
                <Button
                    type="button"
                    variant="outline"
                    @click="dialogOpen = false"
                >
                    <X class="size-4" />
                    {{ cancelLabel }}
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    <Trash2 class="size-4" />
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
