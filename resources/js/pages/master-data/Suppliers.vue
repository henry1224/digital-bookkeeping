<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Eye,
    Mail,
    MapPin,
    Pencil,
    Phone,
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

type Supplier = {
    id: number;
    code: string;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    is_active: boolean;
    updated_at: string;
};
type PageLink = { url: string | null; label: string; active: boolean };
type Filters = { search: string; status: string; per_page: string };
type CancelToken = { cancel?: () => void };

const props = defineProps<{
    filters: Filters;
    suppliers: {
        data: Supplier[];
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
const viewingSupplier = ref<Supplier | null>(null);
const editingSupplier = ref<Supplier | null>(null);
const deletingSupplier = ref<Supplier | null>(null);
const deleteProcessing = ref(false);
const form = useForm({
    code: '',
    name: '',
    phone: '',
    email: '',
    address: '',
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
    get: () => viewingSupplier.value !== null,
    set: (open) => {
        if (!open) {
            viewingSupplier.value = null;
        }
    },
});
const deleteDialogOpen = computed({
    get: () => deletingSupplier.value !== null,
    set: (open) => {
        if (!open) {
            deletingSupplier.value = null;
        }
    },
});

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

    router.get('/master-data/suppliers', activeFilters(overrides), {
        preserveState: true,
        replace: true,
        only: ['filters', 'suppliers'],
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
    editingSupplier.value = null;
    form.defaults({
        code: '',
        name: '',
        phone: '',
        email: '',
        address: '',
        is_active: true,
        updated_at: '',
    });
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
};
const openEdit = (supplier: Supplier) => {
    editingSupplier.value = supplier;
    form.defaults({
        code: supplier.code,
        name: supplier.name,
        phone: supplier.phone ?? '',
        email: supplier.email ?? '',
        address: supplier.address ?? '',
        is_active: supplier.is_active,
        updated_at: supplier.updated_at,
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

    if (editingSupplier.value) {
        form.patch(
            `/master-data/suppliers/${editingSupplier.value.id}`,
            options,
        );

        return;
    }

    form.post('/master-data/suppliers', options);
};
const toggleSupplier = (supplier: Supplier) =>
    router.patch(
        `/master-data/suppliers/${supplier.id}/toggle`,
        { updated_at: supplier.updated_at },
        { preserveScroll: true },
    );
const confirmDelete = () => {
    if (!deletingSupplier.value) {
        return;
    }

    router.delete(`/master-data/suppliers/${deletingSupplier.value.id}`, {
        data: { updated_at: deletingSupplier.value.updated_at },
        preserveScroll: true,
        onStart: () => (deleteProcessing.value = true),
        onSuccess: () => (deletingSupplier.value = null),
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
            { title: 'Supplier', href: '/master-data/suppliers' },
        ],
    },
});
</script>

<template>
    <Head title="Supplier" />
    <div class="space-y-6 p-4">
        <AdminPageHeader
            eyebrow="Master Data"
            title="Supplier"
            description="Kelola pemasok barang dan informasi kontak yang digunakan dalam proses pembelian."
        >
            <template #actions>
                <Button v-if="canCreate" @click="openCreate">
                    <Plus class="size-4" /> Tambah Supplier
                </Button>
            </template>
        </AdminPageHeader>

        <DataTableCard
            title="Daftar Supplier"
            description="Supplier nonaktif tetap tersimpan untuk menjaga riwayat transaksi."
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
                    placeholder="Cari kode, nama, telepon, atau email"
                    label="Cari supplier"
                    @clear="clearSearch"
                />
                <DataTableFilterSelect
                    v-model="filterStatus"
                    label="Filter status supplier"
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
                    variant="ghost"
                    size="sm"
                    @click="resetFilters"
                >
                    <RotateCcw class="size-3.5" /> Reset
                </Button>
            </template>

            <thead
                class="bg-gradient-to-r from-muted via-muted/70 to-card text-left text-xs tracking-wide text-muted-foreground uppercase"
            >
                <tr>
                    <th class="px-4 py-3.5">Kode</th>
                    <th class="px-4 py-3.5">Nama Supplier</th>
                    <th class="px-4 py-3.5">Kontak</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/70">
                <tr
                    v-for="supplier in suppliers.data"
                    :key="supplier.id"
                    class="transition-colors duration-200 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/20"
                >
                    <td class="px-4 py-3">
                        <span
                            class="inline-flex rounded-md bg-primary/10 px-2.5 py-1 font-mono text-xs font-medium text-primary"
                        >
                            {{ supplier.code }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ supplier.name }}</div>
                        <div class="text-xs text-muted-foreground">
                            Diperbarui {{ formatDate(supplier.updated_at) }}
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div
                            v-if="supplier.phone"
                            class="flex items-center gap-2 text-sm"
                        >
                            <Phone class="size-3.5 text-muted-foreground" />{{
                                supplier.phone
                            }}
                        </div>
                        <div
                            v-if="supplier.email"
                            class="mt-1 flex items-center gap-2 text-xs text-muted-foreground"
                        >
                            <Mail class="size-3.5" />{{ supplier.email }}
                        </div>
                        <span
                            v-if="!supplier.phone && !supplier.email"
                            class="text-muted-foreground"
                            >Belum ada</span
                        >
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="
                                supplier.is_active ? 'default' : 'secondary'
                            "
                            class="rounded-md"
                        >
                            {{ supplier.is_active ? 'Aktif' : 'Nonaktif' }}
                        </Badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end">
                            <RowActionMenu>
                                <DropdownMenuItem
                                    @select="viewingSupplier = supplier"
                                    ><Eye /> Lihat</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="openEdit(supplier)"
                                    ><Pencil /> Edit</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="toggleSupplier(supplier)"
                                >
                                    <PowerOff v-if="supplier.is_active" /><Power
                                        v-else
                                    />
                                    {{
                                        supplier.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}
                                </DropdownMenuItem>
                                <DropdownMenuSeparator v-if="canUpdate" />
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    variant="destructive"
                                    @select="deletingSupplier = supplier"
                                >
                                    <Trash2 /> Hapus
                                </DropdownMenuItem>
                            </RowActionMenu>
                        </div>
                    </td>
                </tr>
                <tr v-if="suppliers.data.length === 0">
                    <td
                        colspan="5"
                        class="px-4 py-12 text-center text-muted-foreground"
                    >
                        Supplier tidak ditemukan.
                    </td>
                </tr>
            </tbody>
            <template #footer>
                <DataTablePagination
                    :from="suppliers.from"
                    :to="suppliers.to"
                    :total="suppliers.total"
                    :links="suppliers.links"
                />
            </template>
        </DataTableCard>

        <AdminDataDialog
            v-model:open="viewDialogOpen"
            title="Detail Supplier"
            description="Informasi lengkap supplier yang dipilih."
            size="wide"
        >
            <dl v-if="viewingSupplier" class="grid gap-3 sm:grid-cols-2">
                <div
                    v-for="[label, value] in [
                        ['Kode', viewingSupplier.code],
                        ['Nama', viewingSupplier.name],
                        [
                            'Telepon',
                            viewingSupplier.phone ?? 'Belum ditentukan',
                        ],
                        ['Email', viewingSupplier.email ?? 'Belum ditentukan'],
                        [
                            'Status',
                            viewingSupplier.is_active ? 'Aktif' : 'Nonaktif',
                        ],
                        [
                            'Terakhir Diperbarui',
                            formatDate(viewingSupplier.updated_at),
                        ],
                    ]"
                    :key="label"
                    class="rounded-lg border border-border/70 bg-muted/20 p-4"
                >
                    <dt class="text-xs font-medium text-muted-foreground">
                        {{ label }}
                    </dt>
                    <dd class="mt-1 font-semibold">{{ value }}</dd>
                </div>
                <div
                    class="rounded-lg border border-border/70 bg-muted/20 p-4 sm:col-span-2"
                >
                    <dt
                        class="flex items-center gap-2 text-xs font-medium text-muted-foreground"
                    >
                        <MapPin class="size-3.5" /> Alamat
                    </dt>
                    <dd class="mt-1 font-medium whitespace-pre-line">
                        {{ viewingSupplier.address ?? 'Belum ditentukan' }}
                    </dd>
                </div>
            </dl>
            <template #footer
                ><Button variant="outline" @click="viewDialogOpen = false"
                    >Tutup</Button
                ></template
            >
        </AdminDataDialog>

        <AdminDataDialog
            v-model:open="dialogOpen"
            :title="editingSupplier ? 'Edit Supplier' : 'Tambah Supplier'"
            description="Lengkapi identitas dan kontak supplier agar proses pembelian mudah ditindaklanjuti."
            size="wide"
        >
            <form id="supplier-form" class="space-y-5" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="code">Kode</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            placeholder="SUP-001"
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
                        <Label for="name">Nama Supplier</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Nama perusahaan atau pemasok"
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
                        <Label for="phone">Nomor Telepon</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="0812 3456 7890"
                            autocomplete="tel"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="supplier@contoh.com"
                            autocomplete="email"
                        />
                        <p
                            v-if="form.errors.email"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="address">Alamat</Label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="4"
                        maxlength="1000"
                        placeholder="Alamat lengkap supplier"
                        class="min-h-24 w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/40 focus-visible:outline-none"
                    />
                    <p
                        v-if="form.errors.address"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.address }}
                    </p>
                </div>
                <label
                    class="flex items-center gap-2 rounded-lg border border-border/70 bg-muted/20 px-4 py-3 text-sm"
                >
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="size-4 rounded border"
                    />
                    Aktif dan dapat dipilih pada transaksi baru
                </label>
            </form>
            <template #footer>
                <Button
                    type="button"
                    variant="outline"
                    @click="dialogOpen = false"
                    ><X /> Batal</Button
                >
                <Button
                    type="submit"
                    form="supplier-form"
                    :disabled="form.processing"
                    ><Save /> Simpan</Button
                >
            </template>
        </AdminDataDialog>

        <ConfirmDeleteDialog
            v-model:open="deleteDialogOpen"
            title="Hapus Supplier"
            description="Supplier tidak akan muncul lagi dalam daftar."
            :subject="
                deletingSupplier
                    ? `${deletingSupplier.code} — ${deletingSupplier.name}`
                    : ''
            "
            note="Riwayat data tetap tersimpan dan dapat diperiksa kembali."
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
