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

type ItemType = 'raw_material' | 'finished_good' | 'menu' | 'non_stock';
type Option = { id: number; code: string; name: string };
type Item = {
    id: number;
    sku: string;
    name: string;
    item_type: ItemType;
    item_group_id: number;
    item_group: Option;
    base_uom_id: number;
    base_uom: Option;
    standard_cost_amount: string;
    avg_cost_amount: string;
    is_active: boolean;
    updated_at: string;
};
type PageLink = { url: string | null; label: string; active: boolean };
type Filters = {
    search: string;
    status: string;
    type: string;
    group: string;
    per_page: string;
};
type CancelToken = { cancel?: () => void };

const typeOptions: Array<{ value: ItemType; label: string }> = [
    { value: 'raw_material', label: 'Bahan Baku' },
    { value: 'finished_good', label: 'Barang Jadi' },
    { value: 'menu', label: 'Menu' },
    { value: 'non_stock', label: 'Nonstok' },
];
const props = defineProps<{
    filters: Filters;
    items: {
        data: Item[];
        links: PageLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
    itemGroups: Option[];
    unitOfMeasures: Option[];
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
const filterGroup = ref(props.filters.group ?? 'semua');
const perPage = ref(props.filters.per_page ?? '10');
const dialogOpen = ref(false);
const viewingItem = ref<Item | null>(null);
const editingItem = ref<Item | null>(null);
const deletingItem = ref<Item | null>(null);
const deleteProcessing = ref(false);
const form = useForm({
    sku: '',
    name: '',
    item_type: 'raw_material' as ItemType,
    item_group_id: '',
    base_uom_id: '',
    standard_cost_amount: '0',
    avg_cost_amount: '0',
    is_active: true,
    updated_at: '',
});
const typeLabel = (type: ItemType) =>
    typeOptions.find((option) => option.value === type)?.label ?? type;
const money = (value: string) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 2,
    }).format(Number(value));
const hasActiveFilters = computed(
    () =>
        search.value !== '' ||
        filterStatus.value !== 'semua' ||
        filterType.value !== 'semua' ||
        filterGroup.value !== 'semua' ||
        perPage.value !== '10',
);
const viewDialogOpen = computed({
    get: () => viewingItem.value !== null,
    set: (open) => {
        if (!open) {
viewingItem.value = null;
}
    },
});
const deleteDialogOpen = computed({
    get: () => deletingItem.value !== null,
    set: (open) => {
        if (!open) {
deletingItem.value = null;
}
    },
});
const activeFilters = (overrides: Partial<Filters> = {}) =>
    Object.fromEntries(
        Object.entries({
            search: search.value,
            status: filterStatus.value,
            type: filterType.value,
            group: filterGroup.value,
            per_page: perPage.value,
            ...overrides,
        }).filter(([key, value]) =>
            key === 'search'
                ? String(value ?? '').trim() !== ''
                : value !== 'semua',
        ),
    );
