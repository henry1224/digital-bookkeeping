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
type Option = { id: number; code: string; name: string };
type Bank = {
    id: number;
    outlet_id: number | null;
    outlet: Option | null;
    code: string;
    bank_name: string;
    account_no: string;
    account_name: string;
    account_id: number;
    account: Option;
    is_active: boolean;
    updated_at: string;
};
type Link = { url: string | null; label: string; active: boolean };
type Filters = {
    search: string;
    status: string;
    outlet: string;
    per_page: string;
};
const props = defineProps<{
    filters: Filters;
    bankAccounts: {
        data: Bank[];
        links: Link[];
        from: number | null;
        to: number | null;
        total: number;
    };
    outlets: Option[];
    accounts: Option[];
}>();
const page = usePage();
const canCreate = computed(() =>
    page.props.auth.permissions.includes('master-data.create'),
);
const canUpdate = computed(() =>
    page.props.auth.permissions.includes('master-data.update'),
);
const search = ref(props.filters.search);
const status = ref(props.filters.status);
const outlet = ref(props.filters.outlet);
const perPage = ref(props.filters.per_page);
const dialogOpen = ref(false);
const viewing = ref<Bank | null>(null);
const editing = ref<Bank | null>(null);
const deleting = ref<Bank | null>(null);
const deletingNow = ref(false);
const form = useForm({
    outlet_id: '',
    code: '',
    bank_name: '',
    account_no: '',
    account_name: '',
    account_id: '',
    is_active: true,
    updated_at: '',
});
const outletOptions = computed(() =>
    props.outlets.map((option) => ({
        value: String(option.id),
        label: `${option.code} — ${option.name}`,
    })),
);
const accountOptions = computed(() =>
    props.accounts.map((option) => ({
        value: String(option.id),
        label: `${option.code} — ${option.name}`,
    })),
);
const hasFilters = computed(
    () =>
        search.value !== '' ||
        status.value !== 'semua' ||
        outlet.value !== 'semua' ||
        perPage.value !== '10',
);
const active = (overrides: Partial<Filters> = {}) =>
    Object.fromEntries(
        Object.entries({
            search: search.value,
            status: status.value,
            outlet: outlet.value,
            per_page: perPage.value,
            ...overrides,
        }).filter(([key, value]) =>
            key === 'search' ? String(value).trim() !== '' : value !== 'semua',
        ),
    );
const visit = (overrides: Partial<Filters> = {}) =>
    router.get('/master-data/bank-accounts', active(overrides), {
        preserveState: true,
        replace: true,
        only: ['filters', 'bankAccounts'],
    });
const delayed = useDebounceFn(visit, 150);
const searchDelayed = useDebounceFn(
    (value: string) => delayed({ search: value }),
    400,
);
watch(search, searchDelayed);
const apply = (overrides: Partial<Filters>, now = false) =>
    now ? visit(overrides) : delayed(overrides);
const reset = () => {
    search.value = '';
    status.value = 'semua';
    outlet.value = 'semua';
    perPage.value = '10';
    visit({ search: '', status: 'semua', outlet: 'semua', per_page: '10' });
};
const resetForm = () => {
    form.reset();
    form.clearErrors();
    editing.value = null;
};
const create = () => {
    resetForm();
    dialogOpen.value = true;
};
const edit = (bank: Bank) => {
    editing.value = bank;
    form.outlet_id = bank.outlet_id ? String(bank.outlet_id) : '';
    form.code = bank.code;
    form.bank_name = bank.bank_name;
    form.account_no = bank.account_no;
    form.account_name = bank.account_name;
    form.account_id = String(bank.account_id);
    form.is_active = bank.is_active;
    form.updated_at = bank.updated_at;
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

    if (editing.value) {
        form.patch(`/master-data/bank-accounts/${editing.value.id}`, options);
    } else {
        form.post('/master-data/bank-accounts', options);
    }
};
const toggle = (bank: Bank) =>
    router.patch(
        `/master-data/bank-accounts/${bank.id}/toggle`,
        { updated_at: bank.updated_at },
        { preserveScroll: true },
    );
