<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Pencil,
    Plus,
    Power,
    PowerOff,
    RotateCcw,
    Save,
    Search,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDebounceFn } from '@/lib/debounce';

type Outlet = {
    id: number;
    code: string;
    name: string;
    outlet_type: string;
    timezone: string;
    is_active: boolean;
    updated_at: string;
};

type PageLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type OutletFilters = {
    search: string;
    status: string;
    type: string;
};

type CancelToken = {
    cancel?: () => void;
};

const props = defineProps<{
    filters: OutletFilters;
    outlets: {
        data: Outlet[];
        links: PageLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
}>();

const search = ref(props.filters.search);
const filterStatus = ref(props.filters.status ?? 'semua');
const filterType = ref(props.filters.type ?? 'semua');
const dialogOpen = ref(false);
const editingOutlet = ref<Outlet | null>(null);
const form = useForm({
    code: '',
    name: '',
    outlet_type: 'outlet',
    timezone: 'Asia/Makassar',
    is_active: true,
    updated_at: '',
});

const pageLabel = (label: string) =>
    label
        .replace('&laquo; Previous', 'Sebelumnya')
        .replace('Next &raquo;', 'Berikutnya');

const outletTypeLabel = (type: string) =>
    type === 'central_kitchen' ? 'Central Kitchen' : 'Outlet';

const filterSelectClass =
    'h-10 rounded-md border border-input bg-background px-3 text-sm text-foreground shadow-sm transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/40 focus-visible:outline-none';
const rowActionBaseClass = 'size-9 shadow-sm focus-visible:ring-offset-2';
const rowActionEditClass =
    'border-primary/20 text-primary hover:border-primary/30 hover:bg-primary/10 hover:text-primary';
const rowActionWarningClass =
    'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 focus-visible:ring-amber-500 dark:border-amber-900/70 dark:bg-amber-950/30 dark:text-amber-300 dark:hover:bg-amber-950/50';
const rowActionSuccessClass =
    'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 focus-visible:ring-emerald-500 dark:border-emerald-900/70 dark:bg-emerald-950/30 dark:text-emerald-300 dark:hover:bg-emerald-950/50';

const hasActiveFilters = computed(
    () =>
        search.value !== '' ||
        filterStatus.value !== 'semua' ||
        filterType.value !== 'semua',
);

const activeFilters = (overrides: Partial<OutletFilters> = {}) => {
    const filters = {
        search: search.value,
        status: filterStatus.value,
        type: filterType.value,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (key === 'search') return String(value ?? '').trim() !== '';

            return value !== 'semua';
        }),
    );
};

let cancelToken: CancelToken | null = null;
let skipNextSearchWatch = false;

const visitFilters = (overrides: Partial<OutletFilters> = {}) => {
    cancelToken?.cancel?.();

    let visitToken: CancelToken | null = null;

    router.get('/master-data/outlets', activeFilters(overrides), {
        preserveState: true,
        replace: true,
        only: ['filters', 'outlets'],
        onCancelToken: (token) => {
            visitToken = token;
            cancelToken = token;
        },
        onFinish: () => {
            if (cancelToken === visitToken) {
                cancelToken = null;
            }
        },
    });
};

const requestFilters = useDebounceFn(visitFilters, 150);
const searchFilters = useDebounceFn(
    (value: string) => requestFilters({ search: value }),
    400,
);

const applyFilters = (
    overrides: Partial<OutletFilters> = {},
    immediate = false,
) => {
    if (Object.prototype.hasOwnProperty.call(overrides, 'search')) {
        const nextSearch = overrides.search ?? '';

        if (search.value !== nextSearch) {
            skipNextSearchWatch = true;
            search.value = nextSearch;
        }
    }

    if (Object.prototype.hasOwnProperty.call(overrides, 'status')) {
        filterStatus.value = overrides.status ?? 'semua';
    }

    if (Object.prototype.hasOwnProperty.call(overrides, 'type')) {
        filterType.value = overrides.type ?? 'semua';
    }

    if (immediate) {
        requestFilters.cancel();
        visitFilters(overrides);

        return;
    }

    requestFilters(overrides);
};

