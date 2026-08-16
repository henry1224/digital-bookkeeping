<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronRight,
    Eye,
    GitBranch,
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

type AccountType =
    'asset' | 'liability' | 'equity' | 'revenue' | 'cogs' | 'expense';
type ParentOption = {
    id: number;
    code: string;
    name: string;
    type: AccountType;
};
type Account = {
    id: number;
    code: string;
    name: string;
    type: AccountType;
    parent_id: number | null;
    parent: ParentOption | null;
    is_postable: boolean;
    report_group: string | null;
    is_active: boolean;
    children_count: number;
    updated_at: string;
};
type PageLink = { url: string | null; label: string; active: boolean };
type Filters = {
    search: string;
    status: string;
    type: string;
    postable: string;
    report_group: string;
    per_page: string;
};
type CancelToken = { cancel?: () => void };

const typeOptions: Array<{ value: AccountType; label: string }> = [
    { value: 'asset', label: 'Aset' },
    { value: 'liability', label: 'Liabilitas' },
    { value: 'equity', label: 'Ekuitas' },
    { value: 'revenue', label: 'Pendapatan' },
    { value: 'cogs', label: 'Harga Pokok Penjualan' },
    { value: 'expense', label: 'Beban' },
];
const reportOptions = [
    ['balance_sheet', 'Neraca'],
    ['current_asset', 'Aset Lancar'],
    ['current_liability', 'Liabilitas Lancar'],
    ['equity', 'Ekuitas'],
    ['profit_loss', 'Laba Rugi'],
    ['revenue', 'Pendapatan'],
    ['cogs', 'Harga Pokok Penjualan'],
    ['operating_expense', 'Beban Operasional'],
] as const;

