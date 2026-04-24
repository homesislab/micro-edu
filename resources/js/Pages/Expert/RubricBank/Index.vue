<script setup>
import { ref, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Plus, 
    Trash2, 
    Edit2,
    Layout,
    ArrowRight,
    X,
    PlusCircle,
    Minus,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    templates: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    criteria_json: [
        { label: 'Quality', points: 10 },
        { label: 'Completeness', points: 10 }
    ],
    is_default: false
});

const openModal = (template = null) => {
    isEditing.value = !!template;
    if (template) {
        editingId.value = template.id;
        form.name = template.name;
        form.criteria_json = [...template.criteria_json];
        form.is_default = template.is_default;
    } else {
        form.reset();
        form.criteria_json = [
            { label: 'Quality', points: 10 },
            { label: 'Completeness', points: 10 }
        ];
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const addCriteria = () => {
    form.criteria_json.push({ label: '', points: 5 });
};

const removeCriteria = (index) => {
    form.criteria_json.splice(index, 1);
};

const totalPoints = computed(() => {
    return form.criteria_json.reduce((sum, item) => sum + (Number(item.points) || 0), 0);
});

const submit = () => {
    if (isEditing.value) {
        form.patch(route('expert.rubric-bank.update', editingId.value), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('expert.rubric-bank.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteTemplate = (id) => {
    if (confirm('Are you sure you want to remove this rubric standard from your library?')) {
        useForm({}).delete(route('expert.rubric-bank.delete', id));
    }
};
</script>

<template>
    <Head title="Rubric Bank" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">Rubric Library</h1>
                    <p class="text-slate-400 mt-2 font-bold uppercase tracking-[0.2em] text-xs">Standardize your Kirkpatrick L3 assessments</p>
                </div>
                <button @click="openModal()" 
                        class="bg-slate-900 text-white px-8 py-4 rounded-[2rem] font-black text-sm flex items-center gap-3 shadow-2xl hover:bg-emerald-600 transition-all hover:-translate-y-1 active:scale-95">
                    <Plus class="w-5 h-5 text-emerald-400" /> New Rubric Standard
                </button>
            </div>

            <!-- List State -->
            <div v-if="templates.length === 0" 
                 class="bg-white rounded-[3rem] p-20 border-2 border-dashed border-slate-100 flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                    <Layout class="w-10 h-10 text-slate-200" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2">No standards defined</h3>
                <p class="text-slate-400 max-w-sm font-bold">Create reusable rubric standards to ensure objective grading across all your programs.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="template in templates" :key="template.id" 
                     class="group bg-white rounded-[2.5rem] p-8 border border-slate-100 hover:border-slate-200 hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500">
                    <div class="flex justify-between items-start mb-6">
                        <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-600">
                            <CheckCircle2 class="w-6 h-6" />
                        </div>
                        <div class="flex gap-2">
                            <button @click="openModal(template)" class="p-2 hover:bg-slate-50 rounded-xl transition-all">
                                <Edit2 class="w-4 h-4 text-slate-400" />
                            </button>
                            <button @click="deleteTemplate(template.id)" class="p-2 hover:bg-red-50 rounded-xl transition-all group/del">
                                <Trash2 class="w-4 h-4 text-slate-400 group-hover/del:text-red-500" />
                            </button>
                        </div>
                    </div>

                    <h3 class="text-xl font-black text-slate-900 mb-4 group-hover:text-emerald-600 transition-colors">{{ template.name }}</h3>
                    
                    <div class="space-y-3 mb-8">
                        <div v-for="(criteria, idx) in template.criteria_json.slice(0, 3)" :key="idx" class="flex justify-between items-center text-xs font-bold">
                            <span class="text-slate-400 uppercase tracking-wider">{{ criteria.label }}</span>
                            <span class="text-slate-900">{{ criteria.points }} pts</span>
                        </div>
                        <p v-if="template.criteria_json.length > 3" class="text-[10px] text-slate-300 font-bold italic">+ {{ template.criteria_json.length - 3 }} more criteria</p>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                        <div class="text-left">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Total Value</p>
                            <p class="text-lg font-black text-slate-900">{{ template.criteria_json.reduce((s, c) => s + c.points, 0) }} Pts</p>
                        </div>
                        <ArrowRight class="w-5 h-5 text-slate-200 group-hover:text-emerald-600 group-hover:translate-x-2 transition-all" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
                
                <div class="relative w-full max-w-2xl bg-white rounded-[3rem] shadow-2xl p-12 overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="flex justify-between items-start mb-10">
                        <div>
                            <h2 class="text-3xl font-black text-slate-900">{{ isEditing ? 'Edit Standard' : 'New Standard' }}</h2>
                            <p class="text-slate-400 mt-2 font-bold uppercase tracking-widest text-[10px]">Define objective grading metrics</p>
                        </div>
                        <button @click="closeModal" class="p-3 bg-slate-50 rounded-2xl hover:bg-slate-100 transition-all">
                            <X class="w-5 h-5 text-slate-400" />
                        </button>
                    </div>

                    <div class="overflow-y-auto pr-4 custom-scrollbar flex-1">
                        <form @submit.prevent="submit" class="space-y-10">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Standard Name</label>
                                <input v-model="form.name" type="text" 
                                       class="w-full px-6 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-emerald-500 font-bold text-slate-900" 
                                       placeholder="e.g. Code Quality Standard or Presentation Rubric">
                            </div>

                            <div class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Grading Criteria Matrix</label>
                                    <p class="text-xs font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg">Total: {{ totalPoints }} Pts</p>
                                </div>

                                <div class="space-y-4">
                                    <div v-for="(criteria, idx) in form.criteria_json" :key="idx" 
                                         class="flex gap-4 items-center group/row animate-in slide-in-from-left-2 duration-300">
                                        <input v-model="criteria.label" type="text" placeholder="Criteria Name" 
                                               class="flex-1 px-6 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-emerald-500 font-bold text-slate-900 text-sm">
                                        <input v-model.number="criteria.points" type="number" placeholder="Pts" 
                                               class="w-24 px-6 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-emerald-500 font-black text-slate-900 text-sm text-center">
                                        <button @click="removeCriteria(idx)" type="button" class="p-2 text-slate-200 hover:text-red-500 transition-colors">
                                            <Minus class="w-4 h-4" />
                                        </button>
                                    </div>
                                    
                                    <button @click="addCriteria" type="button" 
                                            class="w-full py-4 border-2 border-dashed border-slate-100 rounded-2xl text-slate-400 font-bold text-xs hover:border-emerald-200 hover:text-emerald-500 hover:bg-emerald-50 transition-all flex items-center justify-center gap-2">
                                        <PlusCircle class="w-4 h-4" /> Add Next Criteria
                                    </button>
                                </div>
                            </div>

                            <div class="pt-10 flex gap-4 sticky bottom-0 bg-white">
                                <button type="submit" 
                                        :disabled="form.processing"
                                        class="flex-1 bg-slate-900 text-white rounded-3xl py-4 font-black shadow-xl shadow-slate-200 hover:scale-105 active:scale-95 transition-all disabled:opacity-50">
                                    {{ isEditing ? 'Update Library' : 'Save to Library' }}
                                </button>
                                <button @click="closeModal" type="button" 
                                        class="px-8 py-4 bg-slate-50 text-slate-400 rounded-3xl font-black hover:bg-slate-100 transition-all">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
