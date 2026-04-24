<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Building2, 
    Users, 
    ShieldCheck, 
    Plus, 
    MoreVertical, 
    Activity,
    CreditCard,
    Zap,
    TrendingUp,
    ShieldAlert
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    stats: Object,
    organizations: Array
});

const showCreateModal = ref(false);
const createForm = useForm({
    name: '',
    user_quota: 100
});

const submitCreate = () => {
    createForm.post(route('super.organizations.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const editingOrg = ref(null);
const editForm = useForm({
    name: '',
    user_quota: 0,
    status: 'active'
});

const openEditModal = (org) => {
    editingOrg.value = org;
    editForm.name = org.name;
    editForm.user_quota = org.user_quota;
    editForm.status = org.status;
};

const submitUpdate = () => {
    editForm.patch(route('super.organizations.update', editingOrg.value.id), {
        onSuccess: () => editingOrg.value = null
    });
};
</script>

<template>
    <Head title="Super Admin Control Room" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between py-6">
                <div>
                    <div class="flex items-center gap-2 text-rose-500 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                        <span class="w-8 h-px bg-rose-500"></span> Global Command Centre
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">
                        Platform Control<span class="text-rose-500">.</span>
                    </h2>
                </div>
                <button @click="showCreateModal = true" 
                        class="bg-slate-900 text-white px-8 py-4 rounded-[2rem] font-black text-sm flex items-center gap-3 shadow-2xl hover:bg-rose-600 transition-all hover:-translate-y-1 active:scale-95">
                    <Plus class="w-5 h-5 text-rose-400" /> Provision New Tenant
                </button>
            </div>
        </template>

        <div class="space-y-10">
            <!-- Global Telemetry -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-indigo-600 rounded-[3rem] p-8 text-white relative overflow-hidden group shadow-2xl">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-700 opacity-50"></div>
                    <Building2 class="absolute -right-8 -bottom-8 w-48 h-48 text-white/10 rotate-12 group-hover:rotate-0 transition-transform duration-700" />
                    <div class="relative z-10">
                        <p class="text-indigo-100 font-black text-[10px] uppercase tracking-widest mb-1">Total Scale</p>
                        <h3 class="text-5xl font-black tracking-tighter">{{ stats.total_organizations }}</h3>
                        <p class="text-indigo-100/60 text-xs font-bold mt-2 uppercase tracking-widest leading-none">Organizations onboarded</p>
                    </div>
                </div>

                <div class="bg-white rounded-[3rem] p-8 border border-slate-100 shadow-xl relative overflow-hidden group">
                    <Users class="absolute -right-8 -bottom-8 w-48 h-48 text-slate-50 rotate-12 group-hover:rotate-0 transition-transform duration-700" />
                    <div class="relative z-10">
                        <p class="text-slate-400 font-black text-[10px] uppercase tracking-widest mb-1">User Ecosystem</p>
                        <h3 class="text-5xl font-black text-slate-900 tracking-tighter">{{ stats.total_users }}</h3>
                        <div class="flex items-center gap-2 mt-4">
                            <span class="p-1 bg-emerald-50 text-emerald-600 rounded-lg"><TrendingUp class="w-4 h-4" /></span>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Active nodes</span>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-[3rem] p-8 text-white relative overflow-hidden group shadow-2xl">
                    <Zap class="absolute -right-8 -bottom-8 w-48 h-48 text-white/5 rotate-12 group-hover:rotate-0 transition-transform duration-700" />
                    <div class="relative z-10">
                        <p class="text-slate-500 font-black text-[10px] uppercase tracking-widest mb-1">Operational Health</p>
                        <h3 class="text-5xl font-black text-white tracking-tighter">{{ stats.active_tenants }}</h3>
                        <div class="flex items-center gap-2 mt-4">
                            <div class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></div>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Live Tenants Online</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tenant Management -->
            <div class="bg-white rounded-[4rem] border border-slate-100 shadow-2xl overflow-hidden">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Tenant Inventory</h3>
                        <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-1">Manage cross-enterprise relationships</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="bg-slate-50 px-6 py-3 rounded-2xl flex items-center gap-3 border border-slate-100">
                            <Activity class="w-4 h-4 text-slate-400" />
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">System Broadcast: ON</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Organization</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">User Quota</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Nodes</th>
                                <th class="px-10 py-6"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="org in organizations" :key="org.id" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-10 py-8">
                                    <div class="flex items-center gap-5">
                                        <div class="w-16 h-16 rounded-[1.5rem] bg-slate-900 flex items-center justify-center text-white font-black text-xl shadow-lg group-hover:scale-110 transition-transform">
                                            {{ org.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-900 text-xl tracking-tight leading-none group-hover:text-rose-600 transition-colors">{{ org.name }}</h4>
                                            <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2 flex items-center gap-2">
                                                <LinkIcon class="w-3 h-3" /> microeducate.com/{{ org.slug }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8">
                                    <div class="flex justify-center">
                                        <div :class="org.status === 'active' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'" 
                                             class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border shadow-sm">
                                            {{ org.status }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xl font-black text-slate-900 tracking-tight">{{ org.user_quota }}</span>
                                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Licensed Seats</span>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xl font-black text-slate-400 tracking-tight group-hover:text-slate-900 transition-colors">{{ org.users_count }}</span>
                                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Active Units</span>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <button @click="openEditModal(org)" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center border border-slate-100 text-slate-400 hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                        <MoreVertical class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Provision Tenant Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="xl">
            <div class="p-10 bg-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-rose-50 rounded-full blur-3xl opacity-50 -mr-20 -mt-20"></div>
                
                <div class="flex items-center justify-between mb-10 relative z-10">
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight">Provision Tenant.</h3>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest mt-1">Onboard new enterprise entity</p>
                    </div>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-8 relative z-10">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-4">Enterprise Legal Name</label>
                        <input v-model="createForm.name" type="text" placeholder="e.g. Acme Corp Industries" 
                               class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-rose-500 focus:bg-white transition-all font-black text-xl outline-none shadow-inner" />
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-4">Seat Allocation (Quota)</label>
                        <input v-model="createForm.user_quota" type="number" 
                               class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-rose-500 focus:bg-white transition-all font-black text-xl outline-none shadow-inner" />
                    </div>

                    <button type="submit" :disabled="createForm.processing" class="w-full bg-slate-900 text-white py-6 rounded-[2.5rem] font-black text-lg shadow-2xl hover:bg-rose-600 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-4">
                        <Zap class="w-6 h-6 text-rose-400" /> Authorize & Provision
                    </button>
                </form>
            </div>
        </Modal>

        <!-- Edit Tenant Modal -->
        <Modal :show="editingOrg !== null" @close="editingOrg = null" maxWidth="xl">
            <div class="p-10 bg-white" v-if="editingOrg">
                <h3 class="text-2xl font-black text-slate-900 mb-8 tracking-tight">Reconfigure Tenant: {{ editingOrg.name }}</h3>
                
                <form @submit.prevent="submitUpdate" class="space-y-8">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-4">Operational Status</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" @click="editForm.status = 'active'"
                                    :class="editForm.status === 'active' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-400 border-slate-100'"
                                    class="py-4 rounded-2xl font-black text-xs uppercase tracking-widest border transition-all">
                                Active Node
                            </button>
                            <button type="button" @click="editForm.status = 'suspended'"
                                    :class="editForm.status === 'suspended' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 text-slate-400 border-slate-100'"
                                    class="py-4 rounded-2xl font-black text-xs uppercase tracking-widest border transition-all">
                                Suspend Access
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-4">Adjust Seat Allocation</label>
                        <input v-model="editForm.user_quota" type="number" 
                               class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-rose-500 focus:bg-white transition-all font-black text-xl outline-none shadow-inner" />
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="editingOrg = null" class="flex-1 py-5 rounded-2xl bg-slate-100 text-slate-400 font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">
                            Cancel
                        </button>
                        <button type="submit" :disabled="editForm.processing" class="flex-[2] py-5 rounded-2xl bg-slate-900 text-white font-black text-xs uppercase tracking-widest hover:bg-rose-600 transition-all shadow-xl">
                            Commit Changes
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
