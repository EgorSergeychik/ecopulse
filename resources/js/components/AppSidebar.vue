<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bot, FolderGit2, LayoutGrid, Map, Users } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as users } from '@/routes/users';
import { index as robots } from '@/routes/robots';
import { index as zones } from '@/routes/zones';
import type { NavItem } from '@/types';
import { Permission } from '@/types/permissions';

const mainNavItems: NavItem[] = [
    {
        title: 'pages.dashboard.title',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'pages.users.title',
        href: users(),
        icon: Users,
        permission: Permission.ViewUsers,
    },
    {
        title: 'pages.zones.title',
        href: zones(),
        icon: Map,
        permission: Permission.ViewZones,
    },
    {
        title: 'pages.robots.title',
        href: robots(),
        icon: Bot,
        permission: Permission.ViewRobots,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'navigation.footer.repository',
        href: 'https://github.com/EgorSergeychik/ecopulse',
        icon: FolderGit2,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
