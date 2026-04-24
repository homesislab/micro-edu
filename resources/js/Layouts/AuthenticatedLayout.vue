<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    BookOpen, 
    UserCircle, 
    LogOut, 
    Menu, 
    X, 
    ClipboardCheck,
    Bell,
    Settings,
    ChevronRight,
    Search,
    Rocket,
    BarChart3
} from 'lucide-vue-next';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import AcademySwitcher from '@/Components/AcademySwitcher.vue';
import axios from 'axios';

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(true);

const notifications = ref([]);
const hasUnreadNotifications = computed(() => notifications.value.length > 0);

const fetchNotifications = async () => {
    try {
        const response = await axios.get(route('notifications.index'));
        notifications.value = response.data;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    }
};

const openNotifications = async () => {
    if (notifications.value.length === 0) return;

    try {
        await axios.post(route('notifications.markAsRead'));
        notifications.value = []; // Clear notifications visually
    } catch (error) {
        console.error('Failed to mark notifications as read:', error);
    }
};

onMounted(() => {
    fetchNotifications();
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const academy = computed(() => page.props.auth.academy);

const topNavItems = computed(() => {
    const items = [];
    const url = page.props.ziggy?.location ?? window.location.pathname;

    if (academy.value) {
        items.push(
            {
                name: 'Dashboard',
                href: route('dashboard'),
                icon: LayoutDashboard,
                active: url.includes('/dashboard') && !url.includes('/expert')
            },
            {
                name: 'Review Queue',
                href: route('expert.dashboard'),
                icon: ClipboardCheck,
                active: url.includes('/expert/dashboard') || url.includes('/expert/review-queue')
            },
        );
    }

    return items;
});

const bottomNavItems = computed(() => {
    const items = [];

    if (academy.value) {
        items.push(
            { name: 'Settings', href: route('academy.settings'), icon: Settings, active: route().current('academy.settings') }
        );
    }

    return items;
});
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] flex overflow-hidden">
        <!-- GLOBAL ACADEMY SWITCHER (Discord Style) -->
        <AcademySwitcher />

        <!-- CORE LAYOUT CONTAINER (Shifted for Switcher) -->
        <div class="flex-1 flex ml-[72px] overflow-hidden">
            <!-- Persistent Sidebar -->
            <aside v-if="academy" :class="[sidebarOpen ? 'w-72' : 'w-20']" 
                   class="bg-white border-r border-slate-200 transition-all duration-300 ease-in-out hidden lg:flex flex-col z-30">
                <div class="p-6 flex items-center gap-3">
                    <div class="bg-indigo-600 w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-200">
                        <BookOpen class="text-white w-6 h-6" />
                    </div>
                    <div v-if="sidebarOpen" class="flex flex-col overflow-hidden">
                        <span class="font-bold text-lg text-slate-900 tracking-tight transition-opacity duration-300 truncate">
                            {{ academy?.name || 'MicroEducate' }}
                        </span>
                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ academy ? 'Internal Academy' : 'Global Hub' }}</span>
                    </div>
                </div>

                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto flex flex-col">
                    <div class="flex-1 space-y-2">
                        <Link v-for="item in topNavItems" :key="item.name" :href="item.href"
                              :class="[item.active ? 'bg-indigo-50 text-indigo-700 shadow-sm border-indigo-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900']"
                              class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group border border-transparent">
                            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" :class="item.active ? 'text-indigo-600' : 'group-hover:text-slate-900'" />
                            <span v-if="sidebarOpen" class="font-medium text-[15px] whitespace-nowrap">{{ item.name }}</span>
                            <ChevronRight v-if="sidebarOpen && item.active" class="ml-auto w-4 h-4" />
                        </Link>
                    </div>
                    
                    <div class="space-y-2 border-t border-slate-100 pt-4 mt-auto">
                        <Link v-for="item in bottomNavItems" :key="item.name" :href="item.href"
                              :class="[item.active ? 'bg-indigo-50 text-indigo-700 shadow-sm border-indigo-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900']"
                              class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group border border-transparent">
                            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" :class="item.active ? 'text-indigo-600' : 'group-hover:text-slate-900'" />
                            <span v-if="sidebarOpen" class="font-medium text-[15px] whitespace-nowrap">{{ item.name }}</span>
                            <ChevronRight v-if="sidebarOpen && item.active" class="ml-auto w-4 h-4" />
                        </Link>
                    </div>
                </nav>

                <div class="p-4 border-t border-slate-100">
                    <div v-if="sidebarOpen" class="bg-slate-50 rounded-2xl p-4 mb-4">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Signed in as</p>
                        <p class="text-sm font-bold text-slate-900 truncate">{{ user?.name }}</p>
                        <p class="text-[11px] text-slate-500 capitalize">{{ user?.role }}</p>
                    </div>
                    
                    <Link :href="route('logout')" method="post" as="button"
                          class="flex items-center gap-3 px-4 py-3 w-full rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-600 transition-all group">
                        <LogOut class="w-5 h-5" />
                        <span v-if="sidebarOpen" class="font-medium text-[15px]">Sign Out</span>
                    </Link>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden">
                <!-- Top Header -->
                <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 h-20 flex items-center justify-between px-6 sticky top-0 z-20">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex p-2 text-slate-400 hover:text-slate-600 transition-colors">
                            <Menu v-if="!sidebarOpen" class="w-6 h-6" />
                            <X v-else class="w-6 h-6" />
                        </button>
                        
                        <div class="relative hidden sm:block">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input type="text" placeholder="Search..." 
                                   class="bg-slate-50 border-transparent focus:bg-white focus:border-indigo-100 focus:ring-4 focus:ring-indigo-50/50 rounded-xl pl-10 pr-4 py-2 text-sm w-64 transition-all" />
                        </div>

                        <div class="h-6 w-[1px] bg-slate-200 ml-2"></div>

                        <Link :href="route('catalog')" class="px-3 py-2 rounded-lg text-sm font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            Explore Courses
                        </Link>
                    </div>

                    <div class="flex items-center gap-4">
                        <Dropdown @open="openNotifications" align="right" width="80">
                            <template #trigger>
                                <button class="p-2 text-slate-400 hover:bg-slate-50 rounded-xl transition-all relative">
                                    <Bell class="w-5 h-5" />
                                    <span v-if="hasUnreadNotifications" class="absolute top-2 right-2 w-2 h-2 bg-indigo-600 rounded-full border-2 border-white"></span>
                                </button>
                            </template>
                            <template #content>
                                <div class="p-4 border-b border-slate-100">
                                    <p class="text-sm font-bold text-slate-900">Notifications</p>
                                </div>
                                <div v-if="notifications.length > 0" class="max-h-96 overflow-y-auto">
                                    <div v-for="notification in notifications" :key="notification.id" class="px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0">
                                        <p class="text-sm text-slate-700 font-semibold">{{ notification.data?.message || 'New Update' }}</p>
                                        <p class="text-xs text-slate-400 mt-1">{{ notification.created_at ? new Date(notification.created_at).toLocaleString() : '' }}</p>
                                    </div>
                                </div>
                                <div v-else class="p-8 text-center">
                                    <Bell class="w-10 h-10 mx-auto text-slate-200 mb-4" />
                                    <p class="text-sm font-bold text-slate-500">No new notifications</p>
                                    <p class="text-xs text-slate-400 mt-1">We'll let you know when something new happens.</p>
                                </div>
                            </template>
                        </Dropdown>
                        
                        <div class="h-8 w-[1px] bg-slate-200 mx-2"></div>

                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 p-1 rounded-xl hover:bg-slate-50 transition-all group">
                                    <div class="bg-indigo-100 w-9 h-9 rounded-lg flex items-center justify-center text-indigo-700 font-bold overflow-hidden border border-indigo-200 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        {{ user?.name?.charAt(0) || 'U' }}
                                    </div>
                                    <div class="text-left hidden sm:block">
                                        <p class="text-sm font-bold text-slate-900 leading-none mb-1">{{ user?.name }}</p>
                                        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-tight">{{ user?.role }}</p>
                                    </div>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <p class="text-sm font-bold text-slate-900">{{ user?.name }}</p>
                                    <p class="text-xs text-slate-500">{{ user?.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')"> Profile Settings </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button"> Sign Out </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <!-- Scrollable Page Container -->
                <main class="flex-1 overflow-y-auto p-6 md:p-10">
                    <!-- Optional Header Slot -->
                    <div v-if="$slots.header" class="mb-10">
                        <slot name="header" />
                    </div>
                    
                    <slot />

                    <!-- Simple Footer -->
                    <footer class="mt-20 py-10 border-t border-slate-200 text-center">
                        <p class="text-sm text-slate-400 font-medium tracking-tight">
                            &copy; 2026 MicroEducate Platform. Build for Community Led Learning.
                        </p>
                    </footer>
                </main>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-slide-left {
    animation: slideLeft 0.3s ease-out;
}
@keyframes slideLeft {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>