const remove = () => {
    if (!deleting.value) {
        return;
    }

    deletingNow.value = true;
    router.delete(`/master-data/bank-accounts/${deleting.value.id}`, {
        data: { updated_at: deleting.value.updated_at },
        preserveScroll: true,
        onFinish: () => (deletingNow.value = false),
        onSuccess: () => (deleting.value = null),
    });
};
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Rekening Bank', href: '/master-data/bank-accounts' },
        ],
    },
});
</script>
<template>
    <Head title="Rekening Bank" />
    <div class="space-y-6 p-4">
        <AdminPageHeader
            eyebrow="Master Data"
            title="Rekening Bank"
            description="Kelola rekening penerimaan dan pembayaran perusahaan."
            ><template #actions
                ><Button v-if="canCreate" @click="create"
                    ><Plus />Tambah Rekening</Button
                ></template
            ></AdminPageHeader
        >
        <DataTableCard
            title="Daftar Rekening Bank"
            description="Rekening nonaktif tidak dapat dipilih pada transaksi baru."
            ><template #filters
                ><DataTableFilterSelect
                    v-model="perPage"
                    label="Jumlah data per halaman"
                    @change="apply({ per_page: $event }, true)"
                    ><option value="10">10 data</option>
                    <option value="25">25 data</option>
                    <option value="50">50 data</option></DataTableFilterSelect
                ><DataTableSearch
                    v-model="search"
                    class="w-full sm:max-w-xs"
                    placeholder="Cari bank, nomor, atau pemilik"
                    label="Cari rekening"
                    @clear="apply({ search: '' }, true)"
                /><DataTableFilterSelect
                    v-model="outlet"
                    label="Filter outlet"
                    @change="apply({ outlet: $event })"
                    ><option value="semua">Semua outlet</option>
                    <option
                        v-for="option in outlets"
                        :key="option.id"
                        :value="String(option.id)"
                    >
                        {{ option.code }} — {{ option.name }}
                    </option></DataTableFilterSelect
                ><DataTableFilterSelect
                    v-model="status"
                    label="Filter status"
                    @change="apply({ status: $event })"
                    ><option value="semua">Semua status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">
                        Nonaktif
                    </option></DataTableFilterSelect
                ></template
            ><template #meta
                ><Button
                    v-if="hasFilters"
                    variant="ghost"
                    size="sm"
                    @click="reset"
                    ><RotateCcw />Reset</Button
                ></template
            >
            <thead
                class="bg-muted text-left text-xs text-muted-foreground uppercase"
            >
                <tr>
                    <th class="px-4 py-3">Rekening</th>
                    <th class="px-4 py-3">Bank</th>
                    <th class="px-4 py-3">Outlet</th>
                    <th class="px-4 py-3">Akun Pembukuan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/70">
                <tr
                    v-for="bank in bankAccounts.data"
                    :key="bank.id"
                    class="hover:bg-emerald-50/60 dark:hover:bg-emerald-950/20"
                >
                    <td class="px-4 py-3">
                        <div class="font-semibold">{{ bank.account_name }}</div>
                        <div class="font-mono text-xs text-primary">
                            {{ bank.account_no }} · {{ bank.code }}
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ bank.bank_name }}</td>
                    <td class="px-4 py-3 text-muted-foreground">
                        {{ bank.outlet ? bank.outlet.name : 'Pusat' }}
                    </td>
                    <td class="px-4 py-3">
                        {{ bank.account.code }} — {{ bank.account.name }}
                    </td>
                    <td class="px-4 py-3">
                        <Badge
                            :variant="bank.is_active ? 'default' : 'secondary'"
                            >{{ bank.is_active ? 'Aktif' : 'Nonaktif' }}</Badge
                        >
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end">
                            <RowActionMenu
                                ><DropdownMenuItem @select="viewing = bank"
                                    ><Eye />Lihat</DropdownMenuItem
                                ><DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="edit(bank)"
                                    ><Pencil />Edit</DropdownMenuItem
                                ><DropdownMenuItem
                                    v-if="canUpdate"
                                    @select="toggle(bank)"
                                    ><PowerOff v-if="bank.is_active" /><Power
                                        v-else
                                    />{{
                                        bank.is_active
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}</DropdownMenuItem
                                ><DropdownMenuSeparator
                                    v-if="canUpdate"
                                /><DropdownMenuItem
                                    v-if="canUpdate"
                                    variant="destructive"
                                    @select="deleting = bank"
                                    ><Trash2 />Hapus</DropdownMenuItem
                                ></RowActionMenu
                            >
                        </div>
                    </td>
                </tr>
                <tr v-if="!bankAccounts.data.length">
                    <td
                        colspan="6"
                        class="px-4 py-12 text-center text-muted-foreground"
                    >
                        Rekening bank tidak ditemukan.
                    </td>
                </tr>
            </tbody>
            <template #footer
                ><DataTablePagination
                    :from="bankAccounts.from"
                    :to="bankAccounts.to"
                    :total="bankAccounts.total"
                    :links="bankAccounts.links" /></template
        ></DataTableCard>
        <AdminDataDialog
            :open="!!viewing"
            title="Detail Rekening Bank"
            description="Informasi rekening yang dipilih."
            size="wide"
            @update:open="
                (value) => {
                    if (!value) viewing = null;
                }
            "
            ><dl v-if="viewing" class="grid gap-3 sm:grid-cols-2">
                <div
                    v-for="[label, value] in [
                        ['Kode', viewing.code],
                        ['Bank', viewing.bank_name],
                        ['Nomor Rekening', viewing.account_no],
                        ['Nama Pemilik', viewing.account_name],
                        ['Outlet', viewing.outlet?.name ?? 'Pusat'],
                        [
                            'Akun Pembukuan',
                            `${viewing.account.code} — ${viewing.account.name}`,
                        ],
                        ['Status', viewing.is_active ? 'Aktif' : 'Nonaktif'],
                        ['Diperbarui', formatDate(viewing.updated_at)],
                    ]"
                    :key="label"
                    class="rounded-lg border p-3"
                >
                    <dt class="text-xs text-muted-foreground">{{ label }}</dt>
                    <dd class="mt-1 font-medium">{{ value }}</dd>
                </div>
            </dl>
            <template #footer
                ><Button variant="outline" @click="viewing = null"
                    ><X />Tutup</Button
                ></template
            ></AdminDataDialog
        >
        <AdminDataDialog
            v-model:open="dialogOpen"
            :title="editing ? 'Edit Rekening' : 'Tambah Rekening'"
            description="Lengkapi informasi rekening bank."
            size="wide"
            ><form
                id="bank-form"
                class="grid gap-4 sm:grid-cols-2"
                @submit.prevent="submit"
            >
                <div
                    v-for="field in [
                        'code',
                        'bank_name',
                        'account_no',
                        'account_name',
                    ] as const"
                    :key="field"
                    class="grid gap-2"
                >
                    <Label :for="field">{{
                        {
                            code: 'Kode',
                            bank_name: 'Nama Bank',
                            account_no: 'Nomor Rekening',
                            account_name: 'Nama Pemilik',
                        }[field]
                    }}</Label>
                    <Input :id="field" v-model="form[field]" />
                    <p
                        v-if="form.errors[field]"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors[field] }}
                    </p>
                </div>
                <div class="grid gap-2">
                    <Label for="outlet_id">Outlet</Label
                    ><AdminSearchSelect
                        v-model="form.outlet_id"
                        :options="outletOptions"
                        placeholder="Cari outlet"
                        empty-label="Pusat"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="account_id">Akun Pembukuan</Label
                    ><AdminSearchSelect
                        v-model="form.account_id"
                        :options="accountOptions"
                        placeholder="Cari akun pembukuan"
                    />
                    <p
                        v-if="form.errors.account_id"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.account_id }}
                    </p>
                </div>
                <label class="col-span-full flex gap-2 rounded-lg border p-3"
                    ><input v-model="form.is_active" type="checkbox" />Aktif dan
                    dapat dipilih pada transaksi baru</label
                >
            </form>
            <template #footer
                ><Button variant="outline" @click="dialogOpen = false"
                    ><X />Batal</Button
                ><Button
                    type="submit"
                    form="bank-form"
                    :disabled="form.processing"
                    ><Save />Simpan</Button
                ></template
            ></AdminDataDialog
        >
        <ConfirmDeleteDialog
            :open="!!deleting"
            title="Hapus Rekening Bank"
            description="Rekening tidak akan muncul lagi dalam daftar."
            :subject="
                deleting ? `${deleting.code} — ${deleting.bank_name}` : ''
            "
            note="Riwayat data tetap tersimpan."
            :processing="deletingNow"
            @update:open="
                (value) => {
                    if (!value) deleting = null;
                }
            "
            @confirm="remove"
        />
    </div>
</template>