let cancelToken: CancelToken | null = null;
let skipNextSearchWatch = false;
const visitFilters = (overrides: Partial<Filters> = {}) => {
    cancelToken?.cancel?.();
    let visitToken: CancelToken | null = null;
    router.get('/master-data/items', activeFilters(overrides), {
        preserveState: true,
        replace: true,
        only: ['filters', 'items'],
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
const applyFilters = (overrides: Partial<Filters> = {}, immediate = false) => {
    if ('search' in overrides && search.value !== (overrides.search ?? '')) {
        skipNextSearchWatch = true;
        search.value = overrides.search ?? '';
    }

    if ('status' in overrides) {
filterStatus.value = overrides.status ?? 'semua';
}

    if ('type' in overrides) {
filterType.value = overrides.type ?? 'semua';
}

    if ('group' in overrides) {
filterGroup.value = overrides.group ?? 'semua';
}

    if ('per_page' in overrides) {
perPage.value = overrides.per_page ?? '10';
}

    if (immediate) {
        requestFilters.cancel();
        visitFilters(overrides);
    } else {
requestFilters(overrides);
}
};
const clearSearch = () => {
    searchFilters.cancel();
    requestFilters.cancel();
    applyFilters({ search: '' }, true);
};
const resetFilters = () => {
    searchFilters.cancel();
    requestFilters.cancel();
    applyFilters(
        {
            search: '',
            status: 'semua',
            type: 'semua',
            group: 'semua',
            per_page: '10',
        },
        true,
    );
};
const resetForm = () => {
    form.reset();
    form.clearErrors();
    editingItem.value = null;
};
const openCreate = () => {
    resetForm();
    dialogOpen.value = true;
};
const openEdit = (item: Item) => {
    editingItem.value = item;
    form.clearErrors();
    form.sku = item.sku;
    form.name = item.name;
    form.item_type = item.item_type;
    form.item_group_id = String(item.item_group_id);
    form.base_uom_id = String(item.base_uom_id);
    form.standard_cost_amount = item.standard_cost_amount;
    form.avg_cost_amount = item.avg_cost_amount;
    form.is_active = item.is_active;
    form.updated_at = item.updated_at;
    dialogOpen.value = true;
};
const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
            resetForm();
        },
    };

    if (editingItem.value) {
form.patch(`/master-data/items/${editingItem.value.id}`, options);
} else {
form.post('/master-data/items', options);
}
};
const toggleItem = (item: Item) =>
    router.patch(
        `/master-data/items/${item.id}/toggle`,
        { updated_at: item.updated_at },
        { preserveScroll: true },
    );
const confirmDelete = () => {
    if (!deletingItem.value) {
return;
}

    deleteProcessing.value = true;
    router.delete(`/master-data/items/${deletingItem.value.id}`, {
        data: { updated_at: deletingItem.value.updated_at },
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
        },
        onSuccess: () => {
            deletingItem.value = null;
        },
    });
};
watch(search, (value) => {
    if (skipNextSearchWatch) {
        skipNextSearchWatch = false;

        return;
    }

    searchFilters(value);
});
watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search;
        filterStatus.value = filters.status;
        filterType.value = filters.type;
        filterGroup.value = filters.group;
        perPage.value = filters.per_page;
    },
);
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Item', href: '/master-data/items' },
        ],
    },
});
</script>

