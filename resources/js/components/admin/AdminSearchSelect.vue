<script setup lang="ts">
import { ChevronRight, Search } from '@lucide/vue';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

type Option = { value: string; label: string };
const props = withDefaults(
    defineProps<{
        modelValue: string;
        options: Option[];
        placeholder?: string;
        searchPlaceholder?: string;
        emptyLabel?: string;
        emptyText?: string;
        ariaLabel?: string;
        disabled?: boolean;
    }>(),
    {
        placeholder: 'Pilih data',
        searchPlaceholder: 'Cari data...',
        emptyLabel: '',
        emptyText: 'Data tidak ditemukan.',
        ariaLabel: '',
        disabled: false,
    },
);
const emit = defineEmits<{
    'update:modelValue': [value: string];
    change: [value: string];
}>();
const componentId = `admin-search-select-${Math.random().toString(36).slice(2, 10)}`;
const wrapper = ref<HTMLElement | null>(null);
const dropdown = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const open = ref(false);
const search = ref('');
const dropdownStyle = ref<Record<string, string>>({});
const teleportTarget = ref<string | HTMLElement>('body');
const selectedOption = computed(
    () =>
        props.options.find((option) => option.value === props.modelValue) ??
        null,
);
const filteredOptions = computed(() => {
    const keyword = search.value.trim().toLocaleLowerCase('id-ID');

    return props.options
        .filter(
            (option) =>
                keyword === '' ||
                option.label.toLocaleLowerCase('id-ID').includes(keyword),
        )
        .slice(0, 50);
});
const close = () => {
    open.value = false;
};
const updatePosition = () => {
    const rect = wrapper.value?.getBoundingClientRect();

    if (!rect) {
        return;
    }

    const dialog = wrapper.value?.closest<HTMLElement>('[role="dialog"]');
    const containerRect = dialog?.getBoundingClientRect();
    teleportTarget.value = dialog ?? 'body';

    const maxHeight = 320;
    const gap = 8;
    const padding = 12;
    const bottomSpace = window.innerHeight - rect.bottom - padding;
    const topSpace = rect.top - padding;
    const above = bottomSpace < maxHeight && topSpace > bottomSpace;
    const availableHeight = Math.max(
        180,
        Math.min(maxHeight, above ? topSpace - gap : bottomSpace - gap),
    );
    dropdownStyle.value = {
        position: dialog ? 'absolute' : 'fixed',
        left: `${rect.left - (containerRect?.left ?? 0)}px`,
        top: above
            ? `${rect.top - (containerRect?.top ?? 0) - availableHeight - gap}px`
            : `${rect.bottom - (containerRect?.top ?? 0) + gap}px`,
        width: `${rect.width}px`,
        maxHeight: `${availableHeight}px`,
    };
};
const toggle = () => {
    if (!props.disabled) {
        open.value = !open.value;
    }
};
const choose = (value: string) => {
    emit('update:modelValue', value);
    emit('change', value);
    close();
};
const outside = (event: PointerEvent) => {
    const target = event.target as Node;

    if (
        open.value &&
        !wrapper.value?.contains(target) &&
        !dropdown.value?.contains(target)
    ) {
        close();
    }
};
const reposition = () => {
    if (open.value) {
        updatePosition();
    }
};
watch(open, async (value) => {
    if (!value) {
        search.value = '';

        return;
    }

    await nextTick();
    updatePosition();
    searchInput.value?.focus();
});
watch(
    () => props.disabled,
    (value) => {
        if (value) {
            close();
        }
    },
);
onMounted(() => {
    document.addEventListener('pointerdown', outside);
    window.addEventListener('resize', reposition);
    window.addEventListener('scroll', reposition, true);
});
onUnmounted(() => {
    document.removeEventListener('pointerdown', outside);
    window.removeEventListener('resize', reposition);
    window.removeEventListener('scroll', reposition, true);
});
</script>

<template>
    <div ref="wrapper" class="relative" @keydown.escape.stop.prevent="close">
        <button
            type="button"
            class="flex h-10 w-full items-center justify-between gap-3 rounded-md border border-input bg-background px-3 text-left text-sm shadow-sm transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/40 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="disabled"
            role="combobox"
            aria-haspopup="listbox"
            :aria-expanded="open"
            :aria-controls="`${componentId}-listbox`"
            :aria-label="ariaLabel || placeholder"
            @click="toggle"
        >
            <span
                class="min-w-0 truncate"
                :class="
                    selectedOption
                        ? 'font-medium text-foreground'
                        : 'text-muted-foreground'
                "
                >{{
                    selectedOption?.label ?? (emptyLabel || placeholder)
                }}</span
            >
            <ChevronRight
                class="size-4 shrink-0 text-muted-foreground transition-transform"
                :class="open ? 'rotate-90' : ''"
            />
        </button>
        <Teleport :to="teleportTarget">
            <div
                v-if="open"
                :id="`${componentId}-listbox`"
                ref="dropdown"
                role="listbox"
                :style="dropdownStyle"
                class="z-[100] overflow-hidden rounded-lg border border-border bg-popover text-popover-foreground shadow-2xl ring-1 ring-foreground/5"
            >
                <div class="border-b border-border p-2">
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        /><input
                            ref="searchInput"
                            v-model="search"
                            type="text"
                            class="h-10 w-full rounded-md border border-input bg-muted/30 pr-3 pl-9 text-sm outline-none placeholder:text-muted-foreground focus:border-ring focus:bg-background focus:ring-2 focus:ring-ring/30"
                            :placeholder="searchPlaceholder"
                            :aria-label="searchPlaceholder"
                        />
                    </div>
                </div>
                <div
                    class="overflow-y-auto p-1"
                    :style="{
                        maxHeight: `calc(${dropdownStyle.maxHeight ?? '20rem'} - 58px)`,
                    }"
                >
                    <button
                        v-if="emptyLabel"
                        type="button"
                        class="mb-1 flex w-full rounded-md px-3 py-2 text-left text-sm font-medium text-muted-foreground hover:bg-accent"
                        role="option"
                        :aria-selected="modelValue === ''"
                        @click="choose('')"
                    >
                        {{ emptyLabel }}
                    </button>
                    <button
                        v-for="option in filteredOptions"
                        :key="option.value"
                        type="button"
                        role="option"
                        :aria-selected="option.value === modelValue"
                        class="flex w-full rounded-md px-3 py-2 text-left text-sm transition-colors"
                        :class="
                            option.value === modelValue
                                ? 'bg-primary/10 font-semibold text-primary'
                                : 'hover:bg-accent'
                        "
                        @click="choose(option.value)"
                    >
                        {{ option.label }}
                    </button>
                    <p
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-4 text-sm text-muted-foreground"
                    >
                        {{ emptyText }}
                    </p>
                </div>
            </div>
        </Teleport>
    </div>
</template>
