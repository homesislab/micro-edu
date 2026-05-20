<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import draggable from 'vuedraggable';
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
    ShieldCheck,
    Settings,
    Box,
    MousePointer2,
    PanelLeft,
    PanelRight,
    Search,
    MonitorPlay,
    Zap
} from 'lucide-vue-next';

const props = defineProps({
    course: { type: Object, required: true },
    isGenerating: { type: Boolean, default: false }
});

const emit = defineEmits(['add-module', 'edit-module', 'delete-module', 'add-item', 'edit-item', 'delete-item', 'generate-ai']);

// ─── Workspace State ─────────────────────────────────────────────────────────

// Content extraction helper (must be defined before normalizeModules)
const extractContentBody = (content) => {
    if (!content) return '';
    if (typeof content === 'string') return content;
    if (typeof content === 'object') {
        if (content.body) return content.body;
        if (content.time_limit !== undefined || content.inline_questions !== undefined) return '';
        return JSON.stringify(content, null, 2);
    }
    return String(content);
};

// Store original content objects to preserve quiz settings
const originalContentMap = ref({});

// Normalize modules: convert item.content objects to display strings
const normalizeModules = (rawModules, existingModules = []) => {
    return rawModules?.map(m => {
        const existing = existingModules.find(ex => ex.id === m.id);
        return {
            ...m,
            expanded: existing ? existing.expanded : true,
            curriculum_items: (m.curriculum_items || []).map(item => {
                // Preserve original object in map for quiz settings extraction
                if (item.content && typeof item.content === 'object') {
                    originalContentMap.value[item.id] = item.content;
                }
                return {
                    ...item,
                    _contentStr: extractContentBody(item.content),
                };
            })
        };
    }) || [];
};

const modules = ref(normalizeModules(props.course.modules));
const activeModuleId = ref(null);
const activeItemId = ref(null);
const activeInspectorType = ref('course'); // 'course' | 'module' | 'item'
const sidebarOpen = ref(true);
const inspectorOpen = ref(true);
const isNavigating = ref(false);
const isLoading = ref(true);
const activeAddMenu = ref(null); // module id with open add-item dropdown

// Simulate initial load skeleton
setTimeout(() => { isLoading.value = false; }, 600);

const selectedModule = computed(() => modules.value.find(m => m.id === activeModuleId.value));
const selectedItem = computed(() => {
    if (!selectedModule.value) return null;
    return selectedModule.value.curriculum_items?.find(i => i.id === activeItemId.value);
});

// Scroll handling
const workspaceRef = ref(null);
const itemRefs = ref({});

// ─── Drag & Drop Handlers ────────────────────────────────────────────────────
const isDraggingModule = ref(false);
const isDraggingItem = ref(false);

const onModuleDragEnd = async () => {
    isDraggingModule.value = false;
    // Persist module order optimistically
    const ordered = modules.value.map((m, i) => ({ id: m.id, order: i }));
    try {
        await axios.post(route('expert.modules.reorder', props.course.id), { modules: ordered });
    } catch (e) {
        console.warn('Module reorder failed, refreshing...', e);
        router.reload({ only: ['course'] });
    }
};

const onItemDragEnd = async (moduleId) => {
    isDraggingItem.value = false;
    const mod = modules.value.find(m => m.id === moduleId);
    if (!mod) return;
    const ordered = mod.curriculum_items.map((item, i) => ({ id: item.id, order: i }));
    try {
        await axios.post(route('expert.items.reorder', moduleId), { items: ordered });
    } catch (e) {
        console.warn('Item reorder failed, refreshing...', e);
        router.reload({ only: ['course'] });
    }
};

// ─── Forms ──────────────────────────────────────────────────────────────────
const moduleForm = useForm({
    id: null,
    title: '',
    description: ''
});

const itemForm = useForm({
    id: null,
    module_id: null,
    title: '',
    type: 'literal',
    sub_type: null,
    content: '',
    assessment_mode: 'diagnostic',
    passing_grade: 75,
    is_capstone: false,
    rubric_json: [],
    time_limit: 0,
    max_attempts: 0,
    shuffle_questions: false,
    shuffle_options: false,
    selection_mode: 'manual',
    selection_tags: [],
    selection_difficulty: 'any',
    selection_count: 10,
    linked_template_id: null,
    linked_template_title: '',
    weighting_mode: 'flat',
    feedback_reveal: 'instantly',
    enable_difficulty_tracking: true,
    skill_tags: [],
    competency_tags: [],
    weighted_scoring: false,
    scoring_method: 'highest',
    inline_questions: [],
});

const courseSettingsForm = useForm({
    attendance_code: props.course.attendance_code ?? '',
    zoom_link: props.course.zoom_link ?? '',
    event_time: props.course.event_time ? new Date(props.course.event_time).toISOString().slice(0, 16) : '',
    passing_grade: props.course.passing_grade ?? 70,
    fast_track_threshold: props.course.fast_track_threshold ?? 90,
});

// ─── Navigation & Selection ─────────────────────────────────────────────────
const selectCourse = () => {
    activeInspectorType.value = 'course';
    activeModuleId.value = null;
    activeItemId.value = null;
};

const selectModule = (module) => {
    activeInspectorType.value = 'module';
    activeModuleId.value = module.id;
    activeItemId.value = null;
    
    moduleForm.id = module.id;
    moduleForm.title = module.title;
    moduleForm.description = module.description || '';
};

