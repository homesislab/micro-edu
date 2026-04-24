<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Plus, 
    GripVertical, 
    MoreVertical, 
    Play, 
    FileText,
    HelpCircle, 
    Award, 
    Edit2, 
    Trash2, 
    X,
    Rocket, 
    Sparkles, 
    Loader2,
    ChevronRight,
    Target,
    Video,
    Layout,
    PlusCircle,
    Minus,
    ChevronDown,
    Check
} from 'lucide-vue-next';

const props = defineProps({
    course: { type: Object, required: true },
    isGenerating: { type: Boolean, default: false }
});

const emit = defineEmits(['add-module', 'edit-module', 'delete-module', 'add-item', 'edit-item', 'delete-item', 'generate-ai']);

// ─── Local State ──────────────────────────────────────────────────────────────
// Map course.modules to local ref for UI state (expanded)
const modules = ref(props.course.modules?.map(m => ({ ...m, expanded: true })) || []);

// Sync local modules ref with props when course updates
watch(() => props.course.modules, (newModules) => {
    modules.value = newModules?.map(m => {
        const existing = modules.value.find(ex => ex.id === m.id);
        return { ...m, expanded: existing ? existing.expanded : true };
    }) || [];
}, { deep: true });

const showModuleModal = ref(false);
const showItemModal = ref(false);
const isEditing = ref(false);
const activeDropdown = ref(null); // ID of module with open dropdown

const moduleForm = useForm({
    id: null,
    title: '',
    description: ''
});

