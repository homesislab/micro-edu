<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronDown,
    CheckCircle2,
    Lock,
    Target,
    UploadCloud,
    Rocket,
    Eye,
    FileText,
    Video,
    HelpCircle,
    Activity,
    Award,
    ArrowLeft
} from 'lucide-vue-next';

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
    course:     { type: Object, required: true },
    enrollment: { type: Object, default: null },
    isPreview:  { type: Boolean, default: false },
});

// ─── State ───────────────────────────────────────────────────────────────────
const activeItemId   = ref(props.course.modules?.[0]?.curriculum_items?.[0]?.id ?? null);
const activeModuleId = ref(props.course.modules?.[0]?.id ?? null);

const expandedModules = ref(
    (props.course.modules ?? []).map(m => m.id)
);

// ─── Computed ─────────────────────────────────────────────────────────────────
const courseModules = computed(() => props.course.modules ?? []);

const activeItem = computed(() => {
    for (const mod of courseModules.value) {
        const found = (mod.curriculum_items ?? []).find(i => i.id === activeItemId.value);
        if (found) return found;
    }
    return null;
});

const progressPercent = computed(() => {
    if (props.isPreview) return 0;
    if (!props.enrollment) return 0;
    const statuses = [
        'enrolled', 
        'pre_test_done', 
        'content_done', 
        'attended', 
        'post_test_done', 
        'l1_done', 
        'l3_submitted', 
        'completed'
    ];
    const idx = statuses.indexOf(props.enrollment.status);
    return idx < 0 ? 0 : Math.round(((idx + 1) / statuses.length) * 100);
});

// ─── Helpers ─────────────────────────────────────────────────────────────────
const ITEM_ICONS = {
    literal:   FileText,
    visual:    Video,
    knowledge: HelpCircle,
    exercise:  Target,
};

const getItemIcon = (item) => {
    if (item.type === 'knowledge') {
        if (item.sub_type === 'pre_test')   return Activity;
        if (item.sub_type === 'final_exam') return Award;
    }
    return ITEM_ICONS[item.type] ?? FileText;
};

const getItemLabel = (item) => {
    if (item.type === 'knowledge') {
        if (item.sub_type === 'pre_test')   return 'Pre-Test';
        if (item.sub_type === 'final_exam') return 'Final Exam';
        return 'Quiz';
    }
    const map = { literal: 'Reading', visual: 'Video', exercise: 'Exercise' };
    return map[item.type] ?? item.type;
};

const getItemStatusClass = (item) => {
    if (item.id === activeItemId.value)
        return 'bg-indigo-50 border-indigo-200 text-indigo-900 border font-semibold shadow-sm';
    return 'text-slate-600 hover:bg-slate-50';
};

const toggleModule = (id) => {
    if (expandedModules.value.includes(id)) {
        expandedModules.value = expandedModules.value.filter(m => m !== id);
    } else {
        expandedModules.value.push(id);
    }
};

const selectItem = (item, moduleId) => {
    activeItemId.value   = item.id;
    activeModuleId.value = moduleId;
};

// ─── Content rendering helpers ────────────────────────────────────────────────
const parsedContent = computed(() => {
    const item = activeItem.value;
    if (!item) return null;
    if (item.type === 'knowledge' && item.content) {
        try {
            return typeof item.content === 'string' ? JSON.parse(item.content) : item.content;
        } catch {
            return null;
        }
    }
    return null;
});

const isVideoUrl = (str) => /\.(mp4|webm|ogg)$/i.test(str) || /youtube\.com|youtu\.be|vimeo\.com/.test(str);
const getYoutubeId = (url) => {
    const m = url.match(/(?:youtu\.be\/|v=|embed\/)([^?&]+)/);
    return m ? m[1] : null;
};
</script>

