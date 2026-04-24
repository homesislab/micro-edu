<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Plus, LayoutGrid, Home } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
// Use the new shared data structure from HandleInertiaRequests
const academies = computed(() => page.props.auth?.user_academies || []);
const activeAcademy = computed(() => page.props.auth?.academy);

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};
</script>

<template>
    <div class="fixed left-0 top-0 bottom-0 w-[72px] bg-slate-950 flex flex-col items-center py-3 gap-3 z-[100] border-r border-white/5">
        <!-- Logo / Global Lobby -->
        <Link 
            :href="route('lobby')"
            class="group mb-2"
        >
            <div 
                class="w-12 h-12 rounded-[24px] group-hover:rounded-2xl bg-indigo-600 flex items-center justify-center text-white transition-all duration-300"
                :class="route().current('lobby') ? 'rounded-2xl bg-indigo-500' : ''"
            >
                <LayoutGrid class="w-6 h-6" />
            </div>
            <div class="absolute left-full ml-2 px-2 py-1 bg-slate-900 text-white text-xs font-bold rounded opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap transition-opacity">
                Lobby
            </div>
        </Link>

        <div class="w-8 h-[2px] bg-white/10 rounded-full"></div>

        <!-- Academy List -->
        <div class="flex-1 flex flex-col gap-2 overflow-y-auto no-scrollbar pt-2">
            <Link 
                v-for="academy in academies" 
                :key="academy.id"
                :href="route('academy.enter', academy.id)"
                class="group relative flex items-center"
            >
                <!-- Active Indicator -->
                <div 
                    class="absolute -left-3 w-2 bg-white rounded-r-md transition-all duration-300"
                    :class="activeAcademy?.id === academy.id ? 'h-8' : 'h-2 group-hover:h-5 opacity-0 group-hover:opacity-100'"
                ></div>

                <!-- Avatar -->
                <div 
                    class="w-12 h-12 transition-all duration-300 flex items-center justify-center font-bold text-sm overflow-hidden"
                    :class="[
                        activeAcademy?.id === academy.id 
                            ? 'bg-indigo-600 text-white rounded-2xl' // Active state: Squircle
                            : 'bg-slate-800 text-slate-400 rounded-full group-hover:bg-indigo-600 group-hover:text-white group-hover:rounded-2xl' // Inactive state: Circle
                    ]"
                >
                    <img v-if="academy.logo_url" :src="academy.logo_url" class="w-full h-full object-cover" />
                    <span v-else>{{ getInitials(academy.name) }}</span>
                </div>

                <!-- Tooltip -->
                <div class="absolute left-full ml-4 px-3 py-1.5 bg-slate-900 border border-white/10 text-white text-xs font-bold rounded-lg shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap transition-opacity">
                    {{ academy.name }}
                </div>
            </Link>
        </div>

        <!-- Permanent "Create New" button at the bottom -->
        <div class="mt-auto">
             <Link 
                :href="route('academy.create')"
                class="group relative flex items-center"
            >
                <div class="w-12 h-12 rounded-full group-hover:rounded-2xl bg-slate-800 border-2 border-dashed border-white/5 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                    <Plus class="w-6 h-6" />
                </div>
                <div class="absolute left-full ml-4 px-3 py-1.5 bg-slate-900 border border-white/10 text-white text-xs font-bold rounded-lg shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap transition-opacity">
                    Create Academy
                </div>
            </Link>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
