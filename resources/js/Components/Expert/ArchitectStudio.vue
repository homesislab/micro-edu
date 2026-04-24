<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
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
    Check,
    Activity,
    Shuffle,
    Layers,
    PenTool,
    Database,
    Eye,
    ShieldCheck
} from 'lucide-vue-next';

const props = defineProps({
    course: { type: Object, required: true },
    isGenerating: { type: Boolean, default: false }
});

const emit = defineEmits(['add-module', 'edit-module', 'delete-module', 'add-item', 'edit-item', 'delete-item', 'generate-ai']);

// ─── Local State ──────────────────────────────────────────────────────────────
// Map course.modules to local ref for UI state (expanded)
const modules = ref(props.course.modules?.map(m => ({ ...m, expanded: true })) || []);

const activeModuleForImport = ref(null);
const activeItemForRubricImport = ref(null);

// --- Rubric Bank Integration ---
const showRubricBankModal = ref(false);
const rubricBankTemplates = ref([]);

const fetchRubricBank = async () => {
    try {
        const response = await axios.get(route('expert.rubric-bank.api'));
        rubricBankTemplates.value = response.data;
    } catch (e) {
        console.error('Failed to fetch rubric bank', e);
    }
};

const openRubricImportModal = (item = null) => {
    if (item) {
        activeItemForRubricImport.value = item;
    }
    fetchRubricBank();
    showRubricBankModal.value = true;
};

const importRubric = (template) => {
    itemForm.rubric_json = [...template.criteria_json];
    showRubricBankModal.value = false;
};

const fetchQuizBank = async () => {
    try {
        const response = await axios.get(route('expert.quiz-bank.api'));
        quizBankTemplates.value = response.data;
    } catch (e) {
        console.error('Failed to fetch quiz bank', e);
    }
};

const openImportModal = (moduleId) => {
    activeModuleForImport.value = moduleId;
    fetchQuizBank();
    showQuizBankModal.value = true;
};

const importQuiz = (template) => {
    itemForm.id = null;
    itemForm.module_id = activeModuleForImport.value;
    itemForm.type = 'knowledge';
    itemForm.sub_type = template.sub_type;
    itemForm.title = template.title;
    itemForm.content = template.content || '';
    itemForm.assessment_mode = template.assessment_mode;
    itemForm.passing_grade = template.passing_grade;
    itemForm.is_capstone = false;
    itemForm.rubric_json = [];

    showQuizBankModal.value = false;
    showItemSlideOver.value = true;
};

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
const showKnowledgeOptions = ref(null); // ID of module with open knowledge sub-menu
const showItemSlideOver = ref(false); // Right slide-over for Knowledge
const activeSettingsTab = ref('General');
const showInternalBank = ref(false);
const quizBankTemplates = ref([]);

const inlineKnowledgeForms = ref({}); // { moduleId: { ...formData } }

const showAIPromptModal = ref(false);
const showAIPreviewModal = ref(false);
const aiPrompt = ref('');
const aiPreviewData = ref([]);
const isGeneratingLocal = ref(false);

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
    sub_type: null, // pre_test, quiz, final_exam
    content: '',
    assessment_mode: 'diagnostic',
    passing_grade: 75,
    is_capstone: false,
    rubric_json: [],
    
    // Quiz Configuration (Phase 3)
    time_limit: 0,
    max_attempts: 0,
    shuffle_questions: false,
    shuffle_options: false,
    
    selection_mode: 'manual', // manual, static, dynamic
    selection_tags: [],
    selection_tags_input: '',
    selection_difficulty: 'any', // any, easy, medium, hard
    selection_count: 10,
    
    linked_template_id: null,
    linked_template_title: '',
    
    weighting_mode: 'flat', // flat, custom
    feedback_reveal: 'instantly', // instantly, after_passing, never
    
    enable_difficulty_tracking: true,
    skill_tags: []
});

const isAddingModule = ref(false);
const inlineModuleTitle = ref('');

const inlineItemForms = ref({}); // { moduleId: { title: '', type: '' } }

// ─── Helpers ──────────────────────────────────────────────────────────────────
const ITEM_TYPES = {
    literal: { label: 'Literal Content', icon: FileText, color: 'text-blue-500', bg: 'bg-blue-50' },
    visual: { label: 'Visual Content', icon: Video, color: 'text-blue-500', bg: 'bg-blue-50' },
    knowledge: { label: 'Knowledge Bank', icon: HelpCircle, color: 'text-emerald-500', bg: 'bg-emerald-50' },
    exercise: { label: 'Mission Exercise', icon: Target, color: 'text-purple-600', bg: 'bg-purple-50' },
};

const getMeta = (type, sub_type = null) => {
    if (type === 'knowledge') {
        if (sub_type === 'pre_test') return { label: 'Pre-Test', icon: Activity, color: 'text-yellow-600', bg: 'bg-yellow-50' };
        if (sub_type === 'final_exam') return { label: 'Final Exam', icon: Award, color: 'text-amber-500', bg: 'bg-amber-50' };
        return { label: 'Module Quiz', icon: Target, color: 'text-emerald-500', bg: 'bg-emerald-50' };
    }
    return ITEM_TYPES[type] ?? ITEM_TYPES.literal;
};

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
        showModuleModal.value = true;
    } else {
        // Switch to inline creation for new modules
        isAddingModule.value = true;
        inlineModuleTitle.value = '';
    }
};

const cancelInlineModule = () => {
    isAddingModule.value = false;
    inlineModuleTitle.value = '';
};

const commitInlineModule = () => {
    if (!inlineModuleTitle.value.trim()) return;
    
    useForm({
        title: inlineModuleTitle.value,
        description: ''
    }).post(route('expert.modules.store', props.course.id), {
        onSuccess: () => {
            isAddingModule.value = false;
            inlineModuleTitle.value = '';
        }
    });
};