<template>
    <Head title="Item" />
    <div class="space-y-6 p-4">
        <AdminPageHeader
            eyebrow="Master Data"
            title="Item"
            description="Kelola barang, bahan baku, menu, satuan, dan nilai biaya yang digunakan dalam transaksi."
        >
            <template #actions
                ><Button v-if="canCreate" @click="openCreate"
                    ><Plus /> Tambah Item</Button
                ></template
            >
        </AdminPageHeader>
        <DataTableCard
            title="Daftar Item"
            description="Item nonaktif tetap tersimpan, tetapi tidak dapat dipilih pada transaksi baru."
        >
            <template #filters>
                <DataTableFilterSelect
                    v-model="perPage"
                    label="Jumlah data per halaman"
                    @change="applyFilters({ per_page: $event }, true)"
                    ><option value="10">10 data</option>
                    <option value="25">25 data</option>
                    <option value="50">50 data</option></DataTableFilterSelect
                >
                <DataTableSearch
                    v-model="search"
                    class="w-full sm:max-w-xs"
                    placeholder="Cari SKU atau nama item"
                    label="Cari item"
                    @clear="clearSearch"
                />
                <DataTableFilterSelect
                    v-model="filterType"
                    label="Filter jenis item"
                    @change="applyFilters({ type: $event })"
                    ><option value="semua">Semua jenis</option>
                    <option
                        v-for="option in typeOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option></DataTableFilterSelect
                >
                <DataTableFilterSelect
                    v-model="filterGroup"
                    label="Filter kelompok item"
                    @change="applyFilters({ group: $event })"
                    ><option value="semua">Semua kelompok</option>
                    <option
                        v-for="group in itemGroups"
                        :key="group.id"
                        :value="String(group.id)"
                    >
                        {{ group.code }} — {{ group.name }}
                    </option></DataTableFilterSelect
                >
                <DataTableFilterSelect
                    v-model="filterStatus"
                    label="Filter status item"
                    @change="applyFilters({ status: $event })"
                    ><option value="semua">Semua status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">
                        Nonaktif
                    </option></DataTableFilterSelect
                >
            </template>
            <template #meta
                ><Button
                    v-if="hasActiveFilters"
                    variant="ghost"
                    size="sm"
                    @click="resetFilters"
                    ><RotateCcw /> Reset</Button
                ></template
            >
            <thead
                class="bg-gradient-to-r from-muted via-muted/70 to-card text-left text-xs tracking-wide text-muted-foreground uppercase"
            >
                <tr>
                    <th class="px-4 py-3.5">Item</th>
                    <th class="px-4 py-3.5">Jenis</th>
                    <th class="px-4 py-3.5">Kelompok</th>
                    <th class="px-4 py-3.5">Satuan</th>
                    <th class="px-4 py-3.5">Biaya Rata-rata</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/70">
                <tr
                    v-for="item in items.data"
                    :key="item.id"
                    class="transition-colors duration-200 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/20"
                >
                    <td class="px-4 py-3">
                        <div class="font-semibold">{{ item.name }}</div>
                        <div
                            class="mt-1 font-mono text-xs font-semibold text-primary"
                        >
                            {{ item.sku }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-muted-foreground">
                        {{ typeLabel(item.item_type) }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium">
                            {{ item.item_group.name }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ item.item_group.code }}
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <Badge variant="outline" class="rounded-md">{{
                            item.base_uom.code
                        }}</Badge>
                    </td>
                    <td class="px-4 py-3 font-medium tabular-nums">
                        {{ money(item.avg_cost_amount) }}
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="item.is_active ? 'default' : 'secondary'"
                            class="rounded-md"
                            >{{ item.is_active ? 'Aktif' : 'Nonaktif' }}</Badge
                        >
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end">
                            <RowActionMenu
                                ><DropdownMenuItem @select="viewingItem = item"
                                    ><Eye /> Lihat</DropdownMenuItem
                                ><DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="openEdit(item)"
                                    ><Pencil /> Edit</DropdownMenuItem
                                ><DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="toggleItem(item)"
                                    ><PowerOff v-if="item.is_active" /><Power
                                        v-else
                                    />{{
                                        item.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}</DropdownMenuItem
                                ><DropdownMenuSeparator
                                    v-if="canUpdate"
                                /><DropdownMenuItem
                                    v-if="canUpdate"
                                    variant="destructive"
                                    @select="deletingItem = item"
                                    ><Trash2 /> Hapus</DropdownMenuItem
                                ></RowActionMenu
                            >
                        </div>
                    </td>
                </tr>
                <tr v-if="items.data.length === 0">
                    <td
                        colspan="7"
                        class="px-4 py-12 text-center text-muted-foreground"
                    >
                        Item tidak ditemukan.
                    </td>
                </tr>
            </tbody>
            <template #footer
                ><DataTablePagination
                    :from="items.from"
                    :to="items.to"
                    :total="items.total"
                    :links="items.links"
            /></template>
        </DataTableCard>

        <AdminDataDialog
            v-model:open="viewDialogOpen"
            title="Detail Item"
            description="Informasi lengkap item yang dipilih."
            size="wide"
        >
            <dl v-if="viewingItem" class="grid gap-3 sm:grid-cols-2">
                <div
                    v-for="[label, value] in [
                        ['SKU', viewingItem.sku],
                        ['Nama', viewingItem.name],
                        ['Jenis', typeLabel(viewingItem.item_type)],
                        [
                            'Kelompok',
                            `${viewingItem.item_group.code} — ${viewingItem.item_group.name}`,
                        ],
                        [
                            'Satuan Dasar',
                            `${viewingItem.base_uom.code} — ${viewingItem.base_uom.name}`,
                        ],
                        [
                            'Biaya Standar',
                            money(viewingItem.standard_cost_amount),
                        ],
                        ['Biaya Rata-rata', money(viewingItem.avg_cost_amount)],
                        [
                            'Status',
                            viewingItem.is_active ? 'Aktif' : 'Nonaktif',
                        ],
                        [
                            'Terakhir Diperbarui',
                            formatDate(viewingItem.updated_at),
                        ],
                    ]"
                    :key="label"
                    class="rounded-lg border border-border/70 bg-muted/20 p-3"
                >
                    <dt class="text-xs font-medium text-muted-foreground">
                        {{ label }}
                    </dt>
                    <dd class="mt-1 font-medium">{{ value }}</dd>
                </div>
            </dl>
            <template #footer
                ><Button
                    type="button"
                    variant="outline"
                    @click="viewDialogOpen = false"
                    ><X /> Tutup</Button
                ></template
            >
        </AdminDataDialog>

        <AdminDataDialog
            v-model:open="dialogOpen"
            :title="editingItem ? 'Edit Item' : 'Tambah Item'"
            :description="
                editingItem
                    ? 'Perbarui informasi item yang dipilih.'
                    : 'Lengkapi informasi item baru.'
            "
            size="wide"
        >
            <form id="item-form" class="grid gap-5" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="sku">SKU</Label
                        ><Input
                            id="sku"
                            v-model="form.sku"
                            placeholder="BEEF-SIRLOIN"
                            autocomplete="off"
                        />
                        <p
                            v-if="form.errors.sku"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.sku }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="name">Nama Item</Label
                        ><Input
                            id="name"
                            v-model="form.name"
                            placeholder="Daging Sirloin"
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
                        <Label for="item_type">Jenis Item</Label
                        ><select
                            id="item_type"
                            v-model="form.item_type"
                            class="h-10 rounded-md border border-input bg-background px-3 text-sm"
                        >
                            <option
                                v-for="option in typeOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.item_type"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.item_type }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="item_group_id">Kelompok Item</Label
                        ><select
                            id="item_group_id"
                            v-model="form.item_group_id"
                            class="h-10 rounded-md border border-input bg-background px-3 text-sm"
                        >
                            <option value="" disabled>Pilih kelompok</option>
                            <option
                                v-for="group in itemGroups"
                                :key="group.id"
                                :value="String(group.id)"
                            >
                                {{ group.code }} — {{ group.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.item_group_id"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.item_group_id }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="base_uom_id">Satuan Dasar</Label
                        ><select
                            id="base_uom_id"
                            v-model="form.base_uom_id"
                            class="h-10 rounded-md border border-input bg-background px-3 text-sm"
                        >
                            <option value="" disabled>Pilih satuan</option>
                            <option
                                v-for="uom in unitOfMeasures"
                                :key="uom.id"
                                :value="String(uom.id)"
                            >
                                {{ uom.code }} — {{ uom.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.base_uom_id"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.base_uom_id }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="standard_cost_amount">Biaya Standar</Label
                        ><Input
                            id="standard_cost_amount"
                            v-model="form.standard_cost_amount"
                            type="number"
                            min="0"
                            step="0.01"
                        />
                        <p
                            v-if="form.errors.standard_cost_amount"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.standard_cost_amount }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="avg_cost_amount">Biaya Rata-rata</Label
                        ><Input
                            id="avg_cost_amount"
                            v-model="form.avg_cost_amount"
                            type="number"
                            min="0"
                            step="0.01"
                        />
                        <p
                            v-if="form.errors.avg_cost_amount"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.avg_cost_amount }}
                        </p>
                    </div>
                </div>
                <label
                    class="flex items-center gap-2 rounded-lg border border-border/70 bg-muted/20 px-4 py-3 text-sm"
                    ><input
                        v-model="form.is_active"
                        type="checkbox"
                        class="size-4 rounded border"
                    />Aktif dan dapat dipilih pada transaksi baru</label
                >
            </form>
            <template #footer
                ><Button
                    type="button"
                    variant="outline"
                    @click="dialogOpen = false"
                    ><X /> Batal</Button
                ><Button
                    type="submit"
                    form="item-form"
                    :disabled="form.processing"
                    ><Save /> Simpan</Button
                ></template
            >
        </AdminDataDialog>

        <ConfirmDeleteDialog
            v-model:open="deleteDialogOpen"
            title="Hapus Item"
            description="Item tidak akan muncul lagi dalam daftar."
            :subject="
                deletingItem ? `${deletingItem.sku} — ${deletingItem.name}` : ''
            "
            note="Riwayat data tetap tersimpan dan dapat diperiksa kembali."
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
