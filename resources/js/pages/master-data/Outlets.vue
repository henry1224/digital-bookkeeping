<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Eye,
    Pencil,
    Plus,
    Power,
    PowerOff,
    RotateCcw,
    Save,
    Trash2,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import AdminDataDialog from '@/components/admin/AdminDataDialog.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DataTableCard from '@/components/admin/DataTableCard.vue';
import DataTableFilterSelect from '@/components/admin/DataTableFilterSelect.vue';
import DataTablePagination from '@/components/admin/DataTablePagination.vue';
import DataTableSearch from '@/components/admin/DataTableSearch.vue';
import RowActionMenu from '@/components/admin/RowActionMenu.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenuItem,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/date';
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
    per_page: string;
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

const page = usePage();
const canCreate = computed(() =>
    page.props.auth.permissions.includes('master-data.create'),
);
const canUpdate = computed(() =>
    page.props.auth.permissions.includes('master-data.update'),
);
const search = ref(props.filters.search);
const filterStatus = ref(props.filters.status ?? 'semua');
const filterType = ref(props.filters.type ?? 'semua');
const perPage = ref(props.filters.per_page ?? '10');
const dialogOpen = ref(false);
const viewingOutlet = ref<Outlet | null>(null);
const editingOutlet = ref<Outlet | null>(null);
const deletingOutlet = ref<Outlet | null>(null);
const deleteProcessing = ref(false);
const form = useForm({
    code: '',
    name: '',
    outlet_type: 'outlet',
    timezone: 'Asia/Makassar',
    is_active: true,
    updated_at: '',
});

const outletTypeLabel = (type: string) =>
    type === 'central_kitchen' ? 'Central Kitchen' : 'Outlet';

const hasActiveFilters = computed(
    () =>
        search.value !== '' ||
        filterStatus.value !== 'semua' ||
        filterType.value !== 'semua' ||
        perPage.value !== '10',
);
const deleteDialogOpen = computed({
    get: () => deletingOutlet.value !== null,
    set: (open) => {
        if (!open) {
            deletingOutlet.value = null;
        }
    },
});
const viewDialogOpen = computed({
    get: () => viewingOutlet.value !== null,
    set: (open) => {
        if (!open) {
            viewingOutlet.value = null;
        }
    },
});

