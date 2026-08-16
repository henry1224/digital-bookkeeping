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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenuItem,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/date';
import { useDebounceFn } from '@/lib/debounce';

type UnitOfMeasure = {
    id: number;
    code: string;
    name: string;
    base_code: string;
    factor: string;
    is_active: boolean;
    items_count: number;
    updated_at: string;
};

type PageLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type UnitFilters = {
    search: string;
    status: string;
    per_page: string;
};

type CancelToken = {
    cancel?: () => void;
};

const props = defineProps<{
    filters: UnitFilters;
    units: {
        data: UnitOfMeasure[];
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
const perPage = ref(props.filters.per_page ?? '10');
const dialogOpen = ref(false);
const viewingUnit = ref<UnitOfMeasure | null>(null);
const editingUnit = ref<UnitOfMeasure | null>(null);
const deletingUnit = ref<UnitOfMeasure | null>(null);
const deleteProcessing = ref(false);
const deleteError = ref('');
const form = useForm({
    code: '',
    name: '',
    base_code: '',
    factor: '1.000000',
    is_active: true,
    updated_at: '',
});

const hasActiveFilters = computed(
    () =>
        search.value !== '' ||
        filterStatus.value !== 'semua' ||
        perPage.value !== '10',
);
const deleteDialogOpen = computed({
    get: () => deletingUnit.value !== null,
    set: (open) => {
        if (!open) {
            deletingUnit.value = null;
            deleteError.value = '';
        }
    },
});
const viewDialogOpen = computed({
    get: () => viewingUnit.value !== null,
    set: (open) => {
        if (!open) {
            viewingUnit.value = null;
        }
    },
});

const activeFilters = (overrides: Partial<UnitFilters> = {}) => {
    const filters = {
        search: search.value,
        status: filterStatus.value,
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

const visitFilters = (overrides: Partial<UnitFilters> = {}) => {
    cancelToken?.cancel?.();

    let visitToken: CancelToken | null = null;

    router.get('/master-data/uom', activeFilters(overrides), {
        preserveState: true,
        replace: true,
        only: ['filters', 'units'],
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
    overrides: Partial<UnitFilters> = {},
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
    applyFilters({ search: '', status: 'semua', per_page: '10' }, true);

const openCreate = () => {
    editingUnit.value = null;
    form.defaults({
        code: '',
        name: '',
        base_code: '',
        factor: '1.000000',
        is_active: true,
        updated_at: '',
    });
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
};

const openEdit = (unit: UnitOfMeasure) => {
    editingUnit.value = unit;
    form.defaults({
        code: unit.code,
        name: unit.name,
        base_code: unit.base_code,
        factor: unit.factor,
        is_active: unit.is_active,
        updated_at: unit.updated_at,
    });
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
};

const openView = (unit: UnitOfMeasure) => {
    viewingUnit.value = unit;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => (dialogOpen.value = false),
    };

    if (editingUnit.value) {
        form.patch(`/master-data/uom/${editingUnit.value.id}`, options);

        return;
    }

    form.post('/master-data/uom', options);
};

const toggleUnit = (unit: UnitOfMeasure) => {
    router.patch(
        `/master-data/uom/${unit.id}/toggle`,
        { updated_at: unit.updated_at },
        { preserveScroll: true },
    );
};

const deleteUnit = (unit: UnitOfMeasure) => {
    deletingUnit.value = unit;
    deleteError.value = '';
};

const confirmDelete = () => {
    if (!deletingUnit.value) {
        return;
    }

    deleteProcessing.value = true;
    deleteError.value = '';

    router.delete(`/master-data/uom/${deletingUnit.value.id}`, {
        data: { updated_at: deletingUnit.value.updated_at },
        preserveScroll: true,
        onSuccess: () => (deletingUnit.value = null),
        onError: (errors) => {
            deleteError.value =
                String(errors.unit_of_measure ?? '') ||
                'Satuan gagal dihapus. Muat ulang lalu coba lagi.';
        },
        onFinish: () => (deleteProcessing.value = false),
    });
};

const formatFactor = (factor: string) =>
    Number(factor).toLocaleString('id-ID', { maximumFractionDigits: 6 });

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
            { title: 'Satuan (UOM)', href: '/master-data/uom' },
        ],
    },
});
</script>

<template>
    <Head title="Satuan (UOM)" />

    <div class="space-y-6 p-4">
        <AdminPageHeader
            eyebrow="Master Data"
            title="Satuan (UOM)"
            description="Atur satuan yang digunakan untuk barang, pembelian, dan pencatatan stok."
        >
            <template #actions>
                <Button v-if="canCreate" @click="openCreate">
                    <Plus class="size-4" />
                    Tambah Satuan
                </Button>
            </template>
        </AdminPageHeader>

        <DataTableCard
            title="Daftar Satuan"
            description="Satuan yang sudah dipakai item tetap tersimpan dan tidak dapat dihapus."
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
                    class="w-full sm:max-w-sm"
                    placeholder="Cari kode, nama, atau satuan dasar"
                    label="Cari satuan"
                    @clear="clearSearch"
                />
                <DataTableFilterSelect
                    v-model="filterStatus"
                    label="Filter status satuan"
                    @change="applyFilters({ status: $event })"
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
                    size="sm"
                    @click="resetFilters"
                >
                    <RotateCcw class="size-3.5" />
                    Reset
                </Button>
                <Badge variant="secondary" class="w-fit rounded-md px-3 py-1">
                    {{ units.total }} data
                </Badge>
            </template>

            <thead
                class="bg-gradient-to-r from-muted via-muted/70 to-card text-left text-xs tracking-wide text-muted-foreground uppercase"
            >
                <tr>
                    <th class="px-4 py-3.5">Kode</th>
                    <th class="px-4 py-3.5">Nama Satuan</th>
                    <th class="px-4 py-3.5">Satuan Dasar</th>
                    <th class="px-4 py-3.5 text-right">Faktor</th>
                    <th class="px-4 py-3.5 text-center">Dipakai Item</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/70">
                <tr
                    v-for="unit in units.data"
                    :key="unit.id"
                    class="transition-colors duration-200 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/20"
                >
                    <td class="px-4 py-3 font-medium">
                        <span
                            class="inline-flex rounded-md bg-primary/10 px-2.5 py-1 font-mono text-xs text-primary"
                        >
                            {{ unit.code }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-foreground">
                            {{ unit.name }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Diperbarui {{ formatDate(unit.updated_at) }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-muted-foreground">
                        {{ unit.base_code }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-xs">
                        {{ formatFactor(unit.factor) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <Badge variant="outline" class="rounded-md">
                            {{ unit.items_count }} item
                        </Badge>
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="unit.is_active ? 'default' : 'secondary'"
                            class="rounded-md"
                        >
                            {{ unit.is_active ? 'Aktif' : 'Nonaktif' }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end">
                            <RowActionMenu>
                                <DropdownMenuItem @select="openView(unit)">
                                    <Eye class="size-4" />
                                    Lihat
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="openEdit(unit)"
                                >
                                    <Pencil class="size-4" />
                                    Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="toggleUnit(unit)"
                                >
                                    <PowerOff
                                        v-if="unit.is_active"
                                        class="size-4"
                                    />
                                    <Power v-else class="size-4" />
                                    {{
                                        unit.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}
                                </DropdownMenuItem>
                                <DropdownMenuSeparator v-if="canUpdate" />
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    variant="destructive"
                                    @select="deleteUnit(unit)"
                                >
                                    <Trash2 class="size-4" />
                                    Hapus
                                </DropdownMenuItem>
                            </RowActionMenu>
                        </div>
                    </td>
                </tr>
                <tr v-if="units.data.length === 0">
                    <td
                        colspan="7"
                        class="px-4 py-12 text-center text-muted-foreground"
                    >
                        Data satuan tidak ditemukan.
                    </td>
                </tr>
            </tbody>

            <template #footer>
                <DataTablePagination
                    :from="units.from"
                    :to="units.to"
                    :total="units.total"
                    :links="units.links"
                />
            </template>
        </DataTableCard>

        <Dialog v-model:open="viewDialogOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Detail Satuan</DialogTitle>
                    <DialogDescription>
                        Informasi lengkap satuan yang dipilih.
                    </DialogDescription>
                </DialogHeader>

                <dl v-if="viewingUnit" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-muted-foreground">Kode</dt>
                        <dd class="font-medium">{{ viewingUnit.code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Nama</dt>
                        <dd class="font-medium">{{ viewingUnit.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Satuan Dasar
                        </dt>
                        <dd>{{ viewingUnit.base_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Faktor</dt>
                        <dd>{{ formatFactor(viewingUnit.factor) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Digunakan oleh
                        </dt>
                        <dd>{{ viewingUnit.items_count }} barang</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Status</dt>
                        <dd>
                            {{ viewingUnit.is_active ? 'Aktif' : 'Nonaktif' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Terakhir Diperbarui
                        </dt>
                        <dd>{{ formatDate(viewingUnit.updated_at) }}</dd>
                    </div>
                </dl>

                <DialogFooter>
                    <Button variant="outline" @click="viewDialogOpen = false">
                        Tutup
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingUnit ? 'Edit Satuan' : 'Tambah Satuan' }}
                    </DialogTitle>
                    <DialogDescription>
                        Faktor menyatakan nilai satuan terhadap satuan dasar.
                        Contoh: GR terhadap KG adalah 0,001.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="code">Kode</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="KG"
                                autocomplete="off"
                            />
                            <p
                                v-if="form.errors.code"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.code }}
                            </p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="base_code">Satuan Dasar</Label>
                            <Input
                                id="base_code"
                                v-model="form.base_code"
                                placeholder="KG"
                                autocomplete="off"
                            />
                            <p
                                v-if="form.errors.base_code"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.base_code }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Nama Satuan</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Kilogram"
                            autocomplete="off"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="factor">Faktor Konversi</Label>
                        <Input
                            id="factor"
                            v-model="form.factor"
                            inputmode="decimal"
                            placeholder="1.000000"
                            autocomplete="off"
                        />
                        <p class="text-xs text-muted-foreground">
                            Gunakan titik sebagai pemisah desimal, maksimal 6
                            angka di belakang koma.
                        </p>
                        <p
                            v-if="form.errors.factor"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.factor }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="size-4 rounded border"
                        />
                        Aktif dan dapat dipakai pada data baru
                    </label>

                    <DialogFooter>
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

        <ConfirmDeleteDialog
            v-model:open="deleteDialogOpen"
            title="Hapus Satuan"
            description="Satuan akan dihapus dari daftar master data."
            :subject="
                deletingUnit
                    ? `${deletingUnit.code} — ${deletingUnit.name}`
                    : ''
            "
            :note="
                deleteError ||
                'Satuan tidak dapat dihapus jika masih digunakan oleh barang.'
            "
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
