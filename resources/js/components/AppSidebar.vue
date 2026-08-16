<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Banknote,
    BookOpenCheck,
    BookOpenText,
    Boxes,
    Building2,
    ChartColumn,
    ClipboardList,
    Cog,
    LayoutGrid,
    Package,
    Ruler,
    Settings2,
    Tags,
    Truck,
    ReceiptText,
    WalletCards,
} from '@lucide/vue';
import type { Component } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

type SidebarNavItem = {
    title: string;
    href?: NavItem['href'];
    icon: Component;
    badge?: string;
};

type SidebarNavGroup = {
    title: string;
    items: SidebarNavItem[];
};

const sidebarNavGroups: SidebarNavGroup[] = [
    {
        title: 'Ringkasan',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
        ],
    },
    {
        title: 'Master Data',
        items: [
            {
                title: 'Outlet',
                href: '/master-data/outlets',
                icon: Building2,
            },
            {
                title: 'Satuan (UOM)',
                href: '/master-data/uom',
                icon: Ruler,
            },
            {
                title: 'Kelompok Item',
                href: '/master-data/item-groups',
                icon: Tags,
            },
            {
                title: 'Supplier',
                href: '/master-data/suppliers',
                icon: Truck,
            },
            {
                title: 'Chart of Accounts',
                href: '/master-data/accounts',
                icon: BookOpenText,
            },
            {
                title: 'Item',
                href: '/master-data/items',
                icon: Package,
            },
            {
                title: 'Rekening Bank',
                href: '/master-data/bank-accounts',
                icon: Banknote,
            },
        ],
    },
    {
        title: 'Operasional',
        items: [
            {
                title: 'Penjualan Harian',
                icon: ReceiptText,
                badge: 'Segera',
            },
            {
                title: 'Buku Bank',
                icon: WalletCards,
                badge: 'Segera',
            },
            {
                title: 'Logistik',
                icon: Package,
                badge: 'Segera',
            },
            {
                title: 'Stok',
                icon: Boxes,
                badge: 'Segera',
            },
        ],
    },
    {
        title: 'Akuntansi',
        items: [
            {
                title: 'Jurnal',
                icon: BookOpenCheck,
                badge: 'Segera',
            },
            {
                title: 'Closing',
                icon: ClipboardList,
                badge: 'Segera',
            },
            {
                title: 'Laporan',
                icon: ChartColumn,
                badge: 'Segera',
            },
        ],
    },
    {
        title: 'Pengaturan',
        items: [
            {
                title: 'Konfigurasi Outlet',
                icon: Settings2,
                badge: 'Segera',
            },
            {
                title: 'Pengguna & Role',
                icon: Cog,
                badge: 'Segera',
            },
        ],
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
const isActive = (item: SidebarNavItem) =>
    item.href ? isCurrentOrParentUrl(item.href) : false;
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="border-sidebar-border/70"
    >
        <SidebarHeader class="border-b border-sidebar-border/70 px-3 py-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="h-12 rounded-md transition-colors hover:bg-sidebar-accent"
                    >
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="px-2 py-4">
            <SidebarGroup
                v-for="group in sidebarNavGroups"
                :key="group.title"
                class="px-0 py-0"
            >
                <SidebarGroupLabel
                    class="px-3 text-[10px] font-semibold tracking-normal text-sidebar-foreground/45 uppercase group-data-[collapsible=icon]:hidden"
                >
                    {{ group.title }}
                </SidebarGroupLabel>
                <SidebarGroupContent class="mt-1">
                    <SidebarMenu class="space-y-1">
                        <SidebarMenuItem
                            v-for="item in group.items"
                            :key="item.title"
                        >
                            <SidebarMenuButton
                                v-if="item.href"
                                as-child
                                :is-active="isActive(item)"
                                :tooltip="item.title"
                                class="h-10 rounded-md px-2 text-[13px] transition-colors data-[active=true]:font-semibold"
                            >
                                <Link :href="item.href">
                                    <span
                                        class="flex size-8 shrink-0 items-center justify-center rounded-md transition-colors"
                                        :class="
                                            isActive(item)
                                                ? 'bg-sidebar-primary text-sidebar-primary-foreground'
                                                : 'bg-sidebar-accent text-sidebar-primary'
                                        "
                                    >
                                        <component
                                            :is="item.icon"
                                            class="size-4"
                                        />
                                    </span>
                                    <span class="min-w-0 truncate">
                                        {{ item.title }}
                                    </span>
                                </Link>
                            </SidebarMenuButton>

                            <button
                                v-else
                                type="button"
                                class="flex h-10 w-full cursor-not-allowed items-center gap-2 rounded-md px-2 text-left text-[13px] text-sidebar-foreground/45 opacity-80 transition-colors group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-2"
                                :title="`${item.title} belum tersedia`"
                                disabled
                            >
                                <span
                                    class="flex size-8 shrink-0 items-center justify-center rounded-md bg-muted text-sidebar-foreground/45"
                                >
                                    <component :is="item.icon" class="size-4" />
                                </span>
                                <span
                                    class="min-w-0 flex-1 truncate group-data-[collapsible=icon]:hidden"
                                >
                                    {{ item.title }}
                                </span>
                                <span
                                    v-if="item.badge"
                                    class="rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground group-data-[collapsible=icon]:hidden"
                                >
                                    {{ item.badge }}
                                </span>
                            </button>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="gap-3 border-t border-sidebar-border/70 p-3">
            <div
                class="rounded-lg border border-sidebar-border/70 bg-sidebar-accent/60 p-3 text-xs text-sidebar-foreground/70 group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:h-9 group-data-[collapsible=icon]:items-center group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0"
            >
                <p
                    class="font-semibold text-sidebar-foreground group-data-[collapsible=icon]:hidden"
                >
                    Kontrol finansial aktif
                </p>
                <p class="mt-1 leading-5 group-data-[collapsible=icon]:hidden">
                    Semua aksi penting lewat permission, outlet scope, dan audit
                    log.
                </p>
                <span
                    class="hidden size-2.5 rounded-full bg-primary group-data-[collapsible=icon]:block"
                />
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
