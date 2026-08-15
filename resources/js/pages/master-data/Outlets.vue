<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref, watch } from 'vue';
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

const props = defineProps<{
    filters: { search: string };
    outlets: {
        data: Outlet[];
        links: PageLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
}>();

const search = ref(props.filters.search);
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
    label.replace('&laquo; Previous', 'Sebelumnya').replace('Next &raquo;', 'Berikutnya');

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
    const options = { preserveScroll: true, onSuccess: () => (dialogOpen.value = false) };

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
    router.get(
        '/master-data/outlets',
        { search: value || undefined },
        { preserveState: true, replace: true, only: ['filters', 'outlets'] },
    );
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
            class="rounded-sm border border-sidebar-border/70 bg-gradient-to-br from-emerald-50 via-white to-cyan-50 p-6 shadow-sm dark:from-emerald-950/30 dark:via-background dark:to-cyan-950/20"
        >
            <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                Master Data
            </p>
            <div class="mt-2 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight">Outlet</h1>
                    <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                        Kelola cabang operasional, central kitchen, dan dasar scope laporan multi-outlet.
                    </p>
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <div class="relative w-full md:w-80">
                        <Search class="absolute top-2.5 left-3 size-4 text-muted-foreground" />
                        <Input v-model="search" class="pl-9" placeholder="Cari kode atau nama outlet" />
                    </div>
                    <Button @click="openCreate">Tambah Outlet</Button>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-sm border bg-card shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/60 text-left text-xs uppercase tracking-wide text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Zona Waktu</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="outlet in outlets.data" :key="outlet.id" class="hover:bg-muted/40">
                            <td class="px-4 py-3 font-medium">{{ outlet.code }}</td>
                            <td class="px-4 py-3">{{ outlet.name }}</td>
                            <td class="px-4 py-3">{{ outlet.outlet_type }}</td>
                            <td class="px-4 py-3">{{ outlet.timezone }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="outlet.is_active ? 'default' : 'secondary'">
                                    {{ outlet.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="outline" size="sm" @click="openEdit(outlet)">
                                        Edit
                                    </Button>
                                    <Button variant="outline" size="sm" @click="toggleOutlet(outlet)">
                                        {{ outlet.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="outlets.data.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-muted-foreground">
                                Data outlet tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t px-4 py-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-muted-foreground">
                    Menampilkan {{ outlets.from ?? 0 }}-{{ outlets.to ?? 0 }} dari {{ outlets.total }} data
                </p>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="link in outlets.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        class="rounded-md border px-3 py-1.5 text-sm"
                        :class="[
                            link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
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
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingOutlet ? 'Edit Outlet' : 'Tambah Outlet' }}</DialogTitle>
                    <DialogDescription>
                        Kode outlet harus unik. Outlet nonaktif tetap tersimpan untuk histori.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="code">Kode</Label>
                        <Input id="code" v-model="form.code" placeholder="BPN-C" />
                        <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Nama</Label>
                        <Input id="name" v-model="form.name" placeholder="Balikpapan C" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
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
                        <p v-if="form.errors.outlet_type" class="text-sm text-destructive">
                            {{ form.errors.outlet_type }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="timezone">Zona Waktu</Label>
                        <Input id="timezone" v-model="form.timezone" readonly />
                        <p v-if="form.errors.timezone" class="text-sm text-destructive">
                            {{ form.errors.timezone }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.is_active" type="checkbox" class="size-4 rounded border" />
                        Aktif
                    </label>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="dialogOpen = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
