<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LayoutGrid, Plus, ArrowRight, Shield, GraduationCap, Users } from 'lucide-vue-next';

defineOptions({
    layout: AuthenticatedLayout
});

const props = defineProps({
    academies: Array,
    user: Object
});

const getRoleIcon = (role) => {
    switch(role) {
        case 'owner': return Shield;
        case 'expert': return GraduationCap;
        default: return Users;
    }
};

const getRoleLabel = (role) => {
    return role.charAt(0).toUpperCase() + role.slice(1);
};
</script>

<template>
    <Head title="Academy Lobby" />

    <div class="selection:bg-indigo-500/30">
        <main class="max-w-7xl mx-auto py-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight sm:text-5xl">
                        Welcome Back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-cyan-500 font-black">{{ user.name.split(' ')[0] }}</span>
                    </h1>
                    <p class="mt-4 text-lg text-slate-500 max-w-2xl">
                        Choose an academy to continue learning or managing your communities.
                    </p>
                </div>
            </div>

            <!-- Academy Grid -->
            <div v-if="academies.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div 
                    v-for="(academy, idx) in academies" 
                    :key="academy.id"
                    :style="{ animationDelay: `${idx * 100}ms` }"
                    class="group relative bg-white/50 border border-slate-200/50 rounded-3xl p-6 hover:bg-white hover:border-indigo-200 transition-all duration-300 shadow-lg hover:shadow-2xl animate-in fade-in zoom-in-95"
                >
                    <div class="flex items-start justify-between mb-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl font-black text-white shadow-xl shadow-indigo-500/20 group-hover:scale-110 transition-transform">
                            {{ academy.name.charAt(0) }}
                        </div>
                        
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 rounded-full border border-slate-200 text-xs font-semibold text-slate-600">
                            <component :is="getRoleIcon(academy.pivot.role)" class="w-3.5 h-3.5 text-indigo-500" />
                            {{ getRoleLabel(academy.pivot.role) }}
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-indigo-600 transition-colors">
                        {{ academy.name }}
                    </h3>
                    <p class="text-slate-500 text-sm mb-8 line-clamp-2">
                        {{ academy.subdomain ? academy.subdomain + '.microeducate.com' : 'Private Workspace' }}
                    </p>

                    <Link 
                        :href="route('academy.enter', academy.id)"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-800 border border-slate-700 text-white font-bold rounded-xl group-hover:bg-indigo-600 group-hover:border-indigo-500 transition-all"
                    >
                        Enter Space
                        <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </Link>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-24 bg-gradient-to-br from-white/80 to-slate-50/50 border-2 border-dashed border-slate-200 rounded-[40px] shadow-2xl shadow-slate-300/50 animate-in fade-in zoom-in-90 duration-500">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-cyan-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-indigo-500/30">
                    <LayoutGrid class="w-10 h-10 text-white" />
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-3">No Academies Joined</h3>
                <p class="text-slate-500 max-w-md mx-auto mb-10">
                    You haven't joined or created any academies yet. Start your journey by creating your own learning space.
                </p>
                <Link 
                    :href="route('academy.create')"
                    class="inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-500 transition-all active:scale-95 shadow-xl shadow-indigo-600/20"
                >
                    <Plus class="w-6 h-6" />
                    Create Your First Academy
                </Link>
            </div>
        </main>
    </div>
</template>