const props = defineProps<{
    filters: Filters;
    accounts: {
        data: Account[];
        links: PageLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
    parentOptions: ParentOption[];
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
const filterPostable = ref(props.filters.postable ?? 'semua');
const filterReportGroup = ref(props.filters.report_group ?? 'semua');
const perPage = ref(props.filters.per_page ?? '10');
const dialogOpen = ref(false);
const viewingAccount = ref<Account | null>(null);
const editingAccount = ref<Account | null>(null);
const deletingAccount = ref<Account | null>(null);
const deleteProcessing = ref(false);
const deleteError = ref('');
const expandedAccounts = ref(new Set<number>());
const form = useForm({
    code: '',
    name: '',
    type: 'asset' as AccountType,
    parent_id: '',
    is_postable: true,
    report_group: '',
    is_active: true,
    updated_at: '',
});

const typeLabel = (type: AccountType) =>
    typeOptions.find((option) => option.value === type)?.label ?? type;
const reportLabel = (group: string | null) =>
    reportOptions.find(([value]) => value === group)?.[1] ?? 'Belum ditentukan';
const availableParents = computed(() =>
    props.parentOptions.filter(
        (option) =>
            option.id !== editingAccount.value?.id && option.type === form.type,
    ),
);
const parentSelectOptions = computed(() =>
    availableParents.value.map((option) => ({
        value: String(option.id),
        label: `${option.code} — ${option.name}`,
    })),
);
type TreeAccount = Account & { treeDepth: number; childrenCount: number };
const parentAccountIds = computed(() => {
    const parentIds = new Set(
        props.accounts.data
            .map((account) => account.parent_id)
            .filter((id): id is number => id !== null),
    );

    return props.accounts.data
        .filter((account) => parentIds.has(account.id))
        .map((account) => account.id);
});
const treeAccounts = computed<TreeAccount[]>(() => {
    const ids = new Set(props.accounts.data.map((account) => account.id));
    const children = new Map<number | null, Account[]>();

    for (const account of props.accounts.data) {
        const parentId =
            account.parent_id && ids.has(account.parent_id)
                ? account.parent_id
                : null;
        children.set(parentId, [...(children.get(parentId) ?? []), account]);
    }

    const result: TreeAccount[] = [];
    const visit = (account: Account, depth: number) => {
        const descendants = children.get(account.id) ?? [];
        result.push({
            ...account,
            treeDepth: depth,
            childrenCount: descendants.length,
        });

        if (expandedAccounts.value.has(account.id)) {
            descendants.forEach((child) => visit(child, depth + 1));
        }
    };

    (children.get(null) ?? []).forEach((account) => visit(account, 0));

    return result;
});
const hasTreeAccounts = computed(() => parentAccountIds.value.length > 0);
const allTreeAccountsOpen = computed(
    () =>
        hasTreeAccounts.value &&
        parentAccountIds.value.every((id) => expandedAccounts.value.has(id)),
);
const toggleTreeAccount = (account: TreeAccount) => {
    if (!account.childrenCount) {
        return;
    }

    const next = new Set(expandedAccounts.value);

    if (next.has(account.id)) {
        next.delete(account.id);
    } else {
        next.add(account.id);
    }

    expandedAccounts.value = next;
};
const toggleAllTreeAccounts = () => {
    expandedAccounts.value = allTreeAccountsOpen.value
        ? new Set()
        : new Set(parentAccountIds.value);
};
const hasActiveFilters = computed(
    () =>
        search.value !== '' ||
        filterStatus.value !== 'semua' ||
        filterType.value !== 'semua' ||
        filterPostable.value !== 'semua' ||
        filterReportGroup.value !== 'semua' ||
        perPage.value !== '10',
);
const viewDialogOpen = computed({
    get: () => viewingAccount.value !== null,
    set: (open) => {
        if (!open) {
            viewingAccount.value = null;
        }
    },
});
const deleteDialogOpen = computed({
    get: () => deletingAccount.value !== null,
    set: (open) => {
        if (!open) {
            deletingAccount.value = null;
            deleteError.value = '';
        }
    },
});

const activeFilters = (overrides: Partial<Filters> = {}) => {
    const filters = {
        search: search.value,
        status: filterStatus.value,
        type: filterType.value,
        postable: filterPostable.value,
        report_group: filterReportGroup.value,
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

    router.get('/master-data/accounts', activeFilters(overrides), {
        preserveState: true,
        replace: true,
        only: ['filters', 'accounts'],
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
    for (const [key, value] of Object.entries(overrides)) {
        if (key === 'search' && search.value !== value) {
            skipNextSearchWatch = true;
            search.value = value ?? '';
        }

        if (key === 'status') {
            filterStatus.value = value ?? 'semua';
        }

        if (key === 'type') {
            filterType.value = value ?? 'semua';
        }

        if (key === 'postable') {
            filterPostable.value = value ?? 'semua';
        }

        if (key === 'report_group') {
            filterReportGroup.value = value ?? 'semua';
        }

        if (key === 'per_page') {
            perPage.value = value ?? '10';
        }
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
        {
            search: '',
            status: 'semua',
            type: 'semua',
            postable: 'semua',
            report_group: 'semua',
            per_page: '10',
        },
        true,
    );

const openCreate = () => {
    editingAccount.value = null;
    form.defaults({
        code: '',
        name: '',
        type: 'asset',
        parent_id: '',
        is_postable: true,
        report_group: '',
        is_active: true,
        updated_at: '',
    });
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
};
const openEdit = (account: Account) => {
    editingAccount.value = account;
    form.defaults({
        code: account.code,
        name: account.name,
        type: account.type,
        parent_id: account.parent_id ? String(account.parent_id) : '',
        is_postable: account.is_postable,
        report_group: account.report_group ?? '',
        is_active: account.is_active,
        updated_at: account.updated_at,
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

    if (editingAccount.value) {
        form.patch(`/master-data/accounts/${editingAccount.value.id}`, options);

        return;
    }

    form.post('/master-data/accounts', options);
};
const toggleAccount = (account: Account) =>
    router.patch(
        `/master-data/accounts/${account.id}/toggle`,
        { updated_at: account.updated_at },
        { preserveScroll: true },
    );
const confirmDelete = () => {
    if (!deletingAccount.value) {
        return;
    }

    router.delete(`/master-data/accounts/${deletingAccount.value.id}`, {
        data: { updated_at: deletingAccount.value.updated_at },
        preserveScroll: true,
        onStart: () => (deleteProcessing.value = true),
        onSuccess: () => (deletingAccount.value = null),
        onError: (errors) => {
            deleteError.value =
                String(errors.account ?? '') ||
                'Akun gagal dihapus. Muat ulang lalu coba lagi.';
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
watch(
    () => props.accounts.data,
    () => {
        expandedAccounts.value = new Set(parentAccountIds.value);
    },
    { immediate: true },
);
watch(
    () => form.type,
    () => {
        if (
            !availableParents.value.some(
                (option) => String(option.id) === form.parent_id,
            )
        ) {
            form.parent_id = '';
        }
    },
);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Daftar Akun', href: '/master-data/accounts' },
        ],
    },
});
</script>

<template>
    <Head title="Daftar Akun" />
    <div class="space-y-6 p-4">
        <AdminPageHeader
            eyebrow="Master Data"
            title="Daftar Akun"
            description="Atur susunan akun yang digunakan untuk pencatatan dan penyajian laporan keuangan."
        >
            <template #actions>
                <Button v-if="canCreate" @click="openCreate"
                    ><Plus /> Tambah Akun</Button
                >
            </template>
        </AdminPageHeader>

        <DataTableCard
            title="Chart of Accounts"
            description="Akun yang sudah digunakan tidak dapat dihapus. Nonaktifkan jika tidak digunakan lagi."
        >
            <template #filters>
                <Button
                    v-if="hasTreeAccounts"
                    type="button"
                    variant="outline"
                    class="shrink-0"
                    @click="toggleAllTreeAccounts"
                >
                    <ChevronDown v-if="allTreeAccountsOpen" class="size-4" />
                    <ChevronRight v-else class="size-4" />
                    {{ allTreeAccountsOpen ? 'Tutup Semua' : 'Buka Semua' }}
                </Button>
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
                    class="w-full sm:max-w-xs"
                    placeholder="Cari kode atau nama akun"
                    label="Cari akun"
                    @clear="clearSearch"
                />
                <DataTableFilterSelect
                    v-model="filterType"
                    label="Filter jenis akun"
                    @change="applyFilters({ type: $event })"
                >
                    <option value="semua">Semua jenis</option>
                    <option
                        v-for="option in typeOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </DataTableFilterSelect>
                <DataTableFilterSelect
                    v-model="filterReportGroup"
                    label="Filter kelompok laporan"
                    @change="applyFilters({ report_group: $event })"
                >
                    <option value="semua">Semua laporan</option>
                    <option
                        v-for="[value, label] in reportOptions"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </DataTableFilterSelect>
                <DataTableFilterSelect
                    v-model="filterPostable"
                    label="Filter penggunaan transaksi"
                    @change="applyFilters({ postable: $event })"
                >
                    <option value="semua">Semua penggunaan</option>
                    <option value="ya">Akun transaksi</option>
                    <option value="tidak">Akun kelompok</option>
                </DataTableFilterSelect>
                <DataTableFilterSelect
                    v-model="filterStatus"
                    label="Filter status akun"
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
                    ><RotateCcw /> Reset</Button
                >
            </template>

            <thead
                class="bg-gradient-to-r from-muted via-muted/70 to-card text-left text-xs tracking-wide text-muted-foreground uppercase"
            >
                <tr>
                    <th class="px-4 py-3.5">Akun</th>
                    <th class="px-4 py-3.5">Jenis</th>
                    <th class="px-4 py-3.5">Penggunaan</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/70">
                <tr
                    v-for="account in treeAccounts"
                    :key="account.id"
                    class="transition-colors duration-200 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/20"
                >
                    <td class="px-4 py-3">
                        <div
                            class="flex min-w-72 items-start gap-2 border-l border-transparent"
                            :class="account.treeDepth ? 'border-border/70' : ''"
                            :style="{
                                paddingLeft: `${Math.min(account.treeDepth, 4) * 1.5}rem`,
                            }"
                        >
                            <button
                                v-if="account.childrenCount"
                                type="button"
                                class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-md border border-border bg-card text-muted-foreground transition-colors hover:border-primary/40 hover:bg-primary/10 hover:text-primary"
                                :aria-label="
                                    expandedAccounts.has(account.id)
                                        ? `Tutup turunan ${account.name}`
                                        : `Buka turunan ${account.name}`
                                "
                                @click="toggleTreeAccount(account)"
                            >
                                <ChevronDown
                                    v-if="expandedAccounts.has(account.id)"
                                    class="size-4"
                                />
                                <ChevronRight v-else class="size-4" />
                            </button>
                            <span
                                v-else
                                class="size-7 shrink-0"
                                aria-hidden="true"
                            />
                            <div>
                                <div class="font-semibold">
                                    {{ account.name }}
                                </div>
                                <div class="mt-1 flex gap-2 text-xs">
                                    <span
                                        class="font-mono font-semibold text-primary"
                                    >
                                        {{ account.code }}
                                    </span>
                                    <span class="text-muted-foreground">
                                        {{ reportLabel(account.report_group) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-muted-foreground">
                        {{ typeLabel(account.type) }}
                    </td>
                    <td class="px-4 py-3">
                        <Badge variant="outline" class="rounded-md"
                            ><GitBranch
                                v-if="!account.is_postable"
                                class="mr-1 size-3"
                            />{{
                                account.is_postable
                                    ? 'Transaksi'
                                    : `${account.children_count} turunan`
                            }}</Badge
                        >
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="
                                account.is_active ? 'default' : 'secondary'
                            "
                            class="rounded-md"
                            >{{
                                account.is_active ? 'Aktif' : 'Nonaktif'
                            }}</Badge
                        >
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end">
                            <RowActionMenu>
                                <DropdownMenuItem
                                    @select="viewingAccount = account"
                                    ><Eye /> Lihat</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="openEdit(account)"
                                    ><Pencil /> Edit</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="toggleAccount(account)"
                                    ><PowerOff v-if="account.is_active" /><Power
                                        v-else
                                    />{{
                                        account.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}</DropdownMenuItem
                                >
                                <DropdownMenuSeparator v-if="canUpdate" />
                                <DropdownMenuItem
                                    v-if="canUpdate"
                                    variant="destructive"
                                    @select="deletingAccount = account"
                                    ><Trash2 /> Hapus</DropdownMenuItem
                                >
                            </RowActionMenu>
                        </div>
                    </td>
                </tr>
                <tr v-if="treeAccounts.length === 0">
                    <td
                        colspan="5"
                        class="px-4 py-12 text-center text-muted-foreground"
                    >
                        Akun tidak ditemukan.
                    </td>
                </tr>
            </tbody>
            <template #footer
                ><DataTablePagination
                    :from="accounts.from"
                    :to="accounts.to"
                    :total="accounts.total"
                    :links="accounts.links"
            /></template>
        </DataTableCard>

        <AdminDataDialog
            v-model:open="viewDialogOpen"
            title="Detail Akun"
            description="Informasi lengkap akun yang dipilih."
            size="wide"
        >
            <dl v-if="viewingAccount" class="grid gap-3 sm:grid-cols-2">
                <div
                    v-for="[label, value] in [
                        ['Kode', viewingAccount.code],
                        ['Nama', viewingAccount.name],
                        ['Jenis', typeLabel(viewingAccount.type)],
                        [
                            'Akun Induk',
                            viewingAccount.parent
                                ? `${viewingAccount.parent.code} — ${viewingAccount.parent.name}`
                                : 'Tidak ada',
                        ],
                        [
                            'Kelompok Laporan',
                            reportLabel(viewingAccount.report_group),
                        ],
                        [
                            'Penggunaan',
                            viewingAccount.is_postable
                                ? 'Dapat digunakan pada transaksi'
                                : 'Hanya sebagai kelompok akun',
                        ],
                        [
                            'Status',
                            viewingAccount.is_active ? 'Aktif' : 'Nonaktif',
                        ],
                        [
                            'Terakhir Diperbarui',
                            formatDate(viewingAccount.updated_at),
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
            </dl>
            <template #footer
                ><Button variant="outline" @click="viewDialogOpen = false"
                    >Tutup</Button
                ></template
            >
        </AdminDataDialog>

        <AdminDataDialog
            v-model:open="dialogOpen"
            :title="editingAccount ? 'Edit Akun' : 'Tambah Akun'"
            description="Lengkapi struktur akun dan tentukan apakah akun dapat digunakan langsung pada transaksi."
            size="wide"
        >
            <form id="account-form" class="space-y-5" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="code">Kode</Label
                        ><Input
                            id="code"
                            v-model="form.code"
                            placeholder="1-1300"
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
                        <Label for="name">Nama Akun</Label
                        ><Input
                            id="name"
                            v-model="form.name"
                            placeholder="Kas Kecil"
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
                        <Label for="type">Jenis Akun</Label
                        ><select
                            id="type"
                            v-model="form.type"
                            class="h-10 rounded-md border bg-background px-3 text-sm"
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
                            v-if="form.errors.type"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.type }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="parent_id">Akun Induk</Label
                        ><AdminSearchSelect
                            v-model="form.parent_id"
                            :options="parentSelectOptions"
                            placeholder="Cari akun induk"
                            empty-label="Tidak ada"
                        />
                        <p
                            v-if="form.errors.parent_id"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.parent_id }}
                        </p>
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="report_group">Kelompok Laporan</Label
                        ><select
                            id="report_group"
                            v-model="form.report_group"
                            class="h-10 rounded-md border bg-background px-3 text-sm"
                        >
                            <option value="">Belum ditentukan</option>
                            <option
                                v-for="[value, label] in reportOptions"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.report_group"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.report_group }}
                        </p>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label
                        class="flex items-start gap-3 rounded-lg border border-border/70 bg-muted/20 p-4 text-sm"
                        ><input
                            v-model="form.is_postable"
                            type="checkbox"
                            class="mt-0.5 size-4 rounded border"
                        /><span
                            ><strong class="block">Akun transaksi</strong
                            ><span class="text-muted-foreground"
                                >Dapat dipilih saat membuat jurnal.</span
                            ></span
                        ></label
                    >
                    <label
                        class="flex items-start gap-3 rounded-lg border border-border/70 bg-muted/20 p-4 text-sm"
                        ><input
                            v-model="form.is_active"
                            type="checkbox"
                            class="mt-0.5 size-4 rounded border"
                        /><span
                            ><strong class="block">Akun aktif</strong
                            ><span class="text-muted-foreground"
                                >Dapat digunakan pada data baru.</span
                            ></span
                        ></label
                    >
                </div>
                <p
                    v-if="form.errors.is_postable"
                    class="text-sm text-destructive"
                >
                    {{ form.errors.is_postable }}
                </p>
            </form>
            <template #footer
                ><Button
                    type="button"
                    variant="outline"
                    @click="dialogOpen = false"
                    ><X /> Batal</Button
                ><Button
                    type="submit"
                    form="account-form"
                    :disabled="form.processing"
                    ><Save /> Simpan</Button
                ></template
            >
        </AdminDataDialog>

        <ConfirmDeleteDialog
            v-model:open="deleteDialogOpen"
            title="Hapus Akun"
            description="Akun tidak akan muncul lagi dalam daftar."
            :subject="
                deletingAccount
                    ? `${deletingAccount.code} — ${deletingAccount.name}`
                    : ''
            "
            :note="
                deleteError ||
                'Akun yang masih digunakan tidak dapat dihapus. Nonaktifkan jika tidak digunakan lagi.'
            "
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