const clearSearch = () => {
    searchFilters.cancel();
    applyFilters({ search: '' }, true);
};

const resetFilters = () =>
    applyFilters({ search: '', status: 'semua', type: 'semua' }, true);

const openCreate = () => {
    editingOutlet.value = null;
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
};

const openEdit = (outlet: Outlet) => {
    editingOutlet.value = outlet;
    form.defaults({
        code: outlet.code,
        name: outlet.name,
        outlet_type: outlet.outlet_type,
        timezone: outlet.timezone,
        is_active: outlet.is_active,
        updated_at: outlet.updated_at,
    });
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => (dialogOpen.value = false),
    };

    if (editingOutlet.value) {
        form.patch(`/master-data/outlets/${editingOutlet.value.id}`, options);

        return;
    }

    form.post('/master-data/outlets', options);
};

const toggleOutlet = (outlet: Outlet) => {
    router.patch(
        `/master-data/outlets/${outlet.id}/toggle`,
        { updated_at: outlet.updated_at },
        { preserveScroll: true },
    );
};

watch(search, (value) => {
    if (skipNextSearchWatch) {
        skipNextSearchWatch = false;

        return;
    }

    searchFilters(value);
});

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Outlet', href: '/master-data/outlets' },
        ],
    },
});
</script>