const openItemModal = (moduleId, type, item = null) => {
    isEditing.value = item && item.id ? true : false;
    
    // If creating a new literal or visual item, use inline flow
    if (!isEditing.value && (type === 'literal' || type === 'visual')) {
        inlineItemForms.value[moduleId] = {
            title: '',
            type: type,
            processing: false
        };
        activeDropdown.value = null;
        return;
    }

    itemForm.id = item?.id ?? null;
    itemForm.module_id = moduleId;
    itemForm.type = type;
    itemForm.sub_type = item?.sub_type ?? null;
    
    // Set smart defaults based on sub_type
    const defaultMode = item?.sub_type === 'final_exam' ? 'mastery' : (item?.sub_type === 'pre_test' ? 'diagnostic' : (item?.assessment_mode ?? 'diagnostic'));
    const defaultTitle = item?.title ?? (type === 'knowledge' ? getMeta(type, item?.sub_type).label : '');
    
    itemForm.title = defaultTitle;
    itemForm.content = item?.content ?? '';
    itemForm.assessment_mode = defaultMode;
    itemForm.passing_grade = item?.passing_grade ?? 75;
    itemForm.is_capstone = item?.is_capstone ?? false;
    itemForm.rubric_json = item?.rubric_json ?? [];

    // Reset Quiz Settings or Load from Content
    activeSettingsTab.value = 'Configuration';
    if (type === 'knowledge' && item?.content) {
        try {
            const data = typeof item.content === 'string' ? JSON.parse(item.content) : item.content;
            itemForm.time_limit = data.time_limit ?? 0;
            itemForm.max_attempts = data.max_attempts ?? 0;
            itemForm.shuffle_questions = !!data.shuffle_questions;
            itemForm.shuffle_options = !!data.shuffle_options;
            itemForm.selection_mode = data.selection_mode ?? 'manual';
            itemForm.selection_tags = data.selection_tags ?? [];
            itemForm.selection_difficulty = data.selection_difficulty ?? 'any';
            itemForm.selection_count = data.selection_count ?? 10;
            itemForm.linked_template_id = data.linked_template_id ?? null;
            itemForm.linked_template_title = data.linked_template_title ?? '';
            itemForm.weighting_mode = data.weighting_mode ?? 'flat';
            itemForm.feedback_reveal = data.feedback_reveal ?? 'instantly';
            itemForm.enable_difficulty_tracking = data.enable_difficulty_tracking !== false;
            itemForm.skill_tags = data.skill_tags ?? [];
            itemForm.competency_tags = data.competency_tags ?? [];
            
            // New Expert Engine Fields
            itemForm.weighted_scoring = !!data.weighted_scoring;
            itemForm.scoring_method = data.scoring_method ?? 'highest';
        } catch (e) {
            console.error("Failed to parse quiz settings", e);
        }
    } else if (type === 'knowledge') {
        itemForm.competency_tags = [];
        itemForm.weighted_scoring = false;
        itemForm.scoring_method = 'highest';
        
        // Defaults for new Knowledge
        itemForm.time_limit = 0;
        itemForm.max_attempts = 0;
        itemForm.shuffle_questions = false;
        itemForm.shuffle_options = false;
        itemForm.selection_mode = 'manual';
        itemForm.selection_tags = [];
        itemForm.selection_difficulty = 'any';
        itemForm.selection_count = 10;
        itemForm.weighting_mode = 'flat';
        itemForm.feedback_reveal = 'instantly';
        itemForm.enable_difficulty_tracking = true;
        itemForm.skill_tags = [];
    }

    if (type === 'exercise' && itemForm.rubric_json.length === 0) {
        itemForm.rubric_json = [{ label: 'Accuracy', points: 10 }];
    }

    activeDropdown.value = null;
    showKnowledgeOptions.value = null;

    if (type === 'knowledge') {
        showItemSlideOver.value = true;
    } else {
        showItemModal.value = true;
    }
};

const cancelInlineItem = (moduleId) => {
    delete inlineItemForms.value[moduleId];
};