const itemForm = useForm({
    id: null,
    module_id: null,
    title: '',
    type: 'literal', // literal, visual, knowledge, exercise
    content: '',
    is_capstone: false,
    rubric_json: []
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
const ITEM_TYPES = {
    literal: { label: 'Literal Content', icon: FileText, color: 'text-slate-500', bg: 'bg-slate-50' },
    visual: { label: 'Visual Content', icon: Video, color: 'text-blue-500', bg: 'bg-blue-50' },
    knowledge: { label: 'Knowledge Bank', icon: HelpCircle, color: 'text-amber-500', bg: 'bg-amber-50' },
    exercise: { label: 'Mission Exercise', icon: Target, color: 'text-indigo-600', bg: 'bg-indigo-50' },
};

const getMeta = (type) => ITEM_TYPES[type] ?? ITEM_TYPES.literal;

const toggleModule = (mId) => {
    const mod = modules.value.find(m => m.id === mId);
    if (mod) mod.expanded = !mod.expanded;
};

const openModuleModal = (module = null) => {
    isEditing.value = !!module;
    if (module) {
        moduleForm.id = module.id;
        moduleForm.title = module.title;
        moduleForm.description = module.description ?? '';
    } else {
        moduleForm.reset();
    }
    showModuleModal.value = true;
};

const openItemModal = (moduleId, type, item = null) => {
    isEditing.value = !!item;
    itemForm.id = item?.id ?? null;
    itemForm.module_id = moduleId;
    itemForm.type = type;
    itemForm.title = item?.title ?? '';
    itemForm.content = item?.content ?? '';
    itemForm.is_capstone = item?.is_capstone ?? false;
    itemForm.rubric_json = item?.rubric_json ?? [];
    
    if (type === 'exercise' && itemForm.rubric_json.length === 0) {
        itemForm.rubric_json = [{ label: 'Accuracy', points: 10 }];
    }
    
    activeDropdown.value = null;
    showItemModal.value = true;
};

const addRubricRow = () => {
    itemForm.rubric_json.push({ label: '', points: 5 });
};

const removeRubricRow = (index) => {
    itemForm.rubric_json.splice(index, 1);
};

const closeModals = () => {
    showModuleModal.value = false;
    showItemModal.value = false;
    moduleForm.reset();
    itemForm.reset();
};

// ─── Handlers ────────────────────────────────────────────────────────────────
const handleSubmitModule = () => {
    if (isEditing.value) {
        moduleForm.patch(route('expert.modules.update', moduleForm.id), {
            onSuccess: () => closeModals()
        });
    } else {
        moduleForm.post(route('expert.modules.store', props.course.id), {
            onSuccess: () => closeModals()
        });
    }
};

const handleSubmitItem = () => {
    const payload = {
        title: itemForm.title,
        type: itemForm.type,
        content: itemForm.content,
        is_capstone: itemForm.is_capstone,
        rubric_json: itemForm.type === 'exercise' ? itemForm.rubric_json : null,
        module_id: itemForm.module_id,
        order: 0
    };

    if (isEditing.value) {
        itemForm.transform(() => payload).patch(route('expert.items.update', itemForm.id), {
            onSuccess: () => closeModals()
        });
    } else {
        itemForm.transform(() => payload).post(route('expert.items.store', props.course.id), {
            onSuccess: () => closeModals()
        });
    }
};

const deleteModule = (id) => {
    if (confirm('Delete this module and all its items?')) {
        useForm({}).delete(route('expert.modules.delete', id));
    }
};

const deleteItem = (id) => {
    if (confirm('Delete this curriculum item?')) {
        useForm({}).delete(route('expert.items.delete', id));
    }
};
</script>

<template>
    <div class="max-w-5xl mx-auto py-10 animate-in fade-in slide-in-from-bottom-5 duration-700">
        
        <!-- Header & Action Row -->
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Architect Studio</h2>
                <p class="text-slate-400 font-bold text-[10px] uppercase tracking-[0.2em] mt-1">Design your program's learning architecture</p>
            </div>
            <div class="flex items-center gap-4">
                <button @click="$emit('generate-ai')"
                        :disabled="isGenerating"
                        class="bg-indigo-50 text-indigo-700 border border-indigo-100 px-6 py-3 rounded-2xl font-black text-xs flex items-center gap-3 hover:bg-indigo-100 transition-all shadow-sm active:scale-95 disabled:opacity-50">
                    <component :is="isGenerating ? Loader2 : Sparkles" :class="{ 'animate-spin': isGenerating }" class="w-4 h-4" />
                    {{ isGenerating ? 'Forging Architecture...' : 'AI Architect' }}
                </button>
                <button @click="openModuleModal()" 
                        class="bg-slate-900 text-white px-8 py-4 rounded-[2rem] font-black text-sm flex items-center gap-3 shadow-2xl hover:bg-indigo-600 transition-all hover:-translate-y-1 active:scale-95">
                    <Plus class="w-5 h-5 text-indigo-400" /> New Modul
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="modules.length === 0" 
             class="bg-white p-32 rounded-[4rem] border-2 border-dashed border-slate-100 text-center relative overflow-hidden group shadow-sm">
            <div class="relative z-10">
                <div class="bg-indigo-50 w-24 h-24 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-indigo-100/50">
                    <Rocket class="w-12 h-12 text-indigo-600" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">No sequence design found.</h3>
                <p class="text-slate-400 font-medium max-w-sm mx-auto mt-3">Create your first modul to define the learning architecture of this program.</p>
                <button @click="openModuleModal()"
                        class="mt-10 bg-indigo-600 text-white px-10 py-5 rounded-[2rem] font-black text-sm shadow-2xl shadow-indigo-200 hover:bg-slate-900 transition-all hover:-translate-y-1 active:scale-95">
                    Initialize First Modul
                </button>
            </div>
            <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-indigo-50 rounded-full blur-[80px] opacity-50"></div>
        </div>

        <!-- Populated State: Accordion -->
        <div v-else class="space-y-6">
            <div v-for="(module, mIdx) in modules" :key="module.id"
                 class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden transition-all duration-500">
                
                <!-- Module Header -->
                <div class="px-8 py-6 flex items-center justify-between border-b border-slate-50 bg-gray-50/50">
                    <div class="flex items-center gap-6 flex-1 cursor-pointer" @click="toggleModule(module.id)">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center font-black text-slate-400 border border-slate-100 text-xs shadow-sm">
                            {{ String(mIdx + 1).padStart(2, '0') }}
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">{{ module.title }}</h3>
                            <p v-if="module.description" class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ module.description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Add Item Dropdown -->
                        <div class="relative">
                            <button @click="activeDropdown = activeDropdown === module.id ? null : module.id"
                                    class="bg-white border border-slate-100 px-5 py-3 rounded-xl font-black text-xs flex items-center gap-2 hover:bg-slate-50 transition-all shadow-sm">
                                <Plus class="w-4 h-4 text-indigo-600" /> + Add Item
                                <ChevronDown class="w-3 h-3 text-slate-300" />
                            </button>
                            
                            <div v-if="activeDropdown === module.id" 
                                 class="absolute right-0 mt-3 w-64 bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-slate-100 z-50 p-3 animate-in fade-in zoom-in-95 overflow-hidden">
                                <button @click="openItemModal(module.id, 'literal')" class="w-full flex items-center gap-4 p-4 hover:bg-slate-50 rounded-2xl transition-all group">
                                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                                        <FileText class="w-5 h-5 text-slate-400 group-hover:text-slate-600" />
                                    </div>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-slate-900">Literal Content</p>
                                        <p class="text-[9px] text-slate-400 font-bold">Text based materials</p>
                                    </div>
                                </button>
                                <button @click="openItemModal(module.id, 'visual')" class="w-full flex items-center gap-4 p-4 hover:bg-slate-50 rounded-2xl transition-all group">
                                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                                        <Video class="w-5 h-5 text-blue-500 group-hover:text-blue-600" />
                                    </div>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-slate-900">Visual Content</p>
                                        <p class="text-[9px] text-slate-400 font-bold">Video based materials</p>
                                    </div>
                                </button>
                                <button @click="openItemModal(module.id, 'knowledge')" class="w-full flex items-center gap-4 p-4 hover:bg-slate-50 rounded-2xl transition-all group">
                                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                                        <HelpCircle class="w-5 h-5 text-amber-500 group-hover:text-amber-600" />
                                    </div>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-slate-900">Knowledge Bank</p>
                                        <p class="text-[9px] text-slate-400 font-bold">Quizzes & Assessment</p>
                                    </div>
                                </button>
                                <div class="h-px bg-slate-50 my-1 mx-2"></div>
                                <button @click="openItemModal(module.id, 'exercise')" class="w-full flex items-center gap-4 p-4 hover:bg-indigo-50/50 rounded-2xl transition-all group">
                                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                                        <Target class="w-5 h-5 text-indigo-600 group-hover:text-indigo-700" />
                                    </div>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-indigo-600">Mission Exercise</p>
                                        <p class="text-[9px] text-indigo-400 font-bold uppercase tracking-tighter">Evaluation L3 (Rocket)</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div class="h-6 w-px bg-slate-200"></div>

                        <button @click="openModuleModal(module)" class="p-3 text-slate-300 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                            <Edit2 class="w-4 h-4" />
                        </button>
                        <button @click="deleteModule(module.id)" class="p-3 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                            <Trash2 class="w-4 h-4" />
                        </button>
                        <button @click="toggleModule(module.id)" class="p-3 text-slate-300 hover:text-slate-900 rounded-xl transition-all">
                            <ChevronRight :class="{ 'rotate-90': module.expanded }" class="w-5 h-5 transition-transform" />
                        </button>
                    </div>
                </div>

                <!-- Module Items -->
                <div v-show="module.expanded" class="p-8 space-y-4 bg-white animate-in slide-in-from-top-2 duration-300">
                    <div v-if="!module.curriculum_items?.length" 
                         class="py-12 border-2 border-dashed border-slate-50 rounded-[2rem] text-center">
                        <p class="text-slate-300 font-bold text-sm tracking-tight">No units assigned to this modul architecture.</p>
                    </div>
                    
                    <div v-for="item in module.curriculum_items" :key="item.id"
                         :class="[
                            'group flex items-center justify-between p-5 transition-all',
                            item.type === 'exercise' 
                                ? 'bg-indigo-50/30 border-l-8 border-indigo-600 rounded-r-[2rem] rounded-l-lg border-y border-r border-indigo-100 shadow-sm' 
                                : 'bg-white border border-slate-50 hover:border-indigo-100 hover:shadow-xl hover:shadow-indigo-500/5 rounded-[2rem]'
                         ]">
                        
                        <div class="flex items-center gap-6">
                            <div :class="[
                                'w-12 h-12 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 shadow-sm', 
                                getMeta(item.type).bg,
                                item.type === 'exercise' ? 'bg-white shadow-md ring-1 ring-black/5' : ''
                            ]">
                                <component :is="getMeta(item.type).icon" :class="['w-6 h-6', getMeta(item.type).color]" />
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <h4 :class="item.type === 'exercise' ? 'text-indigo-900 font-black' : 'text-slate-900 font-bold'" class="text-sm tracking-tight">
                                        {{ item.title }}
                                    </h4>
                                    <span v-if="item.is_capstone" 
                                          class="bg-indigo-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">
                                        Capstone
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ getMeta(item.type).label }}</span>
                                    <template v-if="item.type === 'exercise'">
                                        <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                        <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">{{ item.rubric_json?.length || 0 }} Rubric Criterias</span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity pr-2">
                             <button @click="openItemModal(module.id, item.type, item)"
                                    class="p-2.5 text-slate-400 hover:text-indigo-600 transition-colors rounded-xl hover:bg-indigo-50">
                                <Edit2 class="w-4 h-4" />
                            </button>
                            <button @click="deleteItem(item.id)"
                                    class="p-2.5 text-slate-400 hover:text-rose-600 transition-colors rounded-xl hover:bg-rose-50">
                                <Trash2 class="w-4 h-4" />
                            </button>
                            <GripVertical class="w-5 h-5 text-slate-200 cursor-grab ml-2 hover:text-slate-400" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Modals ─── -->

        <!-- Modal: Module -->
        <div v-if="showModuleModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-2xl z-[100] flex items-center justify-center p-6">
            <div class="bg-white w-full max-w-lg rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-500 border border-white">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ isEditing ? 'Edit Architecture Module' : 'Initialize Module' }}</h4>
                        <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-1">Structuring learning sequence units</p>
                    </div>
                    <button @click="closeModals" class="w-12 h-12 hover:bg-white rounded-2xl flex items-center justify-center transition-all border border-transparent hover:border-slate-100">
                        <X class="w-6 h-6 text-slate-400" />
                    </button>
                </div>
                <form @submit.prevent="handleSubmitModule" class="p-10 space-y-8">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Module Title</label>
                        <input v-model="moduleForm.title" type="text" placeholder="e.g. Fundamental Architecture" required
                               class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-bold text-slate-700 outline-none shadow-inner" />
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Short Description</label>
                        <textarea v-model="moduleForm.description" rows="3" placeholder="Briefly describe the module's mission..."
                                  class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-bold text-slate-700 outline-none shadow-inner resize-none"></textarea>
                    </div>
                    <button type="submit" :disabled="moduleForm.processing" class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all active:scale-95 disabled:opacity-50">
                        {{ isEditing ? 'Commit Changes' : 'Initialize Modul' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Modal: Item -->
        <div v-if="showItemModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-2xl z-[100] flex items-center justify-center p-6 overflow-y-auto">
            <div class="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-500 border border-white my-8">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between"
                     :class="itemForm.type === 'exercise' ? 'bg-indigo-50/50' : 'bg-slate-50/50'">
                    <div class="flex items-center gap-6">
                        <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg', getMeta(itemForm.type).bg]">
                            <component :is="getMeta(itemForm.type).icon" :class="['w-8 h-8', getMeta(itemForm.type).color]" />
                        </div>
                        <div>
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ isEditing ? 'Edit Unit' : 'Assembling New Unit' }}</h4>
                            <p class="text-indigo-600 font-bold text-[10px] uppercase tracking-widest mt-0.5">{{ getMeta(itemForm.type).label }}</p>
                        </div>
                    </div>
                    <button @click="closeModals" class="w-12 h-12 hover:bg-white rounded-2xl flex items-center justify-center transition-all border border-transparent hover:border-slate-100">
                        <X class="w-6 h-6 text-slate-400" />
                    </button>
                </div>
                
                <form @submit.prevent="handleSubmitItem" class="p-10 space-y-8">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Unit Title</label>
                        <input v-model="itemForm.title" type="text" placeholder="e.g. Masterclass: Technical Implementation" required
                               class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-bold text-slate-700 outline-none shadow-inner" />
                    </div>

                    <!-- Literal/Visual Form -->
                    <div v-if="itemForm.type === 'literal' || itemForm.type === 'visual'" class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">
                            {{ itemForm.type === 'visual' ? 'Video Tautan (URL/ID)' : 'Content Teks' }}
                        </label>
                        <textarea v-model="itemForm.content" rows="6" :placeholder="itemForm.type === 'visual' ? 'Enter YouTube ID or URL...' : 'Write your narrative here...'"
                                  class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-bold text-slate-700 outline-none shadow-inner resize-none"></textarea>
                    </div>

                    <!-- Exercise Form -->
                    <div v-if="itemForm.type === 'exercise'" class="space-y-8">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-2">
                                <label class="block text-[10px] font-black text-indigo-600 uppercase tracking-widest">Instruksi Praktik (L3)</label>
                                <div class="flex items-center gap-2">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Capstone Project</p>
                                    <button type="button" @click="itemForm.is_capstone = !itemForm.is_capstone" 
                                            :class="itemForm.is_capstone ? 'bg-indigo-600' : 'bg-slate-200'"
                                            class="w-10 h-5 rounded-full relative transition-all duration-300 focus:outline-none">
                                        <div :class="itemForm.is_capstone ? 'translate-x-5' : 'translate-x-1'"
                                             class="absolute top-1 w-3 h-3 bg-white rounded-full transition-all"></div>
                                    </button>
                                </div>
                            </div>
                            <textarea v-model="itemForm.content" rows="4" placeholder="Describe the mission and practical output required..."
                                      class="w-full px-8 py-5 rounded-2xl bg-indigo-50/30 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-medium text-slate-700 outline-none shadow-inner resize-none"></textarea>
                        </div>

                        <!-- Rubric Section -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-2">
                                <label class="block text-[10px] font-black text-indigo-600 uppercase tracking-widest">Grading Rubric Matrix</label>
                                <button type="button" @click="addRubricRow" class="flex items-center gap-1.5 text-[10px] font-black text-indigo-600 hover:text-slate-900 transition-colors uppercase tracking-widest">
                                    <PlusCircle class="w-4 h-4" /> Add Criteria
                                </button>
                            </div>
                            
                            <div class="space-y-3">
                                <div v-for="(rubric, rIdx) in itemForm.rubric_json" :key="rIdx"
                                     class="flex gap-4 items-center animate-in slide-in-from-left-2 duration-300">
                                    <input v-model="rubric.label" type="text" placeholder="Criteria Name (e.g. Clarity)"
                                           class="flex-1 px-6 py-4 rounded-xl bg-slate-50 border-none focus:ring-4 focus:ring-indigo-50 transition-all font-bold text-slate-700 text-sm shadow-inner" />
                                    <div class="flex items-center gap-2">
                                        <input v-model.number="rubric.points" type="number" placeholder="Pts"
                                               class="w-20 px-4 py-4 rounded-xl bg-slate-50 border-none focus:ring-4 focus:ring-indigo-50 transition-all font-black text-center text-slate-700 text-sm shadow-inner" />
                                        <button type="button" @click="removeRubricRow(rIdx)" class="p-2 text-slate-300 hover:text-rose-500 transition-colors">
                                            <Minus class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" :disabled="itemForm.processing" class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-lg shadow-2xl hover:bg-emerald-600 transition-all active:scale-95 disabled:opacity-50">
                        {{ isEditing ? 'Commit Unit Update' : 'Initialize Unit Strategy' }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Custom transitions for accordion */
.animate-in {
    animation-delay: 0.1s;
}
</style>