<template>
    <Head :title="(isPreview ? '[Preview] ' : '') + course.title" />

    <div class="h-screen flex bg-white font-sans overflow-hidden">

        <!-- ── SIDEBAR: Curriculum Map ── -->
        <aside class="w-80 flex-shrink-0 border-r border-slate-200 flex flex-col h-full bg-white z-20 shadow-[2px_0_10px_rgba(0,0,0,0.02)]">

            <!-- Logo Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center shadow-md">
                        <span class="text-white font-black text-sm">M</span>
                    </div>
                    <span class="font-black text-lg tracking-tight text-slate-800">MicroEducate</span>
                </div>
                <!-- Preview badge + back button -->
                <div v-if="isPreview" class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-amber-100 text-amber-700 text-[9px] font-black uppercase tracking-widest rounded-lg border border-amber-200 flex items-center gap-1">
                        <Eye class="w-3 h-3" /> Preview
                    </span>
                    <a :href="route('expert.courses.builder', course.id)"
                       class="p-1.5 hover:bg-slate-100 rounded-lg transition-all text-slate-500 hover:text-slate-800">
                        <ArrowLeft class="w-4 h-4" />
                    </a>
                </div>
            </div>

            <!-- Course Info + Progress -->
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex-1 min-w-0 pr-3">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest leading-tight">Course</p>
                        <h1 class="text-base font-black text-slate-900 leading-tight truncate">{{ course.title }}</h1>
                    </div>

                    <!-- Progress Donut -->
                    <div class="relative w-14 h-14 flex-shrink-0">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" stroke-width="4" stroke="currentColor" fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-indigo-600 transition-all duration-1000 ease-out"
                                :stroke-dasharray="`${progressPercent}, 100`"
                                stroke-width="4" stroke="currentColor" fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xs font-black text-slate-900 leading-none">{{ progressPercent }}%</span>
                            <span class="text-[6px] font-bold text-slate-500 uppercase">Done</span>
                        </div>
                    </div>
                </div>

                <div v-if="isPreview" class="bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                    <p class="text-[10px] text-amber-700 font-bold">Expert Preview Mode — Interactions are disabled</p>
                </div>
            </div>

            <!-- Curriculum Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                <div v-if="courseModules.length === 0" class="text-center py-10">
                    <p class="text-sm text-slate-400 font-medium">No modules yet.</p>
                    <p class="text-xs text-slate-300 mt-1">Add modules in the Builder.</p>
                </div>

                <div v-for="mod in courseModules" :key="mod.id" class="border border-slate-200 rounded-2xl overflow-hidden bg-white">
                    <!-- Module Header -->
                    <button @click="toggleModule(mod.id)"
                            class="w-full flex items-center justify-between p-4 bg-white hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-4 h-4 flex-shrink-0">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                            </div>
                            <span class="text-xs font-black text-slate-800 truncate">{{ mod.title }}</span>
                        </div>
                        <ChevronDown :class="{ 'rotate-180': expandedModules.includes(mod.id) }"
                                     class="w-4 h-4 text-slate-400 transition-transform flex-shrink-0 ml-2" />
                    </button>

                    <!-- Items List -->
                    <div v-show="expandedModules.includes(mod.id)" class="px-2 pb-2 space-y-1 bg-white border-t border-slate-50">
                        <button v-for="item in (mod.curriculum_items ?? [])" :key="item.id"
                                @click="selectItem(item, mod.id)"
                                :class="['w-full flex items-center gap-3 p-3 rounded-xl transition-all text-left mt-1', getItemStatusClass(item)]">
                            <component :is="getItemIcon(item)"
                                       class="w-4 h-4 flex-shrink-0"
                                       :class="item.id === activeItemId ? 'text-indigo-600' : 'text-slate-400'" />
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold truncate" :class="item.id === activeItemId ? 'text-indigo-900' : 'text-slate-700'">
                                    {{ item.title }}
                                </p>
                                <p class="text-[9px] text-slate-400 font-medium uppercase tracking-wider">{{ getItemLabel(item) }}</p>
                            </div>
                        </button>

                        <div v-if="(mod.curriculum_items ?? []).length === 0" class="px-3 py-4 text-center">
                            <p class="text-[10px] text-slate-300 font-medium">No units in this module</p>
                        </div>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- ── MAIN CONTENT AREA ── -->
        <main class="flex-1 bg-slate-50/50 overflow-y-auto relative py-12 px-8">
            <div class="max-w-4xl mx-auto">

                <!-- Empty state when no item selected -->
                <div v-if="!activeItem" class="flex flex-col items-center justify-center h-full py-32 text-center">
                    <div class="w-20 h-20 bg-indigo-50 rounded-3xl border border-indigo-100 flex items-center justify-center mb-6">
                        <Target class="w-10 h-10 text-indigo-400" />
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 mb-2">Select a unit to begin</h2>
                    <p class="text-slate-400 text-sm max-w-sm">Click any unit in the curriculum outline on the left to view its content.</p>
                </div>

                <!-- Active Item Content -->
                <div v-else class="space-y-8 animate-in fade-in duration-300">

                    <!-- Item Header -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border"
                                  :class="{
                                    'bg-blue-50 text-blue-700 border-blue-200':   activeItem.type === 'literal',
                                    'bg-purple-50 text-purple-700 border-purple-200': activeItem.type === 'visual',
                                    'bg-emerald-50 text-emerald-700 border-emerald-200': activeItem.type === 'knowledge',
                                    'bg-orange-50 text-orange-700 border-orange-200':  activeItem.type === 'exercise',
                                  }">
                                {{ getItemLabel(activeItem) }}
                            </span>
                            <span v-if="activeItem.is_capstone" class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200">
                                Capstone Mission
                            </span>
                            <span v-if="activeItem.assessment_mode === 'mastery'" class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 border border-rose-200">
                                Mastery Gate
                            </span>
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ activeItem.title }}</h2>
                    </div>

                    <!-- ── LITERAL: Text/Markdown Content ── -->
                    <div v-if="activeItem.type === 'literal'"
                         class="bg-white rounded-3xl border border-slate-200 shadow-sm p-10">
                        <div v-if="activeItem.content" class="prose prose-slate max-w-none text-slate-700 leading-relaxed whitespace-pre-wrap">{{ activeItem.content }}</div>
                        <div v-else class="text-center py-16 text-slate-300">
                            <FileText class="w-12 h-12 mx-auto mb-3" />
                            <p class="font-medium">No content yet.</p>
                            <p class="text-sm mt-1">Add content in the Builder.</p>
                        </div>
                    </div>

                    <!-- ── VISUAL: Video Content ── -->
                    <div v-else-if="activeItem.type === 'visual'"
                         class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <template v-if="activeItem.content">
                            <!-- YouTube embed -->
                            <div v-if="getYoutubeId(activeItem.content)" class="aspect-video">
                                <iframe :src="`https://www.youtube.com/embed/${getYoutubeId(activeItem.content)}`"
                                        class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                            </div>
                            <!-- Native video -->
                            <div v-else-if="isVideoUrl(activeItem.content)" class="aspect-video bg-black">
                                <video :src="activeItem.content" controls class="w-full h-full"></video>
                            </div>
                            <!-- Raw URL link -->
                            <div v-else class="p-10 text-center">
                                <Video class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                                <p class="text-slate-600 font-medium mb-4">{{ activeItem.content }}</p>
                                <a :href="activeItem.content" target="_blank"
                                   class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">
                                    Open Video
                                </a>
                            </div>
                        </template>
                        <div v-else class="p-10 text-center py-16 text-slate-300">
                            <Video class="w-12 h-12 mx-auto mb-3" />
                            <p class="font-medium">No video URL yet. Add it in the Builder.</p>
                        </div>
                    </div>

                    <!-- ── KNOWLEDGE: Quiz ── -->
                    <div v-else-if="activeItem.type === 'knowledge'"
                         class="bg-white rounded-3xl border border-slate-200 shadow-sm p-10 space-y-6">
                        <div class="flex items-start justify-between border-b border-slate-100 pb-6">
                            <div>
                                <h3 class="text-xl font-black text-slate-900">{{ activeItem.title }}</h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    {{ activeItem.assessment_mode === 'mastery' ? `Mastery test — minimum ${activeItem.passing_grade ?? 70}% to pass` : 'Diagnostic — no minimum score' }}
                                </p>
                            </div>
                            <div class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg border border-emerald-100 text-xs font-bold">
                                Kirkpatrick L2
                            </div>
                        </div>

                        <div v-if="parsedContent" class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Time Limit</p>
                                <p class="font-black text-slate-800">{{ parsedContent.time_limit > 0 ? parsedContent.time_limit + ' min' : 'Unlimited' }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Max Attempts</p>
                                <p class="font-black text-slate-800">{{ parsedContent.max_attempts > 0 ? parsedContent.max_attempts : 'Unlimited' }}</p>
                            </div>
                        </div>

                        <div v-if="isPreview" class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 text-center">
                            <HelpCircle class="w-8 h-8 text-indigo-400 mx-auto mb-2" />
                            <p class="text-sm font-bold text-slate-700">Quiz questions are loaded at runtime.</p>
                            <p class="text-xs text-slate-500 mt-1">Students will see the actual quiz here. Add questions from Quiz Bank in the Builder.</p>
                        </div>
                    </div>

                    <!-- ── EXERCISE: L3 Assignment ── -->
                    <div v-else-if="activeItem.type === 'exercise'"
                         class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-10 space-y-8">
                            <!-- Mission Header -->
                            <div class="flex items-start justify-between border-b border-slate-100 pb-6">
                                <h3 class="text-2xl font-black text-slate-900 tracking-tight pr-4">{{ activeItem.title }}</h3>
                                <div class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg border border-blue-100 text-xs font-bold whitespace-nowrap">
                                    Kirkpatrick L3
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div v-if="activeItem.content">
                                <h4 class="text-sm font-black text-slate-800 mb-3">Instructions</h4>
                                <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-wrap">{{ activeItem.content }}</p>
                            </div>

                            <!-- Rubric -->
                            <div v-if="activeItem.rubric_json && activeItem.rubric_json.length > 0">
                                <h4 class="text-sm font-black text-slate-800 mb-4">Evaluation Rubric</h4>
                                <div class="space-y-3">
                                    <div v-for="(r, i) in activeItem.rubric_json" :key="i"
                                         class="flex justify-between items-center text-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                                            <span class="text-slate-700 font-medium">{{ r.label || 'Criteria ' + (i+1) }}</span>
                                        </div>
                                        <span class="font-bold text-slate-900">{{ r.points }} pts</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-3 border-t border-slate-100 mt-2">
                                        <span class="font-black text-slate-900 text-sm">Total</span>
                                        <span class="font-black text-slate-900 text-sm">
                                            {{ activeItem.rubric_json.reduce((s, r) => s + Number(r.points || 0), 0) }} pts
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submission Zone (disabled in preview) -->
                            <div>
                                <h4 class="text-sm font-black text-slate-800 mb-4">Submission Zone</h4>
                                <div class="border-2 border-dashed rounded-2xl p-10 text-center"
                                     :class="isPreview ? 'border-slate-200 bg-slate-50 cursor-not-allowed' : 'border-slate-300 hover:bg-slate-50 cursor-pointer group'">
                                    <UploadCloud class="w-10 h-10 mx-auto mb-3 transition-all duration-300"
                                                 :class="isPreview ? 'text-slate-300' : 'text-slate-400 group-hover:scale-110 group-hover:text-indigo-500'" />
                                    <p class="text-sm font-bold text-slate-700 mb-1">
                                        {{ isPreview ? 'Submission disabled in preview' : 'Drag & drop your file here' }}
                                    </p>
                                    <p v-if="!isPreview" class="text-xs text-indigo-600 font-semibold underline">click to upload</p>
                                    <p class="text-[10px] text-slate-400 font-medium mt-2">Supported: PDF, DOCX — max 25 MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Action Bar -->
                        <div class="bg-slate-50/80 px-10 py-5 border-t border-slate-100 flex items-center justify-between">
                            <div v-if="isPreview" class="text-xs text-amber-600 font-bold flex items-center gap-2">
                                <Eye class="w-4 h-4" />
                                Preview Mode — buttons are disabled
                            </div>
                            <template v-else>
                                <button class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 transition-all">
                                    Save Progress
                                </button>
                                <button class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-lg transition-all flex items-center gap-2">
                                    <Rocket class="w-4 h-4" />
                                    Submit for Expert Review
                                </button>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

.animate-in { animation-fill-mode: both; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.fade-in { animation: fadeIn 0.4s ease-out; }

.prose { line-height: 1.75; }
</style>