const commitInlineItem = (moduleId) => {
    const form = inlineItemForms.value[moduleId];
    if (!form || !form.title.trim()) return;

    form.processing = true;
    useForm({
        module_id: moduleId,
        title: form.title,
        type: form.type,
        content: '',
        order: 0
    }).post(route('expert.items.store', props.course.id), {
        onSuccess: () => {
            delete inlineItemForms.value[moduleId];
        },
        onFinish: () => {
            if (inlineItemForms.value[moduleId]) {
                inlineItemForms.value[moduleId].processing = false;
            }
        }
    });
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
    showItemSlideOver.value = false;
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
    let contentStr = itemForm.content;
    
    // Pack Quiz Settings into content for Knowledge items
    if (itemForm.type === 'knowledge') {
        const settings = {
            time_limit: itemForm.time_limit,
            max_attempts: itemForm.max_attempts,
            shuffle_questions: itemForm.shuffle_questions,
            shuffle_options: itemForm.shuffle_options,
            selection_mode: itemForm.selection_mode,
            selection_tags: itemForm.selection_tags,
            selection_difficulty: itemForm.selection_difficulty,
            selection_count: itemForm.selection_count,
            linked_template_id: itemForm.linked_template_id,
            linked_template_title: itemForm.linked_template_title,
            weighting_mode: itemForm.weighting_mode,
            feedback_reveal: itemForm.feedback_reveal,
            enable_difficulty_tracking: itemForm.enable_difficulty_tracking,
            skill_tags: itemForm.skill_tags,
            competency_tags: itemForm.competency_tags,
            weighted_scoring: itemForm.weighted_scoring,
            scoring_method: itemForm.scoring_method
        };
        contentStr = JSON.stringify(settings);
    }

    const payload = {
        title: itemForm.title,
        type: itemForm.type,
        sub_type: itemForm.sub_type,
        content: contentStr,
        assessment_mode: itemForm.assessment_mode,
        passing_grade: itemForm.type === 'knowledge' && itemForm.assessment_mode === 'mastery' ? itemForm.passing_grade : null,
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

const saveInlineKnowledge = (moduleId) => {
    const form = inlineKnowledgeForms.value[moduleId];
    if (!form) return;

    const payload = {
        title: form.title,
        type: 'knowledge',
        content: JSON.stringify({
            question: form.question,
            options: form.options,
            answer: form.answer
        }),
        assessment_mode: form.assessment_mode,
        passing_grade: form.assessment_mode === 'mastery' ? form.passing_grade : null,
        is_capstone: false,
        rubric_json: null,
        module_id: moduleId,
        order: 0
    };

    if (form.id) {
        useForm(payload).patch(route('expert.items.update', form.id), {
            onSuccess: () => {
                delete inlineKnowledgeForms.value[moduleId];
            }
        });
    } else {
        useForm(payload).post(route('expert.items.store', props.course.id), {
            onSuccess: () => {
                delete inlineKnowledgeForms.value[moduleId];
            }
        });
    }
};

const cancelInlineKnowledge = (moduleId) => {
    delete inlineKnowledgeForms.value[moduleId];
};

const openAIPromptModal = () => {
    aiPrompt.value = '';
    showAIPromptModal.value = true;
};

const generateAIPreview = async () => {
    isGeneratingLocal.value = true;
    try {
        const response = await axios.post(route('expert.ai.generate-curriculum', props.course.id), {
            preview: true,
            prompt: aiPrompt.value
        });
        aiPreviewData.value = response.data;
        showAIPromptModal.value = false;
        showAIPreviewModal.value = true;
    } catch (error) {
        console.error('Failed to generate preview', error);
        const errorMsg = error.response?.data?.error || 'Failed to generate AI architecture preview. Please try again.';
        alert(`Error: ${errorMsg}`);
    } finally {
        isGeneratingLocal.value = false;
    }
};

const applyAIArchitecture = () => {
    isGeneratingLocal.value = true;
    router.post(route('expert.ai.generate-curriculum', props.course.id), {
        modules: aiPreviewData.value
    }, {
        onSuccess: () => {
            showAIPreviewModal.value = false;
            aiPreviewData.value = [];
            // Assuming Inertia handles the reload, we just need to stop spinning
        },
        onFinish: () => {
            isGeneratingLocal.value = false;
        }
    });
};
const selectTemplateForLink = (template) => {
    itemForm.linked_template_id = template.id;
    itemForm.linked_template_title = template.title;
    showInternalBank.value = false;
};

// Auto-fetch bank when Questions tab is active
watch(activeSettingsTab, (newTab) => {
    if (newTab === 'Questions' && quizBankTemplates.value.length === 0) {
        fetchQuizBank();
    }
});
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
                <button @click="openAIPromptModal"
                        :disabled="isGeneratingLocal"
                        class="bg-indigo-50 text-indigo-700 border border-indigo-100 px-6 py-3 rounded-2xl font-black text-xs flex items-center gap-3 hover:bg-indigo-100 transition-all shadow-sm active:scale-95 disabled:opacity-50">
                    <component :is="isGeneratingLocal ? Loader2 : Sparkles" :class="{ 'animate-spin': isGeneratingLocal }" class="w-4 h-4" />
                    {{ isGeneratingLocal ? 'Forging Architecture...' : 'AI Architect' }}
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
        <div class="space-y-6">
            <!-- Inline Module Adder (Top) -->
            <div v-if="isAddingModule" 
                 class="bg-indigo-50/50 rounded-[2.5rem] border-2 border-indigo-200 border-dashed p-8 animate-in slide-in-from-top-4 duration-500 mb-8">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                        <Plus class="w-6 h-6 text-indigo-600 animate-pulse" />
                    </div>
                    <div class="flex-1">
                        <input v-model="inlineModuleTitle" 
                               @keyup.enter="commitInlineModule"
                               @keyup.esc="cancelInlineModule"
                               type="text" 
                               placeholder="Enter Module Title (e.g. Master Class 01: Strategy)..."
                               class="w-full bg-transparent border-none text-xl font-black text-slate-900 placeholder:text-slate-300 focus:ring-0 p-0" />
                        <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mt-1">Press Enter to Commit • Esc to Cancel</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="cancelInlineModule" class="p-3 text-slate-400 hover:text-slate-600 transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                        <button @click="commitInlineModule" 
                                :disabled="!inlineModuleTitle.trim()"
                                class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-slate-900 transition-all disabled:opacity-50">
                            Commit Module
                        </button>
                    </div>
                </div>
            </div>

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
                                 class="absolute right-0 mt-3 w-80 bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-slate-100 z-50 p-4 animate-in fade-in zoom-in-95 overflow-hidden">
                                
                                <!-- Section: Reading / Visual -->
                                <div class="mb-4">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest px-4 mb-2">📖 Learning Content</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button @click="openItemModal(module.id, 'literal')" class="flex flex-col items-center gap-2 p-4 hover:bg-blue-50 rounded-2xl transition-all group border border-transparent hover:border-blue-100">
                                            <FileText class="w-5 h-5 text-slate-400 group-hover:text-blue-600" />
                                            <p class="text-[10px] font-black text-slate-900 leading-none">Text</p>
                                        </button>
                                        <button @click="openItemModal(module.id, 'visual')" class="flex flex-col items-center gap-2 p-4 hover:bg-blue-50 rounded-2xl transition-all group border border-transparent hover:border-blue-100">
                                            <Video class="w-5 h-5 text-slate-400 group-hover:text-blue-500" />
                                            <p class="text-[10px] font-black text-slate-900 leading-none">Video</p>
                                        </button>
                                    </div>
                                </div>

                                <!-- Section: Assessment -->
                                <div class="border-t border-slate-50 pt-4 mb-4">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest px-4 mb-2">🎯 Assessment</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button @click="openItemModal(module.id, 'knowledge', { sub_type: 'pre_test' })" class="flex flex-col items-center gap-2 p-4 hover:bg-yellow-50 rounded-2xl transition-all group border border-transparent hover:border-yellow-100">
                                            <Activity class="w-5 h-5 text-slate-400 group-hover:text-yellow-500" />
                                            <p class="text-[10px] font-black text-slate-900 leading-none">Pre-Test</p>
                                        </button>
                                        <button @click="openItemModal(module.id, 'knowledge', { sub_type: 'quiz' })" class="flex flex-col items-center gap-2 p-4 hover:bg-emerald-50 rounded-2xl transition-all group border border-transparent hover:border-emerald-100">
                                            <HelpCircle class="w-5 h-5 text-slate-400 group-hover:text-emerald-500" />
                                            <p class="text-[10px] font-black text-slate-900 leading-none">Quiz</p>
                                        </button>
                                        <button @click="openItemModal(module.id, 'knowledge', { sub_type: 'final_exam' })" class="flex flex-col items-center gap-2 p-4 hover:bg-amber-50 rounded-2xl transition-all group border border-transparent hover:border-amber-100">
                                            <Award class="w-5 h-5 text-slate-400 group-hover:text-amber-500" />
                                            <p class="text-[10px] font-black text-slate-900 leading-none">Final Exam</p>
                                        </button>
                                    </div>
                                </div>

                                <!-- Section: Practice -->
                                <div class="border-t border-slate-50 pt-4 mb-4">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest px-4 mb-2">🚀 Practice</p>
                                    <button @click="openItemModal(module.id, 'exercise')" class="w-full flex items-center gap-4 p-4 hover:bg-purple-50 rounded-2xl transition-all group border border-transparent hover:border-purple-100">
                                        <Target class="w-5 h-5 text-slate-400 group-hover:text-purple-600" />
                                        <div class="text-left">
                                            <p class="text-xs font-black text-slate-900">Mission Exercise</p>
                                            <p class="text-[9px] text-slate-400 font-bold">L3 Behavioral evidence collection</p>
                                        </div>
                                    </button>
                                </div>

                                <!-- Section: Import (Rubric only) -->
                                <div class="border-t border-slate-50 pt-4">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest px-4 mb-2">📚 Import from Library</p>
                                    <button @click="openRubricImportModal(); activeDropdown = null" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 rounded-2xl transition-all group">
                                        <div class="flex items-center gap-3">
                                            <Award class="w-4 h-4 text-purple-400" />
                                            <div class="text-left">
                                                <p class="text-xs font-black text-slate-900">Rubric Library</p>
                                                <p class="text-[9px] text-slate-400 font-bold">Import L3 grading standard</p>
                                            </div>
                                        </div>
                                        <ChevronRight class="w-3 h-3 text-slate-300 group-hover:translate-x-1 transition-transform" />
                                    </button>
                                </div>
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
                    
                    <div v-for="item in module.curriculum_items" :key="item.id">
                        <!-- Inline Edit Form -->
                        <div v-if="inlineKnowledgeForms[module.id] && inlineKnowledgeForms[module.id].id === item.id" 
                             class="bg-emerald-50/30 border-2 border-emerald-100 rounded-[2rem] p-6 mb-4 shadow-sm">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600">
                                    <HelpCircle class="w-6 h-6" />
                                </div>
                                <div class="flex-1">
                                    <input v-model="inlineKnowledgeForms[module.id].title" type="text" placeholder="Quiz Title"
                                           class="w-full bg-transparent border-none focus:ring-0 text-emerald-900 font-black text-lg p-0 placeholder:text-emerald-300" />
                                </div>
                            </div>
                            <div class="space-y-4 bg-white p-6 rounded-2xl border border-emerald-50 shadow-sm">
                                <!-- Assessment Mode Selector -->
                                <div class="grid grid-cols-2 gap-4 mb-2">
                                    <button @click="inlineKnowledgeForms[module.id].assessment_mode = 'diagnostic'"
                                            type="button"
                                            :class="inlineKnowledgeForms[module.id].assessment_mode === 'diagnostic' ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-100' : 'border-slate-100 bg-white'"
                                            class="p-4 rounded-2xl border-2 text-left transition-all group">
                                        <div class="flex items-center gap-3">
                                            <div :class="inlineKnowledgeForms[module.id].assessment_mode === 'diagnostic' ? 'bg-indigo-600' : 'bg-slate-100'" class="w-4 h-4 rounded-full border-4 border-white shadow-sm"></div>
                                            <p class="text-xs font-black text-slate-900">Diagnostic</p>
                                        </div>
                                        <p class="text-[9px] text-slate-400 font-bold mt-1 ml-7">Scores are recorded but do not block progress.</p>
                                    </button>
                                    <button @click="inlineKnowledgeForms[module.id].assessment_mode = 'mastery'"
                                            type="button"
                                            :class="inlineKnowledgeForms[module.id].assessment_mode === 'mastery' ? 'border-emerald-600 bg-emerald-50 ring-2 ring-emerald-100' : 'border-slate-100 bg-white'"
                                            class="p-4 rounded-2xl border-2 text-left transition-all group">
                                        <div class="flex items-center gap-3">
                                            <div :class="inlineKnowledgeForms[module.id].assessment_mode === 'mastery' ? 'bg-emerald-600' : 'bg-slate-100'" class="w-4 h-4 rounded-full border-4 border-white shadow-sm"></div>
                                            <p class="text-xs font-black text-slate-900">Mastery</p>
                                        </div>
                                        <p class="text-[9px] text-slate-400 font-bold mt-1 ml-7">Students must pass to unlock next contents.</p>
                                    </button>
                                </div>

                                <div v-if="inlineKnowledgeForms[module.id].assessment_mode === 'mastery'" class="animate-in slide-in-from-top-2 duration-300">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-2">Passing Grade (%)</label>
                                    <div class="flex items-center gap-4">
                                        <input v-model.number="inlineKnowledgeForms[module.id].passing_grade" type="range" min="0" max="100" step="5" class="flex-1 accent-emerald-500" />
                                        <div class="w-16 h-10 bg-emerald-50 rounded-xl flex items-center justify-center font-black text-emerald-700 text-sm border border-emerald-100">{{ inlineKnowledgeForms[module.id].passing_grade }}%</div>
                                    </div>
                                </div>

                                <div class="h-px bg-slate-50 my-2"></div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Question</label>
                                    <textarea v-model="inlineKnowledgeForms[module.id].question" rows="2" placeholder="What is the concept of..."
                                              class="w-full px-4 py-3 rounded-xl bg-slate-50 border-none focus:ring-2 focus:ring-emerald-100 font-medium text-slate-700"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div v-for="opt in ['A', 'B', 'C', 'D']" :key="opt">
                                        <label class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                            <input type="radio" :value="opt" v-model="inlineKnowledgeForms[module.id].answer" class="text-emerald-500 focus:ring-emerald-500" />
                                            Option {{ opt }}
                                        </label>
                                        <input v-model="inlineKnowledgeForms[module.id].options[opt]" type="text" :placeholder="`Option ${opt}`"
                                               class="w-full px-4 py-2 rounded-xl bg-slate-50 border-none focus:ring-2 focus:ring-emerald-100 font-medium text-slate-700 text-sm" />
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-4">
                                    <button @click="cancelInlineKnowledge(module.id)" class="px-4 py-2 text-slate-400 hover:bg-slate-100 rounded-xl font-bold text-xs transition-all">Cancel</button>
                                    <button @click="saveInlineKnowledge(module.id)" class="px-6 py-2 bg-emerald-500 text-white rounded-xl font-black text-xs shadow-md hover:bg-emerald-600 transition-all">Save Quiz</button>
                                </div>
                            </div>
                        </div>

                        <!-- Normal Item Display -->
                        <div v-else :class="[
                            'group flex items-center justify-between p-5 transition-all mb-4',
                            item.type === 'exercise' 
                                ? 'bg-purple-50/30 border-l-8 border-purple-600 rounded-r-[2rem] rounded-l-lg border-y border-r border-purple-100 shadow-sm' 
                                : 'bg-white border border-slate-50 hover:border-indigo-100 hover:shadow-xl hover:shadow-indigo-500/5 rounded-[2rem]'
                         ]">
                        
                        <div class="flex items-center gap-6">
                            <div :class="[
                                'w-12 h-12 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 shadow-sm', 
                                getMeta(item.type, item.sub_type).bg,
                                item.type === 'exercise' ? 'bg-white shadow-md ring-1 ring-black/5' : ''
                            ]">
                                <component :is="getMeta(item.type, item.sub_type).icon" :class="['w-6 h-6', getMeta(item.type, item.sub_type).color]" />
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <h4 :class="item.type === 'exercise' ? 'text-purple-900 font-black' : 'text-slate-900 font-bold'" class="text-sm tracking-tight">
                                        {{ item.title }}
                                    </h4>
                                    <span v-if="item.type === 'knowledge'" 
                                          :class="item.assessment_mode === 'mastery' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                          class="text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">
                                        {{ getMeta(item.type, item.sub_type).label }} &bull; {{ item.assessment_mode === 'mastery' ? 'Mastery' : 'Diagnostic' }}
                                    </span>
                                    <span v-if="item.is_capstone" 
                                          class="bg-indigo-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">
                                        Capstone
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ getMeta(item.type).label }}</span>
                                    <template v-if="item.type === 'exercise'">
                                        <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                        <span class="text-[9px] font-black text-purple-400 uppercase tracking-widest">{{ item.rubric_json?.length || 0 }} Rubric Criterias</span>
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

                    <!-- Inline Add Form -->
                    <div v-if="inlineKnowledgeForms[module.id] && inlineKnowledgeForms[module.id].id === null" 
                         class="bg-emerald-50/30 border-2 border-emerald-100 rounded-[2rem] p-6 shadow-sm mt-4 animate-in slide-in-from-top-2">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600">
                                <HelpCircle class="w-6 h-6" />
                            </div>
                            <div class="flex-1">
                                <input v-model="inlineKnowledgeForms[module.id].title" type="text" placeholder="Quiz Title"
                                       class="w-full bg-transparent border-none focus:ring-0 text-emerald-900 font-black text-lg p-0 placeholder:text-emerald-300" />
                            </div>
                        </div>
                        <div class="space-y-4 bg-white p-6 rounded-2xl border border-emerald-50 shadow-sm">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Question</label>
                                <textarea v-model="inlineKnowledgeForms[module.id].question" rows="2" placeholder="What is the concept of..."
                                          class="w-full px-4 py-3 rounded-xl bg-slate-50 border-none focus:ring-2 focus:ring-emerald-100 font-medium text-slate-700"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="opt in ['A', 'B', 'C', 'D']" :key="opt">
                                    <label class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                        <input type="radio" :value="opt" v-model="inlineKnowledgeForms[module.id].answer" class="text-emerald-500 focus:ring-emerald-500" />
                                        Option {{ opt }}
                                    </label>
                                    <input v-model="inlineKnowledgeForms[module.id].options[opt]" type="text" :placeholder="`Option ${opt}`"
                                           class="w-full px-4 py-2 rounded-xl bg-slate-50 border-none focus:ring-2 focus:ring-emerald-100 font-medium text-slate-700 text-sm" />
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-4">
                                <button @click="cancelInlineKnowledge(module.id)" class="px-4 py-2 text-slate-400 hover:bg-slate-100 rounded-xl font-bold text-xs transition-all">Cancel</button>
                                <button @click="saveInlineKnowledge(module.id)" class="px-6 py-2 bg-emerald-500 text-white rounded-xl font-black text-xs shadow-md hover:bg-emerald-600 transition-all">Save Quiz</button>
                            </div>
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

                    <!-- Assessment Mode Selector (Modal) -->
                    <div v-if="itemForm.type === 'knowledge'" class="space-y-4">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Assessment Mode</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button @click="itemForm.assessment_mode = 'diagnostic'"
                                    type="button"
                                    :class="itemForm.assessment_mode === 'diagnostic' ? 'border-indigo-600 bg-indigo-50 ring-4 ring-indigo-50' : 'border-slate-100 bg-white'"
                                    class="p-6 rounded-[2rem] border-2 text-left transition-all group">
                                <div class="flex items-center gap-4">
                                    <div :class="itemForm.assessment_mode === 'diagnostic' ? 'bg-indigo-600 shadow-lg shadow-indigo-200' : 'bg-slate-100'" class="w-5 h-5 rounded-full border-4 border-white transition-all"></div>
                                    <p class="text-sm font-black text-slate-900">Diagnostic (Pre-Test)</p>
                                </div>
                                <p class="text-[10px] text-slate-400 font-bold mt-2 ml-9">Scores are recorded but do not block progress.</p>
                            </button>
                            <button @click="itemForm.assessment_mode = 'mastery'"
                                    type="button"
                                    :class="itemForm.assessment_mode === 'mastery' ? 'border-emerald-600 bg-emerald-50 ring-4 ring-emerald-50' : 'border-slate-100 bg-white'"
                                    class="p-6 rounded-[2rem] border-2 text-left transition-all group">
                                <div class="flex items-center gap-4">
                                    <div :class="itemForm.assessment_mode === 'mastery' ? 'bg-emerald-600 shadow-lg shadow-emerald-200' : 'bg-slate-100'" class="w-5 h-5 rounded-full border-4 border-white transition-all"></div>
                                    <p class="text-sm font-black text-slate-900">Mastery (Post-Test)</p>
                                </div>
                                <p class="text-[10px] text-slate-400 font-bold mt-2 ml-9">Students must pass to unlock next contents.</p>
                            </button>
                        </div>

                        <div v-if="itemForm.assessment_mode === 'mastery'" class="animate-in slide-in-from-top-2 bg-emerald-50/50 p-8 rounded-[2rem] border border-emerald-100/50">
                            <label class="block text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-4">Passing Grade Requirement</label>
                            <div class="flex items-center gap-8">
                                <input v-model.number="itemForm.passing_grade" type="range" min="0" max="100" step="5" class="flex-1 h-3 bg-emerald-200 rounded-full appearance-none cursor-pointer accent-emerald-600" />
                                <div class="w-24 h-14 bg-white rounded-2xl flex items-center justify-center font-black text-emerald-600 text-xl shadow-xl shadow-emerald-100/50 border border-emerald-50">{{ itemForm.passing_grade }}%</div>
                            </div>
                        </div>
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
                                <div class="flex items-center gap-4">
                                    <button type="button" @click="openRubricImportModal()" class="flex items-center gap-1.5 text-[10px] font-black text-emerald-600 hover:text-slate-900 transition-colors uppercase tracking-widest bg-emerald-50 px-3 py-1.5 rounded-lg">
                                        <PlusCircle class="w-4 h-4" /> Import from Bank
                                    </button>
                                    <button type="button" @click="addRubricRow" class="flex items-center gap-1.5 text-[10px] font-black text-indigo-600 hover:text-slate-900 transition-colors uppercase tracking-widest bg-indigo-50 px-3 py-1.5 rounded-lg">
                                        <PlusCircle class="w-4 h-4" /> Add Criteria
                                    </button>
                                </div>
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
        <!-- Modal: AI Prompt -->
        <div v-if="showAIPromptModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-2xl z-[100] flex items-center justify-center p-6">
            <div class="bg-white w-full max-w-lg rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-500 border border-white">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-indigo-50/50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                            <Sparkles class="w-6 h-6 text-indigo-600" />
                        </div>
                        <div>
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">AI Architect</h4>
                            <p class="text-indigo-600 font-bold text-[10px] uppercase tracking-widest mt-1">Provide specific context or constraints</p>
                        </div>
                    </div>
                    <button @click="showAIPromptModal = false" class="w-12 h-12 hover:bg-white rounded-2xl flex items-center justify-center transition-all border border-transparent hover:border-slate-100">
                        <X class="w-6 h-6 text-slate-400" />
                    </button>
                </div>
                <div class="p-10 space-y-6">
                    <p class="text-sm font-medium text-slate-600">The AI will generate a complete learning structure including Modules, Video/Text contents, and Knowledge Quizzes based on your course description.</p>
                    
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Additional Instructions (Optional)</label>
                        <textarea v-model="aiPrompt" rows="4" placeholder="e.g. Focus on practical case studies, keep modules very short, include at least one quiz per module..."
                                      class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-medium text-slate-700 outline-none shadow-inner resize-none"></textarea>
                    </div>
                    
                    <button @click="generateAIPreview" :disabled="isGeneratingLocal" 
                            class="w-full py-5 bg-indigo-600 text-white rounded-[2rem] font-black text-sm shadow-xl hover:bg-slate-900 transition-all active:scale-95 flex items-center justify-center gap-3">
                        <component :is="isGeneratingLocal ? Loader2 : Sparkles" :class="{ 'animate-spin': isGeneratingLocal }" class="w-4 h-4" />
                        {{ isGeneratingLocal ? 'Analyzing & Structuring...' : 'Generate Preview' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: AI Preview -->
        <div v-if="showAIPreviewModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-2xl z-[100] flex items-center justify-center p-6 overflow-y-auto">
            <div class="bg-white w-full max-w-4xl rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-500 border border-white my-8 flex flex-col max-h-[90vh]">
                <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-indigo-50/50 flex-shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                            <Rocket class="w-6 h-6 text-indigo-600" />
                        </div>
                        <div>
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">Proposed Architecture</h4>
                            <p class="text-indigo-600 font-bold text-[10px] uppercase tracking-widest mt-1">Review generated curriculum before applying</p>
                        </div>
                    </div>
                    <button @click="showAIPreviewModal = false" class="w-12 h-12 hover:bg-white rounded-2xl flex items-center justify-center transition-all border border-transparent hover:border-slate-100">
                        <X class="w-6 h-6 text-slate-400" />
                    </button>
                </div>
                
                <div class="p-8 overflow-y-auto flex-1 bg-slate-50/30">
                    <div v-if="aiPreviewData.length === 0" class="text-center py-10 text-slate-500">
                        No modules were generated. Please try again with a different prompt.
                    </div>
                    
                    <div class="space-y-6">
                        <div v-for="(module, mIdx) in aiPreviewData" :key="mIdx" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center font-black text-slate-400 text-xs">
                                    {{ mIdx + 1 }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 tracking-tight">{{ module.title }}</h3>
                                    <p v-if="module.description" class="text-xs text-slate-500 mt-1">{{ module.description }}</p>
                                </div>
                            </div>
                            
                            <div class="ml-12 space-y-3">
                                <div v-for="(item, iIdx) in module.items" :key="iIdx" class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50/50 border border-slate-50">
                                    <div :class="['w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center shadow-sm', getMeta(item.type).bg]">
                                        <component :is="getMeta(item.type).icon" :class="['w-5 h-5', getMeta(item.type).color]" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm">{{ item.title }}</h4>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 mb-2">{{ getMeta(item.type).label }}</div>
                                        
                                        <!-- Preview details based on type -->
                                        <p v-if="item.type === 'literal'" class="text-xs text-slate-600 line-clamp-2">{{ item.content }}</p>
                                        <a v-if="item.type === 'visual'" :href="item.content" target="_blank" class="text-xs text-blue-500 hover:underline">{{ item.content }}</a>
                                        <div v-if="item.type === 'knowledge'" class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-100/50 mt-2">
                                            <p class="text-xs font-bold text-emerald-900 mb-2">{{ typeof item.content === 'string' ? JSON.parse(item.content).question : item.content?.question }}</p>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div v-for="(val, key) in (typeof item.content === 'string' ? JSON.parse(item.content).options : item.content?.options)" :key="key" 
                                                     class="text-[10px] p-2 rounded-lg"
                                                     :class="(typeof item.content === 'string' ? JSON.parse(item.content).answer : item.content?.answer) === key ? 'bg-emerald-200 text-emerald-900 font-bold' : 'bg-white text-slate-500 border border-slate-100'">
                                                    {{ key }}. {{ val }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-slate-50 bg-white flex justify-end gap-4 flex-shrink-0">
                        <button @click="showAIPreviewModal = false" class="px-6 py-4 rounded-[2rem] font-bold text-sm text-slate-500 hover:bg-slate-50 transition-all">
                            Discard
                        </button>
                        <button @click="applyAIArchitecture" :disabled="isGeneratingLocal" class="px-8 py-4 bg-indigo-600 text-white rounded-[2rem] font-black text-sm shadow-xl hover:bg-slate-900 transition-all active:scale-95 flex items-center gap-2">
                            <component :is="isGeneratingLocal ? Loader2 : Check" :class="{ 'animate-spin': isGeneratingLocal }" class="w-4 h-4" />
                            Approve & Build Curriculum
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Slide-over: Knowledge Panel -->
        <div v-show="showItemSlideOver" class="fixed inset-y-0 right-0 w-full max-w-xl bg-white shadow-[-20px_0_60px_rgba(0,0,0,0.1)] z-[100] border-l border-slate-50 flex flex-col animate-in slide-in-from-right duration-500">
            <!-- Header -->
            <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                <div class="flex items-center gap-6">
                    <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg', getMeta('knowledge', itemForm.sub_type).bg]">
                        <component :is="getMeta('knowledge', itemForm.sub_type).icon" :class="['w-8 h-8', getMeta('knowledge', itemForm.sub_type).color]" />
                    </div>
                    <div>
                        <h4 class="text-2xl font-black text-slate-900 tracking-tight">Configure {{ getMeta('knowledge', itemForm.sub_type).label }}</h4>
                        <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-1">Strategic assessment unit alignment</p>
                    </div>
                </div>
                <button @click="closeModals" class="w-12 h-12 hover:bg-slate-100 rounded-2xl flex items-center justify-center transition-all">
                    <X class="w-6 h-6 text-slate-400" />
                </button>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto flex flex-col">
                <!-- Inline Header Settings -->
                <div class="p-10 pb-4 space-y-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Quiz Title</label>
                    <input v-model="itemForm.title" type="text" placeholder="e.g. Assessment Phase 01"
                           class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-bold text-slate-700 outline-none shadow-inner" />
                </div>

                <!-- Strategic Tabs Navigation -->
                <div class="px-10 flex border-b border-slate-100 bg-white sticky top-0 z-10">
                    <button v-for="tab in ['Configuration', 'Scoring Logic', 'Custom Analytics']" :key="tab"
                            @click="activeSettingsTab = tab"
                            :class="activeSettingsTab === tab ? 'text-indigo-600 border-indigo-600' : 'text-slate-400 border-transparent hover:text-slate-600'"
                            class="px-6 py-4 text-[10px] font-black uppercase tracking-widest border-b-2 transition-all mr-2">
                        {{ tab }}
                    </button>
                </div>

                <div class="flex-1 p-10 space-y-10 animate-in fade-in duration-500" :key="activeSettingsTab">
                    
                    <!-- TAB 1: CONFIGURATION -->
                    <div v-if="activeSettingsTab === 'Configuration'" class="space-y-10">
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="itemForm.assessment_mode = 'diagnostic'" :class="itemForm.assessment_mode === 'diagnostic' ? 'bg-indigo-50 border-indigo-600 shadow-xl shadow-indigo-100/50' : 'bg-white border-slate-100'" class="p-6 rounded-[2rem] border-2 text-left transition-all">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm mb-4">
                                    <Eye class="w-5 h-5 text-indigo-600" />
                                </div>
                                <p class="text-xs font-black text-slate-900 leading-tight">Diagnostic</p>
                                <p class="text-[9px] text-slate-400 font-bold mt-1">Pre-test / Educational</p>
                            </button>
                            <button @click="itemForm.assessment_mode = 'mastery'" :class="itemForm.assessment_mode === 'mastery' ? 'bg-emerald-50 border-emerald-600 shadow-xl shadow-emerald-100/50' : 'bg-white border-slate-100'" class="p-6 rounded-[2rem] border-2 text-left transition-all">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm mb-4">
                                    <ShieldCheck class="w-5 h-5 text-emerald-600" />
                                </div>
                                <p class="text-xs font-black text-slate-900 leading-tight">Mastery</p>
                                <p class="text-[9px] text-slate-400 font-bold mt-1">Post-test / Gatekeeper</p>
                            </button>
                        </div>

                        <div v-if="itemForm.assessment_mode === 'mastery' || true" class="space-y-6 bg-slate-50 p-6 rounded-3xl border border-slate-100">
                            <div class="flex items-center justify-between px-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Passing Grade</label>
                                <span class="text-lg font-black" :class="itemForm.assessment_mode === 'mastery' ? 'text-emerald-600' : 'text-slate-600'">{{ itemForm.passing_grade }}%</span>
                            </div>
                            <input v-model.number="itemForm.passing_grade" type="range" min="0" max="100" step="5" class="w-full h-3 bg-slate-200 rounded-full appearance-none cursor-pointer shadow-inner" :class="itemForm.assessment_mode === 'mastery' ? 'accent-emerald-600' : 'accent-slate-500'" />
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Time Limit (Min)</label>
                                <input v-model.number="itemForm.time_limit" type="number" 
                                       class="w-full px-6 py-4 rounded-xl bg-white shadow-sm border border-slate-100 focus:ring-4 focus:ring-indigo-50 font-bold text-slate-700" />
                                <p class="text-[9px] text-slate-400 font-medium px-2">0 = Unlimited Duration</p>
                            </div>
                            <div class="space-y-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Max Attempts</label>
                                <input v-model.number="itemForm.max_attempts" type="number" 
                                       class="w-full px-6 py-4 rounded-xl bg-white shadow-sm border border-slate-100 focus:ring-4 focus:ring-indigo-50 font-bold text-slate-700" />
                                <p class="text-[9px] text-slate-400 font-medium px-2">0 = Unlimited Retakes</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 group">Security & Randomization</label>
                            <div class="grid grid-cols-1 gap-3">
                                <label class="flex items-center justify-between p-5 bg-white border border-slate-100 rounded-2xl cursor-pointer hover:border-slate-300 transition-all shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                                            <Shuffle class="w-5 h-5 text-indigo-500" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-900">Shuffle Questions</p>
                                            <p class="text-[9px] text-slate-400 font-bold">Varied order per participant</p>
                                        </div>
                                    </div>
                                    <input type="checkbox" v-model="itemForm.shuffle_questions" class="w-5 h-5 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500" />
                                </label>
                                <label class="flex items-center justify-between p-5 bg-white border border-slate-100 rounded-2xl cursor-pointer hover:border-slate-300 transition-all shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                                            <Layers class="w-5 h-5 text-blue-500" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-900">Shuffle Options</p>
                                            <p class="text-[9px] text-slate-400 font-bold">Randomize A/B/C/D choices</p>
                                        </div>
                                    </div>
                                    <input type="checkbox" v-model="itemForm.shuffle_options" class="w-5 h-5 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: SCORING LOGIC -->
                    <div v-if="activeSettingsTab === 'Scoring Logic'" class="space-y-10">
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 group">Execution Telemetry</label>
                            <label class="flex items-center justify-between p-6 bg-slate-900 text-white rounded-3xl cursor-pointer shadow-2xl group overflow-hidden relative border border-slate-800">
                                <div class="relative z-10 flex items-center gap-5">
                                    <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md">
                                        <Activity class="w-6 h-6 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-black tracking-tight">Enable Weighted Scoring</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">Assign custom points per question</p>
                                    </div>
                                </div>
                                <input type="checkbox" v-model="itemForm.weighted_scoring" class="w-6 h-6 rounded-lg border-white/20 bg-white/10 text-indigo-500 focus:ring-0 relative z-10" />
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Scoring Method</label>
                                <select v-model="itemForm.scoring_method" class="w-full px-5 py-4 rounded-xl bg-white shadow-sm border border-slate-100 focus:ring-4 focus:ring-indigo-50 font-bold text-slate-700 cursor-pointer">
                                    <option value="highest">Highest Score</option>
                                    <option value="first">First Attempt</option>
                                    <option value="average">Average Score</option>
                                </select>
                            </div>
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Answer Key Release</label>
                                <select v-model="itemForm.feedback_reveal" class="w-full px-5 py-4 rounded-xl bg-white shadow-sm border border-slate-100 focus:ring-4 focus:ring-indigo-50 font-bold text-slate-700 cursor-pointer">
                                    <option value="instantly">Instantly</option>
                                    <option value="after_passing">After Passing</option>
                                    <option value="never">Never</option>
                                </select>
                            </div>
                        </div>

                        <!-- Question Sources -->
                        <div class="space-y-4 pt-6 border-t border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Data Source</label>
                            
                            <!-- Import from Quiz Bank -->
                            <div v-if="!itemForm.linked_template_id && !showInternalBank"
                                 class="flex items-center justify-between p-5 bg-indigo-50/50 rounded-2xl border border-dashed border-indigo-100">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
                                        <Database class="w-5 h-5 text-indigo-400" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-700">Import from Quiz Bank</p>
                                        <p class="text-[9px] text-slate-400 font-bold">Pilih soal dari template</p>
                                    </div>
                                </div>
                                <button @click="showInternalBank = true" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition-all active:scale-95">
                                    Browse
                                </button>
                            </div>

                            <!-- Bank Browser (Internal) -->
                            <div v-if="showInternalBank" class="bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden animate-in slide-in-from-top-4">
                                <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-2">Quiz Bank Templates</span>
                                    <button @click="showInternalBank = false" class="text-[9px] font-black text-indigo-600 uppercase px-2">Cancel</button>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <div v-for="template in quizBankTemplates" :key="template.id"
                                         @click="selectTemplateForLink(template)"
                                         class="p-4 border-b border-slate-50 hover:bg-indigo-50 cursor-pointer transition-all flex items-center justify-between group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-indigo-100 transition-all">
                                                <Database class="w-4 h-4 text-slate-400 group-hover:text-indigo-600" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800">{{ template.title }}</p>
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-sm">
                                            <Plus class="w-4 h-4 text-white" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Linked Template Indicator -->
                            <div v-if="itemForm.linked_template_id && !showInternalBank"
                                 class="bg-emerald-50 border border-emerald-100 p-5 rounded-2xl flex items-center justify-between animate-in zoom-in-95">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-50">
                                        <Check class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Linked Template</p>
                                        <h5 class="text-sm font-black text-slate-900">{{ itemForm.linked_template_title }}</h5>
                                    </div>
                                </div>
                                <button @click="itemForm.linked_template_id = null; itemForm.linked_template_title = ''; showInternalBank = true"
                                        class="text-[10px] font-black text-slate-400 hover:text-red-400 transition-all uppercase tracking-widest">
                                    Change
                                </button>
                            </div>

                            <!-- Manual Entry -->
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex items-center gap-4">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 shadow-sm flex-shrink-0">
                                    <PenTool class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-700">Tulis Manual</p>
                                    <p class="text-[9px] text-slate-400 font-bold">Tambahkan dari halaman detail</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: CUSTOM ANALYTICS -->
                    <div v-if="activeSettingsTab === 'Custom Analytics'" class="space-y-10">
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Competency Tags</label>
                            <div class="p-6 bg-indigo-50/50 rounded-3xl border border-indigo-100">
                                <p class="text-[10px] font-bold text-slate-500 mb-4 px-2">Pilih tag untuk Radar Chart & evaluasi.</p>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <button v-for="tag in ['Technical Skill', 'Conceptual', 'Safety', 'Leadership', 'Problem Solving']" :key="tag"
                                            @click="itemForm.competency_tags.includes(tag) ? itemForm.competency_tags.splice(itemForm.competency_tags.indexOf(tag), 1) : itemForm.competency_tags.push(tag)"
                                            :class="itemForm.competency_tags.includes(tag) ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-500 border border-slate-200'"
                                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                                        {{ tag }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mockup Analytics Dashboard -->
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Visual Analytics Preview</label>
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Spider Web Radar Chart Mockup -->
                                <div class="bg-white rounded-3xl p-6 border border-slate-100 flex flex-col items-center shadow-sm">
                                    <h5 class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-6 w-full text-center">Competency Radar</h5>
                                    <div class="relative w-48 h-48 opacity-80">
                                        <svg viewBox="0 0 100 100" class="w-full h-full text-slate-200" fill="none" stroke="currentColor" stroke-width="0.5">
                                            <polygon points="50,5 95,28 95,72 50,95 5,72 5,28" />
                                            <polygon points="50,20 80,38 80,62 50,80 20,62 20,38" />
                                            <line x1="50" y1="5" x2="50" y2="95" />
                                            <line x1="5" y1="28" x2="95" y2="72" />
                                            <line x1="5" y1="72" x2="95" y2="28" />
                                        </svg>
                                        <svg viewBox="0 0 100 100" class="w-full h-full absolute inset-0 text-indigo-500/30" fill="currentColor" stroke="currentColor" stroke-width="2">
                                            <polygon points="50,20 85,35 60,65 50,85 25,65 20,30" />
                                            <circle cx="50" cy="20" r="2" fill="white" class="stroke-indigo-600" />
                                            <circle cx="85" cy="35" r="2" fill="white" class="stroke-indigo-600" />
                                        </svg>
                                    </div>
                                    <div class="flex justify-center gap-4 mt-6">
                                        <span class="flex items-center gap-1.5 text-[9px] font-bold text-slate-500"><div class="w-2 h-2 rounded bg-indigo-500"></div> Class Avg</span>
                                    </div>
                                </div>

                                <!-- Item Difficulty Index Mockup -->
                                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                                    <div class="flex justify-between items-center mb-4">
                                        <h5 class="text-[10px] uppercase font-black tracking-widest text-slate-400">Difficulty Index</h5>
                                        <span class="text-[9px] font-bold text-red-500 bg-red-50 px-2 py-1 rounded">Needs Review</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-700">Q4: "Implementasi middleware..."</p>
                                                <p class="text-[8px] text-slate-400 font-bold mt-0.5">Tag: Conceptual</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-black text-red-500">76%</p>
                                                <p class="text-[8px] text-slate-400 uppercase tracking-widest font-black">Salah</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-10 bg-slate-50 border-t border-slate-100">
                <button @click="handleSubmitItem" :disabled="itemForm.processing" 
                        class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all active:scale-95 disabled:opacity-50">
                    {{ isEditing ? 'Commit Changes' : 'Initialize Assessment' }}
                </button>
            </div>
        </div>
        <!-- Quiz Bank Selection Modal -->
        <div v-if="showQuizBankModal" class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showQuizBankModal = false"></div>
                
                <div class="relative w-full max-w-4xl bg-white rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                        <div class="flex justify-between items-center mb-10">
                            <div>
                                <h2 class="text-3xl font-black text-slate-900">Import from Quiz Bank</h2>
                                <p class="text-slate-400 mt-2 font-bold uppercase tracking-widest text-[10px]">Select a reusable template to add to this module</p>
                            </div>
                            <button @click="showQuizBankModal = false" class="p-4 hover:bg-slate-50 rounded-2xl transition-all">
                                <X class="w-6 h-6 text-slate-400" />
                            </button>
                        </div>

                        <div v-if="quizBankTemplates.length === 0" class="py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <Layout class="w-10 h-10 text-slate-200" />
                            </div>
                            <p class="text-slate-500 font-bold">Your Quiz Bank is empty.</p>
                            <a :href="route('expert.quiz-bank')" class="text-indigo-600 font-black mt-4 inline-block hover:underline">
                                Go to Quiz Bank to create templates &rarr;
                            </a>
                        </div>

                        <div v-else class="grid grid-cols-2 gap-6 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="template in quizBankTemplates" :key="template.id"
                                 @click="importQuiz(template)"
                                 class="p-6 border border-slate-100 rounded-3xl hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-50/50 transition-all cursor-pointer group">
                                <div class="flex items-center gap-4 mb-4">
                                    <div :class="[template.sub_type === 'pre_test' ? 'bg-yellow-50 text-yellow-500' : (template.sub_type === 'final_exam' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-500'), 'w-10 h-10 rounded-xl flex items-center justify-center']">
                                        <component :is="template.sub_type === 'pre_test' ? Activity : (template.sub_type === 'final_exam' ? Award : Target)" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-900 group-hover:text-indigo-600 transition-colors">{{ template.title }}</h4>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ template.sub_type.replace('_', ' ') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                    <span class="text-xs font-bold text-slate-400">{{ template.assessment_mode }}</span>
                                    <span class="text-xs font-black text-slate-900">{{ template.passing_grade }}% Pass</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rubric Bank Modal -->
            <div v-if="showRubricBankModal" class="fixed inset-0 z-[60] overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" @click="showRubricBankModal = false"></div>
                    
                    <div class="relative w-full max-w-3xl bg-white rounded-[3rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.2)] p-12 overflow-hidden animate-in fade-in zoom-in duration-300">
                        <div class="flex justify-between items-center mb-10">
                            <div>
                                <h2 class="text-3xl font-black text-slate-900">Import Rubric Standard</h2>
                                <p class="text-slate-400 mt-2 font-bold uppercase tracking-widest text-[10px]">Select a standardized grading matrix</p>
                            </div>
                            <button @click="showRubricBankModal = false" class="p-4 hover:bg-slate-50 rounded-2xl transition-all">
                                <X class="w-6 h-6 text-slate-400" />
                            </button>
                        </div>

                        <div v-if="rubricBankTemplates.length === 0" class="py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <CheckCircle2 class="w-10 h-10 text-slate-200" />
                            </div>
                            <p class="text-slate-500 font-bold">Your Rubric Library is empty.</p>
                            <a :href="route('expert.rubric-bank')" class="text-emerald-600 font-black mt-4 inline-block hover:underline">
                                Go to Rubric Library to create standards &rarr;
                            </a>
                        </div>

                        <div v-else class="grid grid-cols-2 gap-6 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="template in rubricBankTemplates" :key="template.id"
                                 @click="importRubric(template)"
                                 class="p-8 border border-slate-100 rounded-[2rem] hover:border-emerald-500 hover:shadow-2xl hover:shadow-emerald-100/50 transition-all cursor-pointer group">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <CheckCircle2 class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-900 group-hover:text-emerald-600 transition-colors">{{ template.name }}</h4>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ template.criteria_json.length }} Criteria</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="(c, ci) in template.criteria_json.slice(0, 2)" :key="ci" class="flex justify-between text-[10px] font-bold">
                                        <span class="text-slate-400 uppercase">{{ c.label }}</span>
                                        <span class="text-slate-900">{{ c.points }} pts</span>
                                    </div>
                                </div>
                                <div class="pt-6 mt-6 border-t border-slate-50 flex justify-between items-center">
                                    <span class="text-xs font-black text-slate-900">Total: {{ template.criteria_json.reduce((s, c) => s + (c.points || 0), 0) }} Pts</span>
                                    <ArrowRight class="w-4 h-4 text-slate-200 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" />
                                </div>
                            </div>
                        </div>
                    </div>
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