const selectItem = (item, moduleId) => {
    isNavigating.value = true;
    activeInspectorType.value = 'item';
    activeModuleId.value = moduleId;
    activeItemId.value = item.id;
    
    // Scroll to item
    setTimeout(() => {
        const el = itemRefs.value[item.id];
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        isNavigating.value = false;
    }, 100);
    
    // Fill itemForm for the inspector
    itemForm.id = item.id;
    itemForm.module_id = moduleId;
    itemForm.title = item.title;
    itemForm.type = item.type;
    itemForm.sub_type = item.sub_type;
    itemForm.content = item._contentStr ?? extractContentBody(item.content);
    itemForm.assessment_mode = item.assessment_mode;
    itemForm.passing_grade = item.passing_grade ?? 75;
    itemForm.is_capstone = !!item.is_capstone;
    itemForm.rubric_json = item.rubric_json || [];

    if (item.type === 'knowledge' && item.content) {
        try {
            const orig = originalContentMap.value[item.id];
            const data = orig ?? (typeof item.content === 'string' ? JSON.parse(item.content) : item.content) ?? {};
            itemForm.time_limit = data.time_limit ?? 0;
            itemForm.max_attempts = data.max_attempts ?? 0;
            itemForm.shuffle_questions = !!data.shuffle_questions;
            itemForm.shuffle_options = !!data.shuffle_options;
            itemForm.selection_mode = data.selection_mode ?? 'manual';
            itemForm.selection_tags = data.selection_tags ?? [];
            itemForm.selection_count = data.selection_count ?? 10;
            itemForm.weighted_scoring = !!data.weighted_scoring;
            itemForm.scoring_method = data.scoring_method ?? 'highest';
            itemForm.feedback_reveal = data.feedback_reveal ?? 'instantly';
            itemForm.competency_tags = data.competency_tags ?? ['Analytical Reasoning', 'Technical Architecture'];
            itemForm.inline_questions = data.inline_questions ?? [];
        } catch (e) {
            console.error("Failed to parse quiz settings", e);
        }
    }
};

// ─── Handlers ────────────────────────────────────────────────────────────────
const handleSaveCourse = () => {
    courseSettingsForm.patch(route('expert.courses.settings', props.course.id), {
        preserveScroll: true
    });
};

const handleSaveModule = () => {
    moduleForm.patch(route('expert.modules.update', moduleForm.id), {
        preserveScroll: true
    });
};

