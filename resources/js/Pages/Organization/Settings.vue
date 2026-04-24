<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Settings, 
    ShieldCheck, 
    History, 
    Palette, 
    Layout, 
    ArrowRight,
    User,
    Clock,
    Activity,
    Search,
    Filter,
    Users,
    Mail,
    Upload,
    FileSpreadsheet,
    Plus
} from 'lucide-vue-next';

const props = defineProps({
    organization: Object,
    activityLogs: Object,
    users: Object
});

const activeTab = ref('branding'); // branding | audit | participants

const importForm = useForm({
    file: null
});

const onFileChange = (e) => {
    importForm.file = e.target.files[0];
};

const submitImport = () => {
    importForm.post(route('organization.import-users'), {
        onSuccess: () => importForm.reset(),
    });
};

const brandingForm = useForm({
    name: props.organization.name,
    branding_settings: {
        primary_color: props.organization.branding_settings?.primary_color || '#4f46e5',
        secondary_color: props.organization.branding_settings?.secondary_color || '#10b981',
    }
});

const updateBranding = () => {
    brandingForm.patch(route('organization.update'), {
        preserveScroll: true
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getActionColor = (action) => {
    const map = {
        'created': 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'updated': 'bg-indigo-50 text-indigo-700 border-indigo-100',
        'deleted': 'bg-rose-50 text-rose-700 border-rose-100',
        'approved': 'bg-blue-50 text-blue-700 border-blue-100',
        'assignment_approved': 'bg-emerald-500 text-white',
        'assignment_rejected': 'bg-rose-500 text-white',
    };
    return map[action] || 'bg-slate-50 text-slate-700 border-slate-100';
};
</script>

<template>
    <Head title="Organization Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center shadow-2xl">
                    <Settings class="text-white w-6 h-6" />
                </div>
                <div>
                    <h2 class="font-black text-3xl text-slate-900 tracking-tight">Organization Control</h2>
                    <p class="text-slate-500 font-bold text-sm uppercase tracking-widest">Global Governance & Branding</p>
                </div>
            </div>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Sidebar Tabs -->
                    <div class="space-y-2">
                        <button @click="activeTab = 'branding'"
                                :class="activeTab === 'branding' ? 'bg-white text-slate-900 shadow-xl border-slate-100' : 'text-slate-500 hover:bg-slate-50'"
                                class="w-full flex items-center justify-between p-5 rounded-[2rem] border-2 border-transparent transition-all group">
                            <div class="flex items-center gap-4">
                                <Palette :class="activeTab === 'branding' ? 'text-indigo-600' : 'text-slate-300'" class="w-5 h-5 transition-colors" />
                                <span class="font-black">Identity & Branding</span>
                            </div>
                            <ArrowRight v-if="activeTab === 'branding'" class="w-4 h-4 text-indigo-600 animate-in slide-in-from-left-2" />
                        </button>

                        <button @click="activeTab = 'participants'"
                                :class="activeTab === 'participants' ? 'bg-white text-slate-900 shadow-xl border-slate-100' : 'text-slate-500 hover:bg-slate-50'"
                                class="w-full flex items-center justify-between p-5 rounded-[2rem] border-2 border-transparent transition-all group">
                            <div class="flex items-center gap-4">
                                <Users :class="activeTab === 'participants' ? 'text-indigo-600' : 'text-slate-300'" class="w-5 h-5 transition-colors" />
                                <span class="font-black">Participants</span>
                            </div>
                            <ArrowRight v-if="activeTab === 'participants'" class="w-4 h-4 text-indigo-600 animate-in slide-in-from-left-2" />
                        </button>

                        <button @click="activeTab = 'audit'"
                                :class="activeTab === 'audit' ? 'bg-white text-slate-900 shadow-xl border-slate-100' : 'text-slate-500 hover:bg-slate-50'"
                                class="w-full flex items-center justify-between p-5 rounded-[2rem] border-2 border-transparent transition-all group">
                            <div class="flex items-center gap-4">
                                <History :class="activeTab === 'audit' ? 'text-indigo-600' : 'text-slate-300'" class="w-5 h-5 transition-colors" />
                                <span class="font-black">Audit Trails</span>
                            </div>
                            <ArrowRight v-if="activeTab === 'audit'" class="w-4 h-4 text-indigo-600 animate-in slide-in-from-left-2" />
                        </button>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-3">
                        <!-- Participants Tab -->
                        <div v-if="activeTab === 'participants'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                            <!-- Bulk Import -->
                            <div class="bg-slate-900 rounded-[3.5rem] p-12 shadow-2xl relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/10 to-transparent"></div>
                                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                                    <div>
                                        <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mb-6 backdrop-blur-md border border-white/10">
                                            <Plus class="text-white w-8 h-8" />
                                        </div>
                                        <h3 class="text-3xl font-black text-white tracking-tight mb-4">Bulk Enrollment.</h3>
                                        <p class="text-indigo-100/60 font-bold leading-relaxed mb-0">
                                            Import hundreds of participants instantly from a CSV file.
                                            Format: <code class="text-white px-2 py-1 bg-white/5 rounded">name, email</code>
                                        </p>
                                    </div>
                                    <form @submit.prevent="submitImport" class="bg-white/5 p-8 rounded-[2.5rem] border border-white/10 backdrop-blur-sm space-y-6">
                                        <div class="relative group">
                                            <input type="file" @change="onFileChange" accept=".csv" 
                                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                            <div class="p-8 border-2 border-dashed border-white/20 rounded-[1.5rem] group-hover:border-indigo-400 transition-all text-center">
                                                <Upload class="text-white/40 w-10 h-10 mx-auto mb-3 group-hover:text-indigo-400 transition-colors" />
                                                <p class="text-white font-black text-sm">
                                                    {{ importForm.file ? importForm.file.name : 'Select CSV File' }}
                                                </p>
                                                <p class="text-white/30 text-xs mt-1">Drag and drop or click to browse</p>
                                            </div>
                                        </div>
                                        <button type="submit" 
                                                :disabled="importForm.processing || !importForm.file"
                                                class="w-full py-5 bg-white text-slate-900 rounded-2xl font-black shadow-xl hover:bg-indigo-400 hover:text-white transition-all disabled:opacity-30">
                                            Execute Import
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- User List -->
                            <div class="bg-white rounded-[3.5rem] p-4 shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                                <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                                    <div>
                                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Active Participants</h3>
                                        <p class="text-slate-400 font-bold text-sm uppercase tracking-widest">{{ users.total }} Verified Users</p>
                                    </div>
                                    <div class="relative h-12 w-64 hidden sm:block">
                                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 w-4 h-4" />
                                        <input type="text" placeholder="Find user..." class="w-full h-full pl-12 pr-4 bg-slate-50 border-0 rounded-xl font-bold text-sm focus:ring-4 focus:ring-indigo-50 transition-all" />
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-slate-50/50 transition-colors group">
                                                <td class="p-6">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center border border-indigo-100 shadow-sm group-hover:bg-white transition-all">
                                                            <span class="text-indigo-700 font-black">{{ u.name.charAt(0) }}</span>
                                                        </div>
                                                        <div>
                                                            <p class="font-black text-slate-900 leading-none mb-1">{{ u.name }}</p>
                                                            <p class="text-xs text-slate-400 font-medium">{{ u.email }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-6">
                                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                        {{ u.role }}
                                                    </span>
                                                </td>
                                                <td class="p-6 text-right">
                                                    <button class="p-2 text-slate-300 hover:text-slate-900 transition-colors">
                                                        <ArrowRight class="w-5 h-5" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination -->
                                <div class="px-8 py-6 bg-slate-50/30 flex items-center justify-between border-t border-slate-50">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Page {{ users.current_page }} of {{ users.last_page }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Branding Tab -->
                        <div v-if="activeTab === 'branding'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                            <div class="bg-white rounded-[3.5rem] p-12 shadow-2xl shadow-slate-200/50 border border-slate-100">
                                <h3 class="text-2xl font-black text-slate-900 mb-8 tracking-tight">Public Identity</h3>
                                
                                <form @submit.prevent="updateBranding" class="space-y-10">
                                    <div class="space-y-4">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Academy Name</label>
                                        <input v-model="brandingForm.name" type="text" 
                                               class="w-full p-6 bg-slate-50 border-2 border-transparent rounded-[2rem] focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50 transition-all font-bold text-slate-700" />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="space-y-4">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Primary Color</label>
                                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-[2rem] border-2 border-transparent">
                                                <input v-model="brandingForm.branding_settings.primary_color" type="color" class="w-12 h-12 rounded-xl border-0 bg-transparent cursor-pointer" />
                                                <input v-model="brandingForm.branding_settings.primary_color" type="text" class="bg-transparent border-0 font-mono font-bold text-slate-600 focus:ring-0 p-0" />
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Accent Color</label>
                                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-[2rem] border-2 border-transparent">
                                                <input v-model="brandingForm.branding_settings.secondary_color" type="color" class="w-12 h-12 rounded-xl border-0 bg-transparent cursor-pointer" />
                                                <input v-model="brandingForm.branding_settings.secondary_color" type="text" class="bg-transparent border-0 font-mono font-bold text-slate-600 focus:ring-0 p-0" />
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" 
                                            :disabled="brandingForm.processing"
                                            class="w-full md:w-auto px-12 py-6 bg-slate-900 text-white rounded-[2rem] font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all active:scale-95 disabled:opacity-50">
                                        Commit Changes
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Audit Tab -->
                        <div v-if="activeTab === 'audit'" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                            <div class="bg-white rounded-[3.5rem] p-4 shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                                <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                                    <div>
                                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">System Integrity Log</h3>
                                        <p class="text-slate-400 font-bold text-sm">Immutable record of high-stakes actions</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="bg-indigo-50 px-4 py-2 rounded-full flex items-center gap-2 border border-indigo-100">
                                            <Activity class="w-4 h-4 text-indigo-600" />
                                            <span class="text-indigo-700 font-black text-xs">{{ activityLogs.total }} Events</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-slate-50/50">
                                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Performed By</th>
                                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Activity</th>
                                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Context</th>
                                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="log in activityLogs.data" :key="log.id" class="hover:bg-slate-50/50 transition-colors group">
                                                <td class="p-6">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-white group-hover:shadow-md transition-all">
                                                            <User class="text-slate-400 w-5 h-5" />
                                                        </div>
                                                        <div>
                                                            <p class="font-black text-slate-900 text-sm">{{ log.user?.name || 'System' }}</p>
                                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ log.user?.role }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-6">
                                                    <span :class="getActionColor(log.action)" 
                                                          class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-transparent shadow-sm inline-block">
                                                        {{ log.action.replace('_', ' ') }}
                                                    </span>
                                                </td>
                                                <td class="p-6">
                                                    <p class="text-sm font-bold text-slate-600 max-w-xs leading-relaxed">
                                                        {{ log.description }}
                                                    </p>
                                                </td>
                                                <td class="p-6">
                                                    <div class="flex items-center gap-2 text-slate-400 font-medium text-xs">
                                                        <Clock class="w-4 h-4" />
                                                        {{ formatDate(log.created_at) }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Placeholder -->
                                <div class="p-8 bg-slate-50/30 flex items-center justify-center border-t border-slate-50">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Showing {{ activityLogs.from }}-{{ activityLogs.to }} of {{ activityLogs.total }} total activities</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.glass-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}
</style>
