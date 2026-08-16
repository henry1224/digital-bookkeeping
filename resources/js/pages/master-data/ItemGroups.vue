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
import AdminSearchSelect from '@/components/admin/AdminSearchSelect.vue';
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

type Option = { id?: number; code: string; name: string };
type ItemGroup = {
    id: number;
    code: string;
    name: string;
    parent_id: number | null;
    parent: Option | null;
    inventory_account_code: string | null;
    revenue_account_code: string | null;
    is_active: boolean;
    items_count: number;
    children_count: number;
    updated_at: string;
};
type PageLink = { url: string | null; label: string; active: boolean };
type Filters = { search: string; status: string; per_page: string };
type CancelToken = { cancel?: () => void };

const props = defineProps<{
    filters: Filters;
    groups: {
        data: ItemGroup[];
        links: PageLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
    parentOptions: Option[];
    accountOptions: Option[];
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
const viewingGroup = ref<ItemGroup | null>(null);
const editingGroup = ref<ItemGroup | null>(null);
const deletingGroup = ref<ItemGroup | null>(null);
const deleteProcessing = ref(false);
const deleteError = ref('');
const form = useForm({
    code: '',
    name: '',
    parent_id: '',
    inventory_account_code: '',
    revenue_account_code: '',
    is_active: true,
    updated_at: '',
});

const hasActiveFilters = computed(
    () =>
        search.value !== '' ||
        filterStatus.value !== 'semua' ||
        perPage.value !== '10',
);
const viewDialogOpen = computed({
    get: () => viewingGroup.value !== null,
    set: (open) => {
        if (!open) {
            viewingGroup.value = null;
        }
    },
});
const deleteDialogOpen = computed({
    get: () => deletingGroup.value !== null,
    set: (open) => {
        if (!open) {
            deletingGroup.value = null;
            deleteError.value = '';
        }
    },
});
const availableParents = computed(() =>
    props.parentOptions.filter(
        (option) => option.id !== editingGroup.value?.id,
    ),
);
const parentSelectOptions = computed(() =>
    availableParents.value.map((option) => ({
        value: String(option.id),
        label: `${option.code} — ${option.name}`,
    })),
);
const accountSelectOptions = computed(() =>
    props.accountOptions.map((option) => ({
        value: option.code,
        label: `${option.code} — ${option.name}`,
    })),
);
const accountLabel = (code: string | null) => {
    if (!code) {
        return 'Belum ditentukan';
    }

    const account = props.accountOptions.find((option) => option.code === code);

    return account ? `${account.code} — ${account.name}` : code;
};

const activeFilters = (overrides: Partial<Filters> = {}) => {
    const filters = {
        search: search.value,
        status: filterStatus.value,
        per_page: perPage.value,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(filters).filter(([key, value]) =>
            key === 'search'
                ? String(value ?? '').trim() !== ''
                : value !== 'semua',
        ),
    );
};

let cancelToken: CancelToken | null = null;
let skipNextSearchWatch = false;
const visitFilters = (overrides: Partial<Filters> = {}) => {
    cancelToken?.cancel?.();
    let visitToken: CancelToken | null = null;
    router.get('/master-data/item-groups', activeFilters(overrides), {
        preserveState: true,
        replace: true,
        only: ['filters', 'groups'],
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
    editingGroup.value = null;
    form.defaults({
        code: '',
        name: '',
        parent_id: '',
        inventory_account_code: '',
        revenue_account_code: '',
        is_active: true,
        updated_at: '',
    });
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
};
const openEdit = (group: ItemGroup) => {
    editingGroup.value = group;
    form.defaults({
        code: group.code,
        name: group.name,
        parent_id: group.parent_id ? String(group.parent_id) : '',
        inventory_account_code: group.inventory_account_code ?? '',
        revenue_account_code: group.revenue_account_code ?? '',
        is_active: group.is_active,
        updated_at: group.updated_at,
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

    if (editingGroup.value) {
        form.patch(
            `/master-data/item-groups/${editingGroup.value.id}`,
            options,
        );

        return;
    }

    form.post('/master-data/item-groups', options);
};
const toggleGroup = (group: ItemGroup) =>
    router.patch(
        `/master-data/item-groups/${group.id}/toggle`,
        { updated_at: group.updated_at },
        { preserveScroll: true },
    );
const confirmDelete = () => {
    if (!deletingGroup.value) {
        return;
    }

    router.delete(`/master-data/item-groups/${deletingGroup.value.id}`, {
        data: { updated_at: deletingGroup.value.updated_at },
        preserveScroll: true,
        onStart: () => (deleteProcessing.value = true),
        onSuccess: () => (deletingGroup.value = null),
        onError: (errors) => {
            deleteError.value =
                String(errors.item_group ?? '') ||
                'Kelompok item gagal dihapus. Muat ulang lalu coba lagi.';
        },
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
            { title: 'Kelompok Item', href: '/master-data/item-groups' },
        ],
    },
});
</script>

<template>
    <Head title="Kelompok Item" />
    <div class="space-y-6 p-4">
        <AdminPageHeader
            eyebrow="Master Data"
            title="Kelompok Item"
            description="Atur pengelompokan barang agar pencatatan stok, penjualan, dan laporan lebih rapi."
        >
            <template #actions>
                <Button v-if="canCreate" @click="openCreate">
                    <Plus class="size-4" />
                    Tambah Kelompok
                </Button>
            </template>
        </AdminPageHeader>

        <DataTableCard
            title="Daftar Kelompok Item"
            description="Kelompok yang masih digunakan oleh barang tidak dapat dihapus."
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
                    placeholder="Cari kode atau nama kelompok"
                    label="Cari kelompok item"
                    @clear="clearSearch"
                />
                <DataTableFilterSelect
                    v-model="filterStatus"
                    label="Filter status kelompok"
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
            </template>

            <thead
                class="bg-gradient-to-r from-muted via-muted/70 to-card text-left text-xs tracking-wide text-muted-foreground uppercase"
            >
                <tr>
                    <th class="px-4 py-3.5">Kode</th>
                    <th class="px-4 py-3.5">Nama Kelompok</th>
                    <th class="px-4 py-3.5">Induk</th>
                    <th class="px-4 py-3.5 text-center">Barang</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/70">
                <tr
                    v-for="group in groups.data"
                    :key="group.id"
                    class="transition-colors duration-200 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/20"
                >
                    <td class="px-4 py-3 font-medium">
                        <span
                            class="inline-flex rounded-md bg-primary/10 px-2.5 py-1 font-mono text-xs text-primary"
                        >
                            {{ group.code }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ group.name }}</div>
                        <div class="text-xs text-muted-foreground">
                            Diperbarui {{ formatDate(group.updated_at) }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-muted-foreground">
                        {{ group.parent ? group.parent.name : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <Badge variant="outline" class="rounded-md"
                            >{{ group.items_count }} barang</Badge
                        >
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="group.is_active ? 'default' : 'secondary'"
                            class="rounded-md"
                        >
                            {{ group.is_active ? 'Aktif' : 'Nonaktif' }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end">
                            <RowActionMenu>
                                <DropdownMenuItem
                                    @select="viewingGroup = group"
                                >
                                    <Eye /> Lihat
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="openEdit(group)"
                                >
                                    <Pencil /> Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="toggleGroup(group)"
                                >
                                    <PowerOff v-if="group.is_active" />
                                    <Power v-else />
                                    {{
                                        group.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}
                                </DropdownMenuItem>
                                <DropdownMenuSeparator v-if="canUpdate" />
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    variant="destructive"
                                    @select="deletingGroup = group"
                                >
                                    <Trash2 /> Hapus
                                </DropdownMenuItem>
                            </RowActionMenu>
                        </div>
                    </td>
                </tr>
                <tr v-if="groups.data.length === 0">
                    <td
                        colspan="6"
                        class="px-4 py-12 text-center text-muted-foreground"
                    >
                        Kelompok item tidak ditemukan.
                    </td>
                </tr>
            </tbody>
            <template #footer>
                <DataTablePagination
                    :from="groups.from"
                    :to="groups.to"
                    :total="groups.total"
                    :links="groups.links"
                />
            </template>
        </DataTableCard>

        <AdminDataDialog
            v-model:open="viewDialogOpen"
            title="Detail Kelompok Item"
            description="Informasi lengkap kelompok yang dipilih."
            size="wide"
        >
            <dl v-if="viewingGroup" class="grid gap-3 sm:grid-cols-2">
                <div
                    v-for="[label, value] in [
                        ['Kode', viewingGroup.code],
                        ['Nama', viewingGroup.name],
                        [
                            'Kelompok Induk',
                            viewingGroup.parent?.name ?? 'Tidak ada',
                        ],
                        ['Jumlah Barang', `${viewingGroup.items_count} barang`],
                        [
                            'Akun Persediaan',
                            accountLabel(viewingGroup.inventory_account_code),
                        ],
                        [
                            'Akun Pendapatan',
                            accountLabel(viewingGroup.revenue_account_code),
                        ],
                        [
                            'Status',
                            viewingGroup.is_active ? 'Aktif' : 'Nonaktif',
                        ],
                        [
                            'Terakhir Diperbarui',
                            formatDate(viewingGroup.updated_at),
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
            :title="
                editingGroup ? 'Edit Kelompok Item' : 'Tambah Kelompok Item'
            "
            description="Lengkapi pengelompokan barang dan akun yang digunakan dalam pencatatan."
            size="wide"
        >
            <form
                id="item-group-form"
                class="space-y-5"
                @submit.prevent="submit"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="code">Kode</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            placeholder="RAW-MEAT"
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
                        <Label for="name">Nama Kelompok</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Daging"
                            autocomplete="off"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="parent_id">Kelompok Induk</Label>
                    <AdminSearchSelect
                        v-model="form.parent_id"
                        :options="parentSelectOptions"
                        placeholder="Cari kelompok induk"
                        empty-label="Tidak ada"
                    />
                    <p
                        v-if="form.errors.parent_id"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.parent_id }}
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="inventory_account_code"
                            >Akun Persediaan</Label
                        >
                        <AdminSearchSelect
                            v-model="form.inventory_account_code"
                            :options="accountSelectOptions"
                            placeholder="Cari akun persediaan"
                            empty-label="Belum ditentukan"
                        />
                        <p
                            v-if="form.errors.inventory_account_code"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.inventory_account_code }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="revenue_account_code"
                            >Akun Pendapatan</Label
                        >
                        <AdminSearchSelect
                            v-model="form.revenue_account_code"
                            :options="accountSelectOptions"
                            placeholder="Cari akun pendapatan"
                            empty-label="Belum ditentukan"
                        />
                        <p
                            v-if="form.errors.revenue_account_code"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.revenue_account_code }}
                        </p>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="size-4 rounded border"
                    />
                    Aktif dan dapat dipakai pada data baru
                </label>
            </form>
            <template #footer>
                <Button
                    type="button"
                    variant="outline"
                    @click="dialogOpen = false"
                >
                    <X /> Batal
                </Button>
                <Button
                    type="submit"
                    form="item-group-form"
                    :disabled="form.processing"
                >
                    <Save /> Simpan
                </Button>
            </template>
        </AdminDataDialog>

        <ConfirmDeleteDialog
            v-model:open="deleteDialogOpen"
            title="Hapus Kelompok Item"
            description="Kelompok tidak akan muncul lagi dalam daftar."
            :subject="
                deletingGroup
                    ? `${deletingGroup.code} — ${deletingGroup.name}`
                    : ''
            "
            :note="
                deleteError ||
                'Kelompok tidak dapat dihapus jika masih digunakan oleh barang.'
            "
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
