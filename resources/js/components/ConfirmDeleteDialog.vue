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
            'Data akan dihapus dari daftar, tapi histori tetap tersimpan.',
        subject: '',
        note: 'Aksi ini memakai soft delete dan tercatat di audit log.',
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
                class="border-b bg-rose-50 px-6 py-5 dark:bg-rose-950/20"
            >
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>

            <div class="space-y-3 px-6 py-5">
                <div
                    class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/70 dark:bg-rose-950/30 dark:text-rose-200"
                >
                    Yakin hapus
                    <span v-if="subject" class="font-semibold">
                        {{ subject }}
                    </span>
                    ?
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