<template>
    <Head title="Outlet" />

    <div class="space-y-6 p-4">
        <section
            class="rounded-lg border border-sidebar-border/70 bg-gradient-to-br from-emerald-50 via-white to-cyan-50 p-6 shadow-sm dark:from-emerald-950/30 dark:via-background dark:to-cyan-950/20"
        >
            <p
                class="text-sm font-medium text-emerald-700 dark:text-emerald-300"
            >
                Master Data
            </p>
            <div
                class="mt-2 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight">
                        Outlet
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                        Kelola cabang operasional, central kitchen, dan dasar
                        scope laporan multi-outlet.
                    </p>
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button @click="openCreate">
                        <Plus class="size-4" />
                        Tambah Outlet
                    </Button>
                </div>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-lg border border-border/70 bg-card shadow-sm"
        >
            <div class="flex flex-col gap-4 border-b bg-muted/25 px-4 py-4">
                <div>
                    <h2 class="font-semibold tracking-tight">Daftar Outlet</h2>
                    <p class="text-sm text-muted-foreground">
                        Data outlet aktif dan histori outlet nonaktif.
                    </p>
                </div>
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div
                        class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <div class="relative w-full sm:max-w-sm">
                            <Search
                                class="absolute top-2.5 left-3 size-4 text-muted-foreground"
                            />
                            <Input
                                v-model="search"
                                class="h-10 rounded-md pr-10 pl-9"
                                placeholder="Cari kode atau nama outlet"
                                aria-label="Cari kode atau nama outlet"
                            />
                            <button
                                v-if="search"
                                type="button"
                                class="absolute top-1.5 right-1.5 flex size-7 items-center justify-center rounded-md text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                title="Bersihkan pencarian"
                                aria-label="Bersihkan pencarian"
                                @click="clearSearch"
                            >
                                <X class="size-4" />
                            </button>
                        </div>
                        <select
                            v-model="filterType"
                            :class="filterSelectClass"
                            aria-label="Filter tipe outlet"
                            @change="applyFilters({ type: filterType })"
                        >
                            <option value="semua">Semua tipe</option>
                            <option value="outlet">Outlet</option>
                            <option value="central_kitchen">
                                Central Kitchen
                            </option>
                        </select>
                        <select
                            v-model="filterStatus"
                            :class="filterSelectClass"
                            aria-label="Filter status outlet"
                            @change="applyFilters({ status: filterStatus })"
                        >
                            <option value="semua">Semua status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="hasActiveFilters"
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            title="Reset filter"
                            aria-label="Reset filter"
                            @click="resetFilters"
                        >
                            <RotateCcw class="size-3.5" />
                            <span class="sr-only">Reset filter</span>
                        </Button>
                        <Badge
                            variant="secondary"
                            class="w-fit rounded-md px-3 py-1"
                        >
                            {{ outlets.total }} data
                        </Badge>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead
                        class="bg-gradient-to-r from-muted via-muted/70 to-card text-left text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3.5">Kode</th>
                            <th class="px-4 py-3.5">Nama</th>
                            <th class="px-4 py-3.5">Tipe</th>
                            <th class="px-4 py-3.5">Zona Waktu</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr
                            v-for="outlet in outlets.data"
                            :key="outlet.id"
                            class="transition-colors duration-200 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/20"
                        >
                            <td class="px-4 py-3 font-medium">
                                <span
                                    class="inline-flex rounded-md bg-primary/10 px-2.5 py-1 font-mono text-xs text-primary"
                                >
                                    {{ outlet.code }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-foreground">
                                    {{ outlet.name }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Diperbarui {{ outlet.updated_at }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ outletTypeLabel(outlet.outlet_type) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ outlet.timezone }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        outlet.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    class="rounded-md"
                                >
                                    {{
                                        outlet.is_active ? 'Aktif' : 'Nonaktif'
                                    }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        size="icon-sm"
                                        :class="[
                                            rowActionBaseClass,
                                            rowActionEditClass,
                                        ]"
                                        title="Edit outlet"
                                        aria-label="Edit outlet"
                                        @click="openEdit(outlet)"
                                    >
                                        <Pencil class="size-3.5" />
                                        <span class="sr-only">Edit outlet</span>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="icon-sm"
                                        :class="[
                                            rowActionBaseClass,
                                            outlet.is_active
                                                ? rowActionWarningClass
                                                : rowActionSuccessClass,
                                        ]"
                                        :title="
                                            outlet.is_active
                                                ? 'Nonaktifkan outlet'
                                                : 'Aktifkan outlet'
                                        "
                                        :aria-label="
                                            outlet.is_active
                                                ? 'Nonaktifkan outlet'
                                                : 'Aktifkan outlet'
                                        "
                                        @click="toggleOutlet(outlet)"
                                    >
                                        <PowerOff
                                            v-if="outlet.is_active"
                                            class="size-3.5"
                                        />
                                        <Power v-else class="size-3.5" />
                                        <span class="sr-only">
                                            {{
                                                outlet.is_active
                                                    ? 'Nonaktifkan outlet'
                                                    : 'Aktifkan outlet'
                                            }}
                                        </span>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="outlets.data.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Data outlet tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t px-4 py-3 md:flex-row md:items-center md:justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Menampilkan {{ outlets.from ?? 0 }}-{{
                        outlets.to ?? 0
                    }}
                    dari {{ outlets.total }} data
                </p>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="link in outlets.links"
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
        </section>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="overflow-hidden p-0 sm:max-w-xl">
                <DialogHeader class="border-b bg-muted/30 px-6 py-5">
                    <DialogTitle>{{
                        editingOutlet ? 'Edit Outlet' : 'Tambah Outlet'
                    }}</DialogTitle>
                    <DialogDescription>
                        Kode outlet harus unik. Outlet nonaktif tetap tersimpan
                        untuk histori.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4 px-6 py-5" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="code">Kode</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            placeholder="BPN-C"
                        />
                        <p
                            v-if="form.errors.code"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Nama</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Balikpapan C"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="outlet_type">Tipe</Label>
                        <select
                            id="outlet_type"
                            v-model="form.outlet_type"
                            class="h-9 rounded-md border bg-background px-3 text-sm"
                        >
                            <option value="outlet">Outlet</option>
                            <option value="central_kitchen">
                                Central Kitchen
                            </option>
                        </select>
                        <p
                            v-if="form.errors.outlet_type"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.outlet_type }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="timezone">Zona Waktu</Label>
                        <Input id="timezone" v-model="form.timezone" readonly />
                        <p
                            v-if="form.errors.timezone"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.timezone }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="size-4 rounded border"
                        />
                        Aktif
                    </label>

                    <DialogFooter
                        class="-mx-6 -mb-5 border-t bg-muted/20 px-6 py-4"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="dialogOpen = false"
                        >
                            <X class="size-4" />
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="size-4" />
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