const handleSaveItem = () => {
    let contentStr = itemForm.content;
    if (itemForm.type === 'knowledge') {
        const settings = {
            time_limit: itemForm.time_limit,
            max_attempts: itemForm.max_attempts,
            shuffle_questions: itemForm.shuffle_questions,
            shuffle_options: itemForm.shuffle_options,
            selection_mode: itemForm.selection_mode,
            selection_tags: itemForm.selection_tags,
            selection_count: itemForm.selection_count,
            weighted_scoring: itemForm.weighted_scoring,
            scoring_method: itemForm.scoring_method,
            feedback_reveal: itemForm.feedback_reveal,
            competency_tags: itemForm.competency_tags,
            inline_questions: itemForm.inline_questions
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

    itemForm.transform(() => payload).patch(route('expert.items.update', itemForm.id), {
        preserveScroll: true
    });
};

const handleAddModule = () => {
    useForm({
        title: 'New Module',
        description: ''
    }).post(route('expert.modules.store', props.course.id));
};

const handleAddItem = (moduleId, type, sub_type = null) => {
    activeAddMenu.value = null;
    useForm({
        module_id: moduleId,
        title: sub_type ? `${sub_type.replace('_', ' ').toUpperCase()}` : `New ${type}`,
        type: type,
        sub_type: sub_type,
        content: '',
        order: 0
    }).post(route('expert.items.store', props.course.id));
};

const handleDeleteModule = (id) => {
    if (confirm('Hapus module ini? Semua unit di dalamnya juga akan dihapus.')) {
        useForm({}).delete(route('expert.modules.delete', id));
    }
};

const handleDeleteItem = (id) => {
    if (confirm('Hapus unit ini?')) {
        useForm({}).delete(route('expert.items.delete', id));
    }
};

// Close add menu on outside click
const closeAddMenu = () => { activeAddMenu.value = null; };

// ─── Link Asset Modal ────────────────────────────────────────────────────────
const showLinkAssetModal = ref(false);
const linkAssetItemId = ref(null);
const linkAssetType = ref('url'); // 'url' or 'file'
const linkAssetUrl = ref('');
const linkAssetFile = ref(null);

const openLinkAsset = (itemId) => {
    linkAssetItemId.value = itemId;
    linkAssetUrl.value = '';
    linkAssetFile.value = null;
    linkAssetType.value = 'url';
    showLinkAssetModal.value = true;
};

const handleLinkAssetFile = (e) => {
    linkAssetFile.value = e.target.files[0];
};

const applyLinkAsset = () => {
    if (linkAssetType.value === 'url' && linkAssetUrl.value) {
        // Insert URL into the active textarea content
        itemForm.content = (itemForm.content ? itemForm.content + '\n' : '') + linkAssetUrl.value;
        // Also update the item's display string
        const mod = modules.value.find(m => m.id === activeModuleId.value);
        if (mod) {
            const item = mod.curriculum_items.find(i => i.id === linkAssetItemId.value);
            if (item) item._contentStr = itemForm.content;
        }
    } else if (linkAssetType.value === 'file' && linkAssetFile.value) {
        // Upload file via FormData
        const fd = new FormData();
        fd.append('file', linkAssetFile.value);
        fd.append('item_id', linkAssetItemId.value);
        axios.post(route('expert.items.upload-asset', props.course.id), fd)
            .then(res => {
                const url = res.data.url;
                itemForm.content = (itemForm.content ? itemForm.content + '\n' : '') + url;
                const mod = modules.value.find(m => m.id === activeModuleId.value);
                if (mod) {
                    const item = mod.curriculum_items.find(i => i.id === linkAssetItemId.value);
                    if (item) item._contentStr = itemForm.content;
                }
            })
            .catch(err => console.error('Upload failed:', err));
    }
    showLinkAssetModal.value = false;
};

// ─── Helpers ──────────────────────────────────────────────────────────────────
const ITEM_TYPES = {
    literal: { label: 'Literal', icon: FileText, color: 'text-blue-500', bg: 'bg-blue-50' },
    visual: { label: 'Visual', icon: Video, color: 'text-purple-500', bg: 'bg-purple-50' },
    knowledge: { label: 'Knowledge', icon: HelpCircle, color: 'text-emerald-500', bg: 'bg-emerald-50' },
    exercise: { label: 'Exercise', icon: Target, color: 'text-orange-500', bg: 'bg-orange-50' },
};

const getMeta = (type, sub_type = null) => {
    if (type === 'knowledge') {
        if (sub_type === 'pre_test') return { label: 'Pre-Test', icon: Activity, color: 'text-yellow-600', bg: 'bg-yellow-50' };
        if (sub_type === 'final_exam') return { label: 'Final Exam', icon: Award, color: 'text-amber-500', bg: 'bg-amber-50' };
        return { label: 'Quiz', icon: HelpCircle, color: 'text-emerald-500', bg: 'bg-emerald-100' };
    }
    return ITEM_TYPES[type] ?? ITEM_TYPES.literal;
};

// Sync local modules ref with props when course updates
watch(() => props.course.modules, (newModules) => {
    modules.value = normalizeModules(newModules, modules.value);
}, { deep: true });

// ─── AI Architect Modal ───────────────────────────────────────────────────────
const showAIModal = ref(false);
const aiPromptText = ref('');
const isAILoading = ref(false);

const openAIPromptModal = () => {
    aiPromptText.value = '';
    showAIModal.value = true;
};

const submitAIArchitect = () => {
    if (!aiPromptText.value.trim()) {
        emit('generate-ai');
        showAIModal.value = false;
        return;
    }
    isAILoading.value = true;
    router.post(route('expert.ai.generate-curriculum', props.course.id), 
        { preview: false, prompt: aiPromptText.value },
        {
            preserveScroll: true,
            onFinish: () => {
                isAILoading.value = false;
                showAIModal.value = false;
                aiPromptText.value = '';
            }
        }
    );
};

// ─── Deploy (Publish) ─────────────────────────────────────────────────────────
const deployForm = useForm({});
const deployStatus = ref(props.course.status ?? 'draft');

const handleDeploy = () => {
    const nextStatus = deployStatus.value === 'published' ? 'draft' : 'published';
    const label = nextStatus === 'published' ? 'publish' : 'unpublish';
    if (!confirm(`Are you sure you want to ${label} this program?`)) return;
    router.patch(
        route('expert.courses.status.update', props.course.id),
        { status: nextStatus },
        {
            preserveScroll: true,
            onSuccess: () => { deployStatus.value = nextStatus; }
        }
    );
};

</script>

<template>
    <div class="h-screen flex flex-col bg-[#0F1115] text-slate-300 overflow-hidden font-sans">
        
        <!-- ══ IDE Header ══ -->
        <header class="h-14 border-b border-white/5 flex items-center justify-between px-4 bg-[#161920] flex-shrink-0">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-600/10 rounded-lg border border-indigo-500/20 text-indigo-400">
                    <Box class="w-4 h-4" />
                    <span class="text-xs font-black uppercase tracking-widest">Architect Studio</span>
                </div>
                <div class="h-4 w-px bg-white/10"></div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500">Program:</span>
                    <span class="text-xs font-black text-slate-200">{{ props.course?.title }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button @click="openAIPromptModal" 
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-600/20">
                    <Sparkles class="w-3.5 h-3.5" />
                    AI Architect
                </button>
                <div class="h-4 w-px bg-white/10 mx-2"></div>
                <button class="p-2 text-slate-500 hover:text-white transition-colors" @click="sidebarOpen = !sidebarOpen">
                    <PanelLeft class="w-4 h-4" :class="{'text-indigo-400': sidebarOpen}" />
                </button>
                <button class="p-2 text-slate-500 hover:text-white transition-colors" @click="inspectorOpen = !inspectorOpen">
                    <PanelRight class="w-4 h-4" :class="{'text-indigo-400': inspectorOpen}" />
                </button>
                <button @click="handleDeploy"
                        :class="deployStatus === 'published' 
                            ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30 hover:bg-amber-500/30' 
                            : 'bg-emerald-600 text-white hover:bg-emerald-500 shadow-lg'"
                        class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">
                    <Zap class="w-3.5 h-3.5" />
                    {{ deployStatus === 'published' ? 'Unpublish' : 'Deploy' }}
                </button>
            </div>
        </header>

        <!-- ══ Main Studio Layout ══ -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- ── PANE 1: OUTLINE (Sidebar) ── -->
            <aside v-show="sidebarOpen" class="w-72 border-r border-white/5 bg-[#161920] flex flex-col flex-shrink-0" @click.self="closeAddMenu">
                <div class="p-4 border-b border-white/5 flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Curriculum Outline</span>
                    <button @click="handleAddModule" title="Add Module"
                            class="p-1.5 hover:bg-indigo-600/20 rounded-lg text-indigo-400 transition-all">
                        <Plus class="w-4 h-4" />
                    </button>
                </div>

                <!-- Skeleton Loading -->
                <div v-if="isLoading" class="flex-1 p-2 space-y-2 animate-pulse">
                    <div class="h-8 bg-white/5 rounded-lg"></div>
                    <div class="h-2"></div>
                    <div v-for="i in 4" :key="i" class="space-y-1">
                        <div class="h-8 bg-white/5 rounded-lg"></div>
                        <div class="ml-4 space-y-1">
                            <div class="h-6 bg-white/3 rounded-lg w-5/6"></div>
                            <div class="h-6 bg-white/3 rounded-lg w-4/6"></div>
                        </div>
                    </div>
                </div>

                <div v-else class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1" @click="closeAddMenu">
                    <!-- Global Settings Node -->
                    <div @click.stop="selectCourse"
                         :class="activeInspectorType === 'course' ? 'bg-indigo-600/10 text-indigo-400 border-indigo-500/20' : 'hover:bg-white/5 border-transparent'"
                         class="flex items-center gap-3 px-3 py-2 rounded-lg cursor-pointer border transition-all">
                        <Settings class="w-4 h-4" />
                        <span class="text-xs font-black">Global Configuration</span>
                    </div>

                    <div class="h-2"></div>

                    <!-- ─ Draggable Modules ─ -->
                    <draggable
                        v-model="modules"
                        item-key="id"
                        handle=".drag-handle-module"
                        ghost-class="drag-ghost"
                        chosen-class="drag-chosen"
                        animation="180"
                        @start="isDraggingModule = true"
                        @end="onModuleDragEnd"
                        class="space-y-1"
                    >
                        <template #item="{ element: module }">
                            <div class="space-y-1">
                                <!-- Module Row -->
                                <div class="group flex items-center justify-between px-2 py-2 rounded-lg cursor-pointer hover:bg-white/5 transition-all"
                                     :class="activeModuleId === module.id && activeInspectorType === 'module' ? 'bg-white/5 text-white' : ''"
                                     @click.stop="selectModule(module)">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <!-- Drag Handle -->
                                        <GripVertical class="drag-handle-module w-3.5 h-3.5 text-slate-700 cursor-grab active:cursor-grabbing hover:text-slate-400 flex-shrink-0 transition-colors" />
                                        <ChevronRight class="w-3 h-3 text-slate-600 transition-transform flex-shrink-0"
                                                      :class="{'rotate-90': module.expanded}"
                                                      @click.stop="module.expanded = !module.expanded" />
                                        <span class="text-xs font-bold truncate">{{ module.title }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0 relative">
                                        <!-- Add Unit Button + Dropdown -->
                                        <button @click.stop="activeAddMenu = activeAddMenu === module.id ? null : module.id"
                                                class="p-1 hover:bg-white/10 rounded text-slate-500 hover:text-indigo-400 transition-all">
                                            <Plus class="w-3 h-3" />
                                        </button>
                                        <button @click.stop="handleDeleteModule(module.id)"
                                                class="p-1 hover:bg-rose-500/10 rounded text-slate-600 hover:text-rose-500 transition-all">
                                            <Trash2 class="w-3 h-3" />
                                        </button>
                                        <!-- Add Item Dropdown -->
                                        <div v-if="activeAddMenu === module.id"
                                             class="absolute right-0 top-7 w-44 bg-[#1E2130] border border-white/10 rounded-xl shadow-2xl z-50 overflow-hidden">
                                            <p class="px-3 pt-2.5 pb-1 text-[9px] font-black uppercase tracking-widest text-slate-600">Add Unit Type</p>
                                            <button v-for="t in [{type:'literal',label:'Literal (Text)',icon:FileText},{type:'visual',label:'Visual (Video)',icon:Video},{type:'knowledge',label:'Quiz',icon:HelpCircle},{type:'exercise',label:'Exercise',icon:Target}]"
                                                    :key="t.type"
                                                    @click.stop="handleAddItem(module.id, t.type)"
                                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-[11px] font-bold text-slate-400 hover:bg-white/5 hover:text-white transition-all">
                                                <component :is="t.icon" class="w-3.5 h-3.5" />
                                                {{ t.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Draggable Items under Module -->
                                <div v-show="module.expanded" class="ml-4 border-l border-white/5">
                                    <draggable
                                        v-model="module.curriculum_items"
                                        item-key="id"
                                        handle=".drag-handle-item"
                                        ghost-class="drag-ghost"
                                        chosen-class="drag-chosen"
                                        animation="160"
                                        group="items"
                                        @start="isDraggingItem = true"
                                        @end="onItemDragEnd(module.id)"
                                        class="space-y-0.5 min-h-[4px]"
                                    >
                                        <template #item="{ element: item }">
                                            <div @click.stop="selectItem(item, module.id)"
                                                 :class="activeItemId === item.id ? 'bg-indigo-600/10 text-white border-indigo-500/20' : 'text-slate-500 hover:text-slate-300 border-transparent'"
                                                 class="group flex items-center gap-2 px-2 py-1.5 rounded-lg border cursor-pointer transition-all mx-1">
                                                <GripVertical class="drag-handle-item w-3 h-3 text-slate-700 cursor-grab active:cursor-grabbing hover:text-slate-500 flex-shrink-0 transition-colors" />
                                                <component :is="getMeta(item.type, item.sub_type).icon"
                                                           class="w-3.5 h-3.5 flex-shrink-0"
                                                           :class="activeItemId === item.id ? 'text-indigo-400' : 'text-slate-600'" />
                                                <span class="text-[11px] truncate flex-1">{{ item.title }}</span>
                                                <button @click.stop="handleDeleteItem(item.id)"
                                                        class="opacity-0 group-hover:opacity-100 p-0.5 hover:text-rose-500 transition-all flex-shrink-0">
                                                    <X class="w-3 h-3" />
                                                </button>
                                            </div>
                                        </template>
                                    </draggable>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>
            </aside>

            <!-- ── PANE 2: WORKSPACE (Central) ── -->
            <main class="flex-1 bg-[#0F1115] relative flex flex-col min-w-0">
                <!-- Workspace Tab Bar -->
                <div class="h-10 border-b border-white/5 flex items-center px-4 gap-4 bg-[#161920]/50">
                   <div v-if="activeInspectorType === 'item' && selectedItem" class="flex items-center gap-2">
                       <component :is="getMeta(selectedItem.type, selectedItem.sub_type).icon" class="w-3.5 h-3.5 text-indigo-400" />
                       <span class="text-[10px] font-black uppercase text-slate-200">{{ selectedItem.title }}</span>
                   </div>
                   <div v-else-if="activeInspectorType === 'module' && selectedModule" class="flex items-center gap-2">
                       <Layers class="w-3.5 h-3.5 text-emerald-400" />
                       <span class="text-[10px] font-black uppercase text-slate-200">{{ selectedModule.title }}</span>
                   </div>
                   <div v-else class="flex items-center gap-2">
                       <Settings class="w-3.5 h-3.5 text-slate-400" />
                       <span class="text-[10px] font-black uppercase text-slate-200">Global Settings</span>
                   </div>
                </div>

                <!-- Unified Workspace Content -->
                <div ref="workspaceRef" class="flex-1 overflow-y-auto custom-scrollbar p-12 scroll-smooth">
                    <div class="max-w-4xl mx-auto space-y-32">
                        
                        <!-- Content Editor for Items (Infinite Scroll Mode) -->
                        <div v-if="(activeInspectorType === 'item' || activeInspectorType === 'module') && selectedModule" class="space-y-40">
                            <div v-for="item in selectedModule.curriculum_items" :key="item.id" 
                                 :ref="el => itemRefs[item.id] = el"
                                 class="space-y-8 min-h-[40vh] border-b border-white/5 pb-40 last:border-0"
                                 @mouseenter="activeItemId = item.id; selectItem(item, selectedModule.id)">
                                
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-3xl font-black text-white tracking-tight">{{ item.title }}</h2>
                                        <div class="flex items-center gap-2">
                                            <component :is="getMeta(item.type, item.sub_type).icon" class="w-4 h-4 text-slate-500" />
                                            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">{{ item.type }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 text-[9px] font-black uppercase tracking-widest border border-indigo-500/20">
                                            {{ getMeta(item.type, item.sub_type).label }}
                                        </span>
                                        <span v-if="item.is_capstone" class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500 text-[9px] font-black uppercase tracking-widest border border-amber-500/20">
                                            Capstone Mission
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-4 relative group">
                                    <!-- Dynamic Floating Overlay for selected item -->
                                    <div v-if="activeItemId === item.id" class="absolute -left-8 top-0 bottom-0 w-1 bg-indigo-600 rounded-full animate-in fade-in slide-in-from-left-1 duration-300"></div>

                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-600">Content Narrative</label>
                                    <div class="bg-[#161920] rounded-2xl border border-white/10 overflow-hidden focus-within:border-indigo-500/50 transition-all shadow-2xl">
                                        <!-- Active item: editable, bound to itemForm -->
                                        <textarea v-if="activeItemId === item.id" v-model="itemForm.content" rows="12" 
                                                  placeholder="Enter markdown, video URL, or quiz logic..."
                                                  class="w-full bg-transparent border-none focus:ring-0 p-8 text-slate-300 font-medium leading-relaxed resize-none"></textarea>
                                        <!-- Inactive items: read-only, pre-normalized string -->
                                        <textarea v-else :value="item._contentStr" rows="12" readonly
                                                  class="w-full bg-transparent border-none focus:ring-0 p-8 text-slate-500 font-medium leading-relaxed resize-none cursor-default"></textarea>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <button @click="openLinkAsset(item.id)" class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500 hover:text-white transition-all">
                                                <Database class="w-3.5 h-3.5" /> Link Asset
                                            </button>
                                            <button @click="router.get(route('expert.courses.preview', props.course.id))"
                                                    class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500 hover:text-indigo-400 transition-all">
                                                <MonitorPlay class="w-3.5 h-3.5" /> Logic Preview
                                            </button>
                                        </div>
                                        <button @click="handleSaveItem" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-indigo-600/20">
                                            Sync Unit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blank / Welcome State -->
                        <div v-else-if="activeInspectorType === 'course'" class="py-20 text-center space-y-10 animate-in fade-in duration-700">
                            <div class="w-24 h-24 bg-indigo-600/10 rounded-[2.5rem] border border-indigo-500/20 flex items-center justify-center mx-auto shadow-2xl shadow-indigo-600/10">
                                <Rocket class="w-10 h-10 text-indigo-400" />
                            </div>
                            <div class="space-y-4">
                                <h2 class="text-4xl font-black text-white tracking-tighter italic">"Precision in Pedagogy."</h2>
                                <p class="text-slate-500 max-w-md mx-auto leading-relaxed">Select a module or unit from the outline on the left to begin editing. Architect Studio provides a high-performance environment for curriculum scaling.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4 max-w-lg mx-auto">
                                <div class="p-6 bg-[#161920] border border-white/5 rounded-3xl text-left hover:border-indigo-500/50 transition-all group cursor-pointer"
                                     @click="router.get(route('expert.courses.preview', props.course.id))">
                                    <MonitorPlay class="w-6 h-6 text-indigo-500 mb-4 group-hover:scale-110 transition-transform" />
                                    <h4 class="text-sm font-black text-white">Live Preview</h4>
                                    <p class="text-[10px] text-slate-500 mt-2">See your course exactly as a student would experience it.</p>
                                </div>
                                <div class="p-6 bg-[#161920] border border-white/5 rounded-3xl text-left hover:border-emerald-500/50 transition-all group cursor-pointer" @click="handleAddModule">
                                    <PlusCircle class="w-6 h-6 text-emerald-500 mb-4 group-hover:scale-110 transition-transform" />
                                    <h4 class="text-sm font-black text-white">Add Module</h4>
                                    <p class="text-[10px] text-slate-500 mt-2">Initialize a new learning sequence container.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>

            <!-- ── PANE 3: INSPECTOR (Right) ── -->
            <aside v-show="inspectorOpen" class="w-80 border-l border-white/5 bg-[#161920] flex flex-col flex-shrink-0 animate-in slide-in-from-right duration-300">
                <div class="p-4 border-b border-white/5 bg-[#1A1D26]">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Inspector Properties</span>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-8">
                    
                    <!-- Course Global Properties -->
                    <div v-if="activeInspectorType === 'course'" class="space-y-6">
                         <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase text-slate-600 tracking-tighter px-1">Technical Parameters</label>
                            <div class="space-y-4">
                                <div class="group">
                                    <p class="text-[9px] font-bold text-slate-500 mb-1.5 px-1 uppercase tracking-widest">Attendance Code</p>
                                    <div class="flex items-center bg-[#0F1115] rounded-xl border border-white/10 group-focus-within:border-indigo-500/30 transition-all overflow-hidden">
                                        <input v-model="courseSettingsForm.attendance_code" type="text" 
                                               class="w-full bg-transparent border-none focus:ring-0 text-xs font-black text-white px-4 py-3 placeholder:text-slate-700" />
                                    </div>
                                </div>
                                <div class="group">
                                    <p class="text-[9px] font-bold text-slate-500 mb-1.5 px-1 uppercase tracking-widest">Live Portal Link</p>
                                    <div class="flex items-center bg-[#0F1115] rounded-xl border border-white/10 group-focus-within:border-indigo-500/30 transition-all overflow-hidden">
                                        <input v-model="courseSettingsForm.zoom_link" type="text" 
                                               placeholder="URL (Zoom/Teams/Meet)"
                                               class="w-full bg-transparent border-none focus:ring-0 text-xs font-black text-white px-4 py-3 placeholder:text-slate-700" />
                                    </div>
                                </div>
                            </div>
                         </div>

                         <div class="h-px bg-white/5"></div>

                         <div class="space-y-4">
                             <div class="flex items-center justify-between px-1">
                                <label class="text-[10px] font-black uppercase text-slate-600 tracking-tighter">Success Threshold</label>
                                <span class="text-xs font-black text-indigo-400">{{ courseSettingsForm.passing_grade }}%</span>
                             </div>
                             <input v-model.number="courseSettingsForm.passing_grade" type="range" min="0" max="100" class="w-full h-1.5 bg-[#0F1115] rounded-full accent-indigo-500" />
                         </div>

                         <div class="space-y-4">
                             <div class="flex items-center justify-between px-1">
                                <label class="text-[10px] font-black uppercase text-slate-600 tracking-tighter">Fast Track Score</label>
                                <span class="text-xs font-black text-amber-400">{{ courseSettingsForm.fast_track_threshold }}%</span>
                             </div>
                             <input v-model.number="courseSettingsForm.fast_track_threshold" type="range" min="0" max="100" class="w-full h-1.5 bg-[#0F1115] rounded-full accent-amber-500" />
                             <p class="text-[9px] text-slate-600 px-1">Min Pre-Test score to skip materials &amp; attendance</p>
                         </div>

                         <button @click="handleSaveCourse"
                                 :disabled="courseSettingsForm.processing"
                                 class="w-full py-3 bg-white text-black rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-400 hover:text-white transition-all disabled:opacity-50">
                             {{ courseSettingsForm.processing ? 'Saving...' : 'Update Global Context' }}
                         </button>
                    </div>

                    <!-- Module Properties -->
                    <div v-else-if="activeInspectorType === 'module'" class="space-y-6">
                        <div class="group">
                            <p class="text-[9px] font-bold text-slate-500 mb-1.5 px-1 uppercase tracking-widest">Module Identity</p>
                            <input v-model="moduleForm.title" type="text" 
                                   class="w-full bg-[#0F1115] rounded-xl border border-white/10 focus:border-indigo-500/30 text-xs font-black text-white px-4 py-3 shadow-inner" />
                        </div>
                        <div class="group">
                            <p class="text-[9px] font-bold text-slate-500 mb-1.5 px-1 uppercase tracking-widest">Description</p>
                            <textarea v-model="moduleForm.description" rows="4"
                                   class="w-full bg-[#0F1115] rounded-xl border border-white/10 focus:border-indigo-500/30 text-[11px] font-medium text-slate-400 px-4 py-3 leading-relaxed resize-none"></textarea>
                        </div>
                        <button @click="handleSaveModule" 
                                class="w-full py-3 bg-white/5 text-slate-200 border border-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white hover:text-black transition-all">
                            Save Module Metadata
                        </button>
                        <div class="h-px bg-white/5"></div>
                        <button @click="handleDeleteModule(moduleForm.id)" 
                                class="w-full py-3 text-rose-500/50 hover:text-rose-500 text-[10px] font-black uppercase tracking-widest transition-all">
                            Terminate Module
                        </button>
                    </div>

                    <!-- Item Specific Properties -->
                    <div v-else-if="activeInspectorType === 'item'" class="space-y-8 animate-in fade-in duration-300">
                        <div class="group">
                            <p class="text-[9px] font-bold text-slate-500 mb-1.5 px-1 uppercase tracking-widest">Unit Title</p>
                            <input v-model="itemForm.title" type="text" 
                                   class="w-full bg-[#0F1115] rounded-xl border border-white/10 focus:border-indigo-500/30 text-xs font-black text-white px-4 py-3 shadow-inner" />
                        </div>

                        <!-- Competency Framework -->
                        <div class="space-y-4">
                            <p class="text-[9px] font-bold text-slate-600 px-1 uppercase tracking-widest">Growth Matrix</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="tag in itemForm.competency_tags" :key="tag" 
                                      class="px-3 py-1 rounded-full bg-indigo-600/10 text-indigo-400 border border-indigo-500/20 text-[9px] font-black uppercase tracking-tight">
                                    {{ tag }}
                                </span>
                                <button class="px-3 py-1 rounded-full border border-white/5 text-slate-600 text-[9px] font-black">+</button>
                            </div>
                        </div>

                        <!-- Assessment Logic for Knowledge -->
                        <div v-if="itemForm.type === 'knowledge'" class="space-y-6">
                            <div class="space-y-3">
                                <p class="text-[9px] font-bold text-slate-500 px-1 uppercase tracking-widest">Assessment Mode</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <button @click="itemForm.assessment_mode = 'diagnostic'" 
                                            :class="itemForm.assessment_mode === 'diagnostic' ? 'bg-indigo-600 text-white' : 'bg-[#0F1115] text-slate-500 border border-white/5'"
                                            class="py-2.5 rounded-lg text-[9px] font-black uppercase transition-all">Diagnostic</button>
                                    <button @click="itemForm.assessment_mode = 'mastery'" 
                                            :class="itemForm.assessment_mode === 'mastery' ? 'bg-indigo-600 text-white' : 'bg-[#0F1115] text-slate-500 border border-white/5'"
                                            class="py-2.5 rounded-lg text-[9px] font-black uppercase transition-all">Mastery</button>
                                </div>
                            </div>
                            
                            <div v-if="itemForm.assessment_mode === 'mastery'" class="space-y-4 animate-in slide-in-from-top-2">
                                <div class="flex items-center justify-between px-1">
                                    <label class="text-[9px] font-black uppercase text-slate-600">Min. Pass Requirement</label>
                                    <span class="text-xs font-black text-indigo-400">{{ itemForm.passing_grade }}%</span>
                                </div>
                                <input v-model.number="itemForm.passing_grade" type="range" class="w-full accent-indigo-500" />
                            </div>

                            <div class="bg-indigo-500/5 rounded-2xl p-4 border border-indigo-500/10 space-y-4">
                                <div class="flex items-center justify-between px-1">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Time Limit (Min)</p>
                                    <input v-model.number="itemForm.time_limit" type="number" class="w-16 bg-transparent text-right text-xs font-black text-white border-none focus:ring-0 p-0" />
                                </div>
                                <div class="flex items-center justify-between px-1">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Max Attempts</p>
                                    <input v-model.number="itemForm.max_attempts" type="number" class="w-16 bg-transparent text-right text-xs font-black text-white border-none focus:ring-0 p-0" />
                                </div>
                            </div>
                        </div>

                        <!-- Rubric Builder for Exercise -->
                        <div v-if="itemForm.type === 'exercise'" class="space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Rubric Matrix</p>
                                <button @click="itemForm.rubric_json.push({label: '', points: 10})" class="text-indigo-400 hover:text-white transition-all">
                                    <Plus class="w-3 h-3" />
                                </button>
                            </div>
                            <div class="space-y-2">
                                <div v-for="(r, ri) in itemForm.rubric_json" :key="ri" class="flex gap-2">
                                    <input v-model="r.label" placeholder="Criteria" class="flex-1 bg-[#0F1115] border border-white/10 rounded-lg text-[10px] px-2 py-1.5 focus:border-indigo-500/30" />
                                    <input v-model.number="r.points" type="number" class="w-12 bg-[#0F1115] border border-white/10 rounded-lg text-[10px] px-1 py-1.5 text-center focus:border-indigo-500/30" />
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 space-y-3">
                            <button @click="handleSaveItem" 
                                    class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-opacity-80 transition-all shadow-xl shadow-indigo-600/20">
                                Apply All Changes
                            </button>
                            <button @click="handleDeleteItem(itemForm.id)" 
                                    class="w-full py-3 text-rose-500/50 hover:text-rose-500 text-[10px] font-black uppercase tracking-widest transition-all">
                                Remove Unit
                            </button>
                        </div>
                    </div>

                </div>
            </aside>

        </div>

    </div>

    <!-- ══ AI Architect Modal ══ -->
    <Teleport to="body">
        <div v-if="showAIModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showAIModal = false"></div>
            <div class="relative w-full max-w-lg bg-[#161920] rounded-3xl border border-white/10 shadow-2xl p-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-600/20 rounded-xl border border-indigo-500/30 flex items-center justify-center">
                            <Sparkles class="w-4 h-4 text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white">AI Architect</h3>
                            <p class="text-[10px] text-slate-500">Generate full curriculum using Gemini AI</p>
                        </div>
                    </div>
                    <button @click="showAIModal = false" class="p-2 hover:bg-white/5 rounded-xl transition-all text-slate-500 hover:text-white">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest">Additional Context (Optional)</label>
                    <textarea v-model="aiPromptText"
                              rows="4"
                              placeholder="E.g. Focus on practical exercises, 3 modules, target audience is middle managers..."
                              :disabled="isAILoading"
                              class="w-full bg-[#0F1115] border border-white/10 rounded-2xl focus:border-indigo-500/40 px-5 py-4 text-sm text-slate-300 placeholder:text-slate-700 resize-none transition-all"
                    ></textarea>
                </div>

                <div class="bg-indigo-500/5 border border-indigo-500/10 rounded-2xl p-4">
                    <p class="text-[10px] text-indigo-400 font-bold">Based on: <span class="text-white">{{ props.course.title }}</span></p>
                    <p class="text-[9px] text-slate-500 mt-1">AI will generate 3-4 modules with learning units. Existing modules will NOT be deleted.</p>
                </div>

                <div class="flex gap-3">
                    <button @click="showAIModal = false"
                            class="flex-1 py-3 bg-white/5 text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all">
                        Cancel
                    </button>
                    <button @click="submitAIArchitect"
                            :disabled="isAILoading"
                            class="flex-1 py-3 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-500 transition-all disabled:opacity-60 flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20">
                        <Loader2 v-if="isAILoading" class="w-3.5 h-3.5 animate-spin" />
                        <Sparkles v-else class="w-3.5 h-3.5" />
                        {{ isAILoading ? 'Generating...' : 'Generate Curriculum' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- ══ Link Asset Modal ══ -->
    <Teleport to="body">
        <div v-if="showLinkAssetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showLinkAssetModal = false"></div>
            <div class="relative w-full max-w-md bg-[#161920] rounded-3xl border border-white/10 shadow-2xl p-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-600/20 rounded-xl border border-emerald-500/30 flex items-center justify-center">
                            <Database class="w-4 h-4 text-emerald-400" />
                        </div>
                        <h3 class="text-sm font-black text-white">Link Asset</h3>
                    </div>
                    <button @click="showLinkAssetModal = false" class="p-1 text-slate-600 hover:text-white"><X class="w-4 h-4" /></button>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button @click="linkAssetType = 'url'"
                            :class="linkAssetType === 'url' ? 'bg-emerald-600 text-white' : 'bg-white/5 text-slate-400 border border-white/10'"
                            class="py-2.5 rounded-xl text-[10px] font-black uppercase transition-all">External URL</button>
                    <button @click="linkAssetType = 'file'"
                            :class="linkAssetType === 'file' ? 'bg-emerald-600 text-white' : 'bg-white/5 text-slate-400 border border-white/10'"
                            class="py-2.5 rounded-xl text-[10px] font-black uppercase transition-all">Upload File</button>
                </div>

                <div v-if="linkAssetType === 'url'" class="space-y-2">
                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest px-1">Resource URL</label>
                    <input v-model="linkAssetUrl" type="url" placeholder="https://youtube.com/watch?v=... or docs link"
                           class="w-full bg-[#0F1115] rounded-xl border border-white/10 focus:border-emerald-500/30 text-xs font-bold text-white px-4 py-3 placeholder:text-slate-700" />
                </div>

                <div v-else class="space-y-2">
                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest px-1">Upload Asset</label>
                    <input type="file" @change="handleLinkAssetFile" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.ppt,.pptx"
                           class="w-full bg-[#0F1115] rounded-xl border border-white/10 text-xs text-slate-400 px-4 py-3 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-600/20 file:text-emerald-400 file:text-[10px] file:font-black" />
                    <p v-if="linkAssetFile" class="text-[10px] text-emerald-400 font-bold px-1">{{ linkAssetFile.name }}</p>
                </div>

                <div class="flex gap-3">
                    <button @click="showLinkAssetModal = false"
                            class="flex-1 py-3 bg-white/5 text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all">
                        Cancel
                    </button>
                    <button @click="applyLinkAsset"
                            class="flex-1 py-3 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-500 transition-all shadow-lg shadow-emerald-600/20">
                        Insert Asset
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.1);
}

.animate-in {
    animation-fill-mode: both;
}

@keyframes slideInFromLeft {
    from { transform: translateX(-100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideInFromRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.slide-in-from-left { animation: slideInFromLeft 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-in-from-right { animation: slideInFromRight 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.fade-in { animation: fadeIn 0.8s ease-out; }

input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
}
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    height: 12px;
    width: 12px;
    border-radius: 50%;
    background: #6366f1;
    cursor: pointer;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
}

/* ─── Drag & Drop ─────────────────────────────────────────────────────────── */
.drag-ghost {
    opacity: 0.35;
    background: rgba(99, 102, 241, 0.08) !important;
    border: 1px dashed rgba(99, 102, 241, 0.3) !important;
    border-radius: 8px;
}

.drag-chosen {
    background: rgba(99, 102, 241, 0.12) !important;
    border-radius: 8px;
    box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.3), 0 8px 24px rgba(0, 0, 0, 0.4) !important;
}

.drag-handle-module,
.drag-handle-item {
    touch-action: none;
}
</style>
