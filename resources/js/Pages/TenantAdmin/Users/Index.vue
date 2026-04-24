<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Users, 
    Upload, 
    Search, 
    MoreHorizontal, 
    Check, 
    Download,
    X,
    FileText,
    Shield
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    users: Object
});

const showImportModal = ref(false);
const importForm = useForm({
    file: null
});

const submitImport = () => {
    importForm.post(route('tenant.users.bulk-import'), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        }
    });
};

const handleFileUpload = (e) => {
    importForm.file = e.target.files[0];
};

const getRoleBadge = (role) => {
    switch (role) {
        case 'admin': return 'bg-rose-50 text-rose-600 border-rose-100';
        case 'expert': return 'bg-indigo-50 text-indigo-600 border-indigo-100';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>

<template>
    <Head title="Personnel Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between py-6">
                <div>
                    <div class="flex items-center gap-2 text-indigo-500 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                        <span class="w-8 h-px bg-indigo-500"></span> Node Inventory
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">
                        Personnel Directory<span class="text-indigo-500">.</span>
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="showImportModal = true" 
                            class="bg-indigo-600 text-white px-8 py-4 rounded-[2rem] font-black text-sm flex items-center gap-3 shadow-2xl hover:bg-slate-900 transition-all hover:-translate-y-1 active:scale-95">
                        <Upload class="w-5 h-5" /> Bulk Import CSV
                    </button>
                    <button class="bg-white text-slate-900 border border-slate-100 px-8 py-4 rounded-[2rem] font-black text-sm flex items-center gap-3 shadow-lg hover:bg-slate-50 transition-all">
                        <Download class="w-5 h-5 text-indigo-500" /> Export Ledger
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-10">
            <!-- Functional Filters -->
            <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-xl flex flex-wrap items-center justify-between gap-6">
                <div class="flex-1 min-w-[300px] relative">
                    <Search class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input type="text" placeholder="Search by name, email, or competency unit..." 
                           class="w-full pl-16 pr-8 py-5 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white transition-all font-bold text-slate-900 outline-none" />
                </div>
                <div class="flex gap-4">
                    <select class="bg-slate-50 border-2 border-transparent px-8 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-400 outline-none focus:border-indigo-500 focus:bg-white transition-all appearance-none cursor-pointer">
                        <option>All Roles</option>
                    </select>
                    <select class="bg-slate-50 border-2 border-transparent px-8 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-400 outline-none focus:border-indigo-500 focus:bg-white transition-all appearance-none cursor-pointer">
                        <option>Sort: Latest Onboarded</option>
                    </select>
                </div>
            </div>

            <!-- Personnel Ledger -->
            <div class="bg-white rounded-[4rem] border border-slate-100 shadow-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Personnel Node</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status/Role</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Identity</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Onboarded</th>
                                <th class="px-10 py-6"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="user in users.data" :key="user.id" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-10 py-8">
                                    <div class="flex items-center gap-5">
                                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-lg group-hover:scale-110 transition-transform">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-900 text-lg tracking-tight leading-none group-hover:text-indigo-600 transition-colors">{{ user.name }}</h4>
                                            <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">UUID: {{ user.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8">
                                    <div :class="getRoleBadge(user.role)" 
                                         class="inline-flex px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border shadow-sm">
                                        {{ user.role }}
                                    </div>
                                </td>
                                <td class="px-10 py-8">
                                    <span class="text-slate-600 font-bold text-sm">{{ user.email }}</span>
                                </td>
                                <td class="px-10 py-8">
                                    <span class="text-slate-400 font-bold text-sm">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <button class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center border border-slate-100 text-slate-400 hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                        <MoreHorizontal class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-10 border-t border-slate-50">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>

        <!-- Bulk Ingestion Modal -->
        <Modal :show="showImportModal" @close="showImportModal = false" maxWidth="xl">
            <div class="p-12 bg-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 -mr-20 -mt-20"></div>
                
                <div class="mb-10 relative z-10">
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">Bulk Ingestion Engine.</h3>
                    <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest mt-1">Scale your academy workforce instantly</p>
                </div>

                <div class="bg-amber-50 border border-amber-100 p-6 rounded-3xl mb-10 flex gap-4 relative z-10">
                    <Shield class="w-6 h-6 text-amber-600 shrink-0" />
                    <div>
                        <h4 class="text-amber-900 font-black text-[10px] uppercase tracking-widest mb-1">CSV Template Requirements</h4>
                        <p class="text-amber-700/80 text-xs font-bold leading-relaxed">
                            Ensure your CSV has two columns: <span class="font-black text-amber-900">Name, Email</span>. 
                            Default password for all new nodes will be <span class="font-black text-amber-900">password123</span>.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitImport" class="space-y-8 relative z-10">
                    <div class="relative group">
                        <input type="file" @change="handleFileUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                        <div class="p-12 border-2 border-dashed border-slate-200 rounded-[3rem] bg-slate-50 flex flex-col items-center group-hover:bg-indigo-50 group-hover:border-indigo-200 transition-all">
                            <div class="w-16 h-16 bg-white rounded-2xl shadow-xl flex items-center justify-center text-indigo-500 mb-6 group-hover:scale-110 transition-transform">
                                <FileText class="w-8 h-8" />
                            </div>
                            <h4 class="font-black text-slate-900 tracking-tight mb-2">{{ importForm.file ? importForm.file.name : 'Select Data File' }}</h4>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Accepts .CSV or .TXT formats</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" @click="showImportModal = false" class="flex-1 py-5 rounded-[2.5rem] bg-slate-100 text-slate-400 font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">
                            Abort
                        </button>
                        <button type="submit" :disabled="!importForm.file || importForm.processing" class="flex-[2] py-5 rounded-[2.5rem] bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 transition-all shadow-2xl disabled:opacity-50">
                            {{ importForm.processing ? 'Ingesting...' : 'Initialize Ingestion' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
