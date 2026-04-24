<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Settings, 
    Shield, 
    Palette, 
    Users, 
    Globe, 
    Lock, 
    Check, 
    Save,
    ChevronRight,
    Search
} from 'lucide-vue-next';

const props = defineProps({
    academy: Object,
    members: Array,
});

const form = useForm({
    name: props.academy.name,
    is_public: !!props.academy.is_public,
    allow_self_enroll: !!props.academy.allow_self_enroll,
    primary_color: props.academy.primary_color || '#4f46e5',
    secondary_color: props.academy.secondary_color || '#06b6d4',
});

const submit = () => {
    form.patch(route('academy.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success notification logic could go here
        },
    });
};

const getRoleBadge = (role) => {
    switch(role) {
        case 'owner': return 'bg-purple-100 text-purple-700 border-purple-200';
        case 'expert': return 'bg-indigo-100 text-indigo-700 border-indigo-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${academy.name} Settings`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Academy Console</h2>
                    <p class="text-slate-500 font-medium">Control governance, branding, and personnel for {{ academy.name }}.</p>
                </div>
                
                <button 
                    @click="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 disabled:opacity-50"
                >
                    <Save class="w-5 h-5" />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>
        </template>

        <div class="space-y-8 pb-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Branding & Info -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- General Settings -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-2.5 bg-indigo-50 border border-indigo-100 rounded-xl">
                                <Settings class="w-6 h-6 text-indigo-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">General Identity</h3>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Display Name</label>
                                <input 
                                    v-model="form.name"
                                    type="text" 
                                    class="w-full bg-slate-50 border-transparent focus:bg-white focus:border-indigo-100 focus:ring-4 focus:ring-indigo-50 rounded-2xl p-4 text-lg font-bold transition-all"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <Globe class="w-5 h-5 text-indigo-600" />
                                            <span class="font-bold text-slate-900">Marketplace Visibility</span>
                                        </div>
                                        <button 
                                            @click="form.is_public = !form.is_public"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                            :class="form.is_public ? 'bg-indigo-600' : 'bg-slate-200'"
                                        >
                                            <span 
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                :class="form.is_public ? 'translate-x-5' : 'translate-x-0'"
                                            ></span>
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-500 leading-relaxed">
                                        {{ form.is_public ? 'Visible in global catalog and search. Open for discovery.' : 'Private workspace. Accessible only via direct invitation or SSO.' }}
                                    </p>
                                </div>

                                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <Shield class="w-5 h-5 text-emerald-600" />
                                            <span class="font-bold text-slate-900">Self-Enrollment</span>
                                        </div>
                                        <button 
                                            @click="form.allow_self_enroll = !form.allow_self_enroll"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                            :class="form.allow_self_enroll ? 'bg-emerald-600' : 'bg-slate-200'"
                                        >
                                            <span 
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                :class="form.allow_self_enroll ? 'translate-x-5' : 'translate-x-0'"
                                            ></span>
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-500 leading-relaxed">
                                        {{ form.allow_self_enroll ? 'Visitors can join this academy without an invite.' : 'Only invited members can join this workspace.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Branding -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-2.5 bg-purple-50 border border-purple-100 rounded-xl">
                                <Palette class="w-6 h-6 text-purple-600" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">White-Label Branding</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest px-1">Primary Color</label>
                                <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-2xl">
                                    <div class="w-12 h-12 rounded-xl" :style="{ backgroundColor: form.primary_color }"></div>
                                    <input v-model="form.primary_color" type="text" class="flex-1 bg-transparent border-none font-mono text-sm focus:ring-0" />
                                    <input v-model="form.primary_color" type="color" class="w-8 h-8 rounded cursor-pointer opacity-0 absolute" />
                                    <button class="p-2 hover:bg-white rounded-lg transition-all text-slate-400 hover:text-slate-900">
                                        <Palette class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest px-1">Secondary Accent</label>
                                <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-2xl">
                                    <div class="w-12 h-12 rounded-xl" :style="{ backgroundColor: form.secondary_color }"></div>
                                    <input v-model="form.secondary_color" type="text" class="flex-1 bg-transparent border-none font-mono text-sm focus:ring-0" />
                                    <input v-model="form.secondary_color" type="color" class="w-8 h-8 rounded cursor-pointer opacity-0 absolute" />
                                    <button class="p-2 hover:bg-white rounded-lg transition-all text-slate-400 hover:text-slate-900">
                                        <Palette class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 p-6 bg-slate-900 rounded-[32px] border border-white/5 relative overflow-hidden">
                            <div class="absolute inset-0 opacity-20" :style="{ background: `linear-gradient(135deg, ${form.primary_color} 0%, ${form.secondary_color} 100%)` }"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-black text-white/40 uppercase tracking-[0.2em] mb-1">Live Preview</p>
                                    <h4 class="text-2xl font-black text-white">{{ academy.name }}</h4>
                                </div>
                                <div :style="{ backgroundColor: form.primary_color }" class="px-6 py-2.5 rounded-xl text-white text-xs font-black uppercase tracking-widest shadow-xl">
                                    Action Button
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personnel / Roles Sidebar -->
                <div class="space-y-8">
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm h-full flex flex-col">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-indigo-50 border border-indigo-100 rounded-xl">
                                    <Users class="w-6 h-6 text-indigo-600" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900">Personnel</h3>
                            </div>
                        </div>

                        <div class="relative mb-6">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input type="text" placeholder="Filter members..." class="w-full bg-slate-50 border-transparent focus:bg-white focus:border-indigo-100 rounded-xl pl-10 pr-4 py-2.5 text-sm" />
                        </div>

                        <div class="flex-1 space-y-4 overflow-y-auto max-h-[600px] no-scrollbar">
                            <div v-for="member in members" :key="member.id" class="flex items-center gap-4 p-4 hover:bg-slate-50 rounded-2xl transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    {{ member.name.charAt(0) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ member.name }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-tight border" :class="getRoleBadge(member.pivot.role)">
                                            {{ member.pivot.role }}
                                        </span>
                                    </div>
                                </div>
                                <ChevronRight class="w-4 h-4 text-slate-300 group-hover:text-indigo-400 transition-colors" />
                            </div>
                        </div>

                        <button class="mt-8 w-full py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
                            <Users class="w-5 h-5" />
                            Invite Professionals
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
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