const activeFilters = (overrides: Partial<OutletFilters> = {}) => {
    const filters = {
        search: search.value,
        status: filterStatus.value,
        type: filterType.value,
        per_page: perPage.value,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (key === 'search') {
                return String(value ?? '').trim() !== '';
            }

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

    if (Object.prototype.hasOwnProperty.call(overrides, 'per_page')) {
        perPage.value = overrides.per_page ?? '10';
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
    applyFilters(
        { search: '', status: 'semua', type: 'semua', per_page: '10' },
        true,
    );

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

const openView = (outlet: Outlet) => {
    viewingOutlet.value = outlet;
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

const deleteOutlet = (outlet: Outlet) => {
    deletingOutlet.value = outlet;
};

const confirmDeleteOutlet = () => {
    if (!deletingOutlet.value) {
        return;
    }

    router.delete(`/master-data/outlets/${deletingOutlet.value.id}`, {
        data: { updated_at: deletingOutlet.value.updated_at },
        preserveScroll: true,
        onStart: () => (deleteProcessing.value = true),
        onSuccess: () => (deletingOutlet.value = null),
        onFinish: () => (deleteProcessing.value = false),
    });
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
        <AdminPageHeader
            eyebrow="Master Data"
            title="Outlet"
            description="Atur cabang dan dapur pusat yang digunakan dalam kegiatan operasional dan laporan."
        >
            <template #actions>
                <Button v-if="canCreate" @click="openCreate">
                    <Plus class="size-4" />
                    Tambah Outlet
                </Button>
            </template>
        </AdminPageHeader>

        <DataTableCard
            title="Daftar Outlet"
            description="Lihat outlet yang masih digunakan maupun yang sudah dinonaktifkan."
        >
            <template #filters>
                <DataTableFilterSelect
                    v-model="perPage"
                    label="Jumlah data per halaman"
                    @change="applyFilters({ per_page: $event }, true)"
                >
                    <option value="10">10 data</option>
                    <option value="25">25 data</option>
                    <option value="50">50 data</option>
                </DataTableFilterSelect>
                <DataTableSearch
                    v-model="search"
                    placeholder="Cari kode atau nama outlet"
                    label="Cari kode atau nama outlet"
                    @clear="clearSearch"
                />
                <DataTableFilterSelect
                    v-model="filterType"
                    label="Filter tipe outlet"
                    @change="(value) => applyFilters({ type: value })"
                >
                    <option value="semua">Semua tipe</option>
                    <option value="outlet">Outlet</option>
                    <option value="central_kitchen">Central Kitchen</option>
                </DataTableFilterSelect>
                <DataTableFilterSelect
                    v-model="filterStatus"
                    label="Filter status outlet"
                    @change="(value) => applyFilters({ status: value })"
                >
                    <option value="semua">Semua status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </DataTableFilterSelect>
            </template>

            <template #meta>
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
            </template>
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
                            Diperbarui {{ formatDate(outlet.updated_at) }}
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
                                outlet.is_active ? 'default' : 'secondary'
                            "
                            class="rounded-md"
                        >
                            {{ outlet.is_active ? 'Aktif' : 'Nonaktif' }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end">
                            <RowActionMenu>
                                <DropdownMenuItem @select="openView(outlet)">
                                    <Eye class="size-4" />
                                    Lihat
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="openEdit(outlet)"
                                >
                                    <Pencil class="size-4" />
                                    Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="toggleOutlet(outlet)"
                                >
                                    <PowerOff
                                        v-if="outlet.is_active"
                                        class="size-4"
                                    />
                                    <Power v-else class="size-4" />
                                    {{
                                        outlet.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}
                                </DropdownMenuItem>
                                <DropdownMenuSeparator v-if="canUpdate" />
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    variant="destructive"
                                    @select="deleteOutlet(outlet)"
                                >
                                    <Trash2 class="size-4" />
                                    Hapus
                                </DropdownMenuItem>
                            </RowActionMenu>
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

            <template #footer>
                <DataTablePagination
                    :from="outlets.from"
                    :to="outlets.to"
                    :total="outlets.total"
                    :links="outlets.links"
                />
            </template>
        </DataTableCard>

        <AdminDataDialog
            v-model:open="viewDialogOpen"
            title="Detail Outlet"
            description="Informasi lengkap outlet yang dipilih."
        >
            <dl v-if="viewingOutlet" class="grid gap-3 sm:grid-cols-2">
                <div
                    v-for="[label, value] in [
                        ['Kode', viewingOutlet.code],
                        ['Nama', viewingOutlet.name],
                        ['Jenis', outletTypeLabel(viewingOutlet.outlet_type)],
                        ['Zona Waktu', viewingOutlet.timezone],
                        [
                            'Status',
                            viewingOutlet.is_active ? 'Aktif' : 'Nonaktif',
                        ],
                        [
                            'Terakhir Diperbarui',
                            formatDate(viewingOutlet.updated_at),
                        ],
                    ]"
                    :key="label"
                    class="rounded-lg border border-border/70 bg-muted/20 p-4"
                >
                    <dt class="text-xs font-medium text-muted-foreground">
                        {{ label }}
                    </dt>
                    <dd class="mt-1 font-semibold text-foreground">
                        {{ value }}
                    </dd>
                </div>
            </dl>
            <template #footer>
                <Button variant="outline" @click="viewDialogOpen = false"
                    >Tutup</Button
                >
            </template>
        </AdminDataDialog>

        <AdminDataDialog
            v-model:open="dialogOpen"
            :title="editingOutlet ? 'Edit Outlet' : 'Tambah Outlet'"
            description="Lengkapi identitas outlet yang digunakan dalam kegiatan operasional dan laporan."
        >
            <form id="outlet-form" class="space-y-5" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="code">Kode</Label>
                    <Input id="code" v-model="form.code" placeholder="BPN-C" />
                    <p v-if="form.errors.code" class="text-sm text-destructive">
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
                    <p v-if="form.errors.name" class="text-sm text-destructive">
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
                        <option value="central_kitchen">Central Kitchen</option>
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
            </form>
            <template #footer>
                <Button
                    type="button"
                    variant="outline"
                    @click="dialogOpen = false"
                >
                    <X class="size-4" /> Batal
                </Button>
                <Button
                    type="submit"
                    form="outlet-form"
                    :disabled="form.processing"
                >
                    <Save class="size-4" /> Simpan
                </Button>
            </template>
        </AdminDataDialog>

        <ConfirmDeleteDialog
            v-model:open="deleteDialogOpen"
            title="Hapus Outlet"
            :subject="`outlet ${deletingOutlet?.name ?? ''}`"
            :processing="deleteProcessing"
            @confirm="confirmDeleteOutlet"
        />
    </div>
</template>
