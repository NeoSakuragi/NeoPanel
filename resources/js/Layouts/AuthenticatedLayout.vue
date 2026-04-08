<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import {
    Squares2X2Icon,
    CubeIcon,
    ServerStackIcon,
    Cog6ToothIcon,
    Bars3Icon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const mobileOpen = ref(false);

const navItems = [
    { label: 'Dashboard', route: 'dashboard', icon: Squares2X2Icon, pattern: 'dashboard' },
    { label: 'Applications', route: 'applications.index', icon: CubeIcon, pattern: 'applications.*' },
    { label: 'Instances', route: 'instances.index', icon: ServerStackIcon, pattern: 'instances.*' },
    { label: 'Settings', route: 'settings.edit', icon: Cog6ToothIcon, pattern: 'settings.*' },
];

function isActive(pattern) {
    try { return route().current(pattern); } catch { return false; }
}
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <!-- Top bar -->
        <nav class="sticky top-0 z-30 bg-slate-900 shadow-md">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-14 items-center justify-between">
                    <!-- Left: brand + nav -->
                    <div class="flex items-center gap-6">
                        <Link :href="route('dashboard')" class="text-lg font-bold text-white tracking-tight">
                            NeoPanel
                        </Link>

                        <div class="hidden sm:flex items-center gap-1">
                            <Link
                                v-for="item in navItems"
                                :key="item.route"
                                :href="route(item.route)"
                                :class="[
                                    'flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                                    isActive(item.pattern)
                                        ? 'bg-slate-700 text-white'
                                        : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                                ]"
                            >
                                <component :is="item.icon" class="h-4 w-4" />
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <!-- Right: user -->
                    <div class="hidden sm:flex items-center">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Mobile hamburger -->
                    <button @click="mobileOpen = !mobileOpen" class="sm:hidden rounded p-1.5 text-slate-400 hover:text-white">
                        <Bars3Icon v-if="!mobileOpen" class="h-6 w-6" />
                        <XMarkIcon v-else class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile nav -->
            <div v-show="mobileOpen" class="sm:hidden border-t border-slate-700 px-4 pb-3 pt-2 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    :class="[
                        'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium',
                        isActive(item.pattern)
                            ? 'bg-slate-700 text-white'
                            : 'text-slate-300 hover:bg-slate-800',
                    ]"
                    @click="mobileOpen = false"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.label }}
                </Link>
                <div class="border-t border-slate-700 pt-2 mt-2">
                    <div class="px-3 py-2 text-sm text-slate-400">{{ $page.props.auth.user.name }}</div>
                    <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-3 py-2 text-sm text-red-400 hover:bg-slate-800 rounded-lg">
                        Log Out
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Page content -->
        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
            <slot />
        </main>

        <!-- Flash messages -->
        <FlashMessage />
    </div>
</template>
