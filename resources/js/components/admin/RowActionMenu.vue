<script setup lang="ts">
import { EllipsisVertical } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="outline"
                size="icon-sm"
                title="Buka pilihan tindakan"
                aria-label="Buka pilihan tindakan"
            >
                <EllipsisVertical class="size-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-48">
            <slot />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
