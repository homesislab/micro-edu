<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Plus, GripVertical, MoreVertical, Play, FileText,
    HelpCircle, Award, UploadCloud, Edit2, Trash2, X,
    Dumbbell, Image, Link as LinkIcon, PlusCircle, Minus,
    ChevronRight, Rocket, Sparkles, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    course: { type: Object, required: true, default: () => ({ sections: [] }) },
    isGenerating: { type: Boolean, default: false }
});

const emit = defineEmits(['add-section','edit-section','delete-section','add-lesson','edit-lesson','delete-lesson', 'generate-ai']);

// ─── Local State ──────────────────────────────────────────────────────────────
const sections = ref(props.course.sections?.map(s => ({ ...s, expanded: true })) || []);
const showSectionModal = ref(false);
const showLessonModal  = ref(false);
const isEditing        = ref(false);

const sectionForm = ref({ id: null, title: '' });

// Lesson form
const lessonForm = useForm({
    id:             null,
    section_id:     null,
    title:          '',
    type:           'video',
    description:    '',
    evidence_type:  'upload_file',
    rubric_json:    [],
    content:        '',
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
const TYPE_META = {
    video:    { label: 'Video',               icon: Play,        color: 'text-blue-500',   bg: 'bg-blue-50' },
    text:     { label: 'Text Material',       icon: FileText,    color: 'text-slate-500',  bg: 'bg-slate-50' },
    quiz:     { label: 'Quiz',                icon: HelpCircle,  color: 'text-amber-500',  bg: 'bg-amber-50' },
    assignment:{ label: 'Assignment',         icon: Award,       color: 'text-emerald-500', bg: 'bg-emerald-50' },
    exercise: { label: 'Mission Exercise (L3)', icon: Dumbbell,  color: 'text-indigo-600', bg: 'bg-indigo-50' },
};

const getMeta = (type) => TYPE_META[type] ?? TYPE_META.text;

const addCriteria = () => {
    lessonForm.rubric_json = [...lessonForm.rubric_json, { label: '', max_points: 5 }];
};

const removeCriteria = (idx) => {
    lessonForm.rubric_json = lessonForm.rubric_json.filter((_, i) => i !== idx);
};

watch(() => lessonForm.type, (type) => {
    if (type === 'exercise' && lessonForm.rubric_json.length === 0) {
        lessonForm.rubric_json = [
            { label: 'Accuracy',     max_points: 5 },
            { label: 'Creativity',   max_points: 5 },
            { label: 'Completeness', max_points: 5 },
        ];
    }
});

const openSectionModal = (section = null) => {
    isEditing.value = !!section;
    sectionForm.value = section ? { ...section } : { id: null, title: '' };
    showSectionModal.value = true;
};

const openLessonModal = (sectionId, lesson = null) => {
    isEditing.value = !!lesson;
    lessonForm.id           = lesson?.id ?? null;
    lessonForm.section_id   = sectionId;
    lessonForm.title        = lesson?.title ?? '';
    lessonForm.type         = lesson?.type ?? 'video';
    lessonForm.description  = lesson?.description ?? '';
    lessonForm.evidence_type = lesson?.content_json?.evidence_type ?? 'upload_file';
    lessonForm.rubric_json  = lesson?.rubric_json ?? [];
    lessonForm.content      = lesson?.content_json?.description ?? '';
    showLessonModal.value   = true;
};

const closeModals = () => {
    showSectionModal.value = false;
    showLessonModal.value  = false;
};

const handleSectionSubmit = () => {
    if (isEditing.value) {
        emit('edit-section', sectionForm.value);
    } else {
        emit('add-section', sectionForm.value);
    }
    closeModals();
};

const handleLessonSubmit = () => {
    const payload = {
        title:       lessonForm.title,
        type:        lessonForm.type,
        content:     lessonForm.type === 'exercise' ? lessonForm.description : lessonForm.content,
        rubric_json: lessonForm.type === 'exercise' ? lessonForm.rubric_json : null,
        order_index: 0,
    };

    if (isEditing.value && lessonForm.id) {
        lessonForm.transform(() => payload)
                  .patch(route('expert.materials.update', lessonForm.id), {
                      onSuccess: () => closeModals(),
                      preserveScroll: true,
                  });
    } else {
        lessonForm.transform(() => payload)
                  .post(route('expert.materials.store', props.course.id), {
                      onSuccess: () => closeModals(),
                      preserveScroll: true,
                  });
    }
};
</script>

<template>
    <div class="max-w-4xl mx-auto py-10 animate-in fade-in slide-in-from-bottom-5 duration-700 bg-white min-h-screen px-4 sm:px-0">
        <!-- Header Area -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-10 gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Learning Sequence</h2>
            </div>
            <div class="flex items-center gap-3">
                <button @click="$emit('generate-ai')"
                        :disabled="isGenerating"
                        class="bg-indigo-50 text-indigo-700 border border-indigo-100 px-5 py-2.5 rounded-lg font-bold text-sm flex items-center gap-2 hover:bg-indigo-100 transition-colors shadow-sm disabled:opacity-50">
                    <component :is="isGenerating ? Loader2 : Sparkles" :class="{ 'animate-spin': isGenerating }" class="w-4 h-4" />
                    {{ isGenerating ? 'Forging...' : 'AI Forge' }}
                </button>
                <button @click="openSectionModal()" 
                        class="bg-white text-slate-700 border border-slate-200 px-5 py-2.5 rounded-lg font-bold text-sm flex items-center gap-2 hover:bg-slate-50 hover:border-slate-300 transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Add Modul
                </button>
            </div>
        </div>

        <!-- Modul Container (Sections) -->
        <div class="space-y-6">
            <div v-for="(section, sIdx) in sections" :key="section.id"
                 class="bg-slate-50 border border-slate-200 rounded-xl overflow-hidden transition-all duration-300">
                <!-- Section Header -->
                <div class="px-6 py-5 flex items-center justify-between cursor-pointer hover:bg-slate-100 transition-colors"
                     @click="section.expanded = !section.expanded">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 bg-white border border-slate-200 rounded-md flex items-center justify-center text-slate-700 font-bold text-sm shadow-sm">
                            {{ sIdx + 1 }}
                        </div>
                        <h3 class="text-base font-bold text-slate-900">{{ section.title }}</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click.stop="openLessonModal(section.id)"
                                class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-md transition-all border border-transparent hover:border-slate-200 hover:shadow-sm" title="Add Lesson">
                            <Plus class="w-4 h-4" />
                        </button>
                        <button @click.stop="openSectionModal(section)"
                                class="p-2 text-slate-400 hover:text-slate-700 hover:bg-white rounded-md transition-all border border-transparent hover:border-slate-200 hover:shadow-sm" title="Edit Modul">
                            <Edit2 class="w-4 h-4" />
                        </button>
                        <div class="w-px h-4 bg-slate-300 mx-2"></div>
                        <ChevronRight :class="section.expanded ? 'rotate-90' : ''" 
                                      class="w-5 h-5 text-slate-400 transition-transform duration-300" />
                    </div>
                </div>

                <!-- Lesson Rows -->
                <div v-show="section.expanded" class="px-6 pb-6 space-y-3">
                    <template v-if="section.lessons && section.lessons.length">
                        <div v-for="lesson in section.lessons" :key="lesson.id"
                             :class="[
                                'flex items-center justify-between group/lesson transition-all p-4',
                                lesson?.type === 'exercise' 
                                    ? 'ml-8 bg-purple-50/50 border-l-4 border-l-indigo-600 rounded-r-lg border border-transparent' 
                                    : 'bg-white border border-slate-200 rounded-lg shadow-sm hover:border-slate-300'
                             ]">
                        
                        <div class="flex items-center gap-4">
                            <div :class="lesson?.type === 'exercise' ? 'text-indigo-600 bg-white shadow-sm p-2 rounded-md' : getMeta(lesson?.type).color"
                                 class="flex items-center justify-center font-black">
                                <component :is="getMeta(lesson?.type).icon" class="w-5 h-5" />
                            </div>
                            <div>
                                <h4 :class="lesson?.type === 'exercise' ? 'text-indigo-900 font-bold' : 'text-slate-800 font-semibold'"
                                    class="text-sm tracking-tight flex items-center gap-2">
                                    {{ lesson?.title || 'Untitled Unit' }}
                                    <span v-if="lesson?.type === 'exercise'" 
                                          class="text-[10px] bg-white text-indigo-600 border border-indigo-100 px-2 py-0.5 rounded font-bold shadow-sm">
                                        L3
                                    </span>
                                </h4>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 opacity-0 group-hover/lesson:opacity-100 transition-opacity">
                            <button @click="openLessonModal(section.id, lesson)"
                                    class="p-1.5 text-slate-400 hover:text-indigo-600 transition-colors rounded hover:bg-slate-100">
                                <Edit2 class="w-4 h-4" />
                            </button>
                            <button @click="$emit('delete-lesson', lesson?.id)"
                                    class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors rounded hover:bg-slate-100">
                                <Trash2 class="w-4 h-4" />
                            </button>
                            <GripVertical class="w-4 h-4 text-slate-300 cursor-grab ml-2 hover:text-slate-500" />
                        </div>
                    </div>
                </template>

                    <!-- Empty lessons -->
                    <div v-if="!section.lessons?.length"
                         class="py-8 border border-dashed border-slate-300 rounded-lg text-center bg-white mt-2">
                        <p class="text-slate-400 font-medium text-sm">No units in this modul yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty Sections -->
        <div v-if="sections.length === 0" class="py-24 text-center bg-slate-50 rounded-xl border border-slate-200 mt-6">
            <div class="w-24 h-24 bg-white rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl">
                <Rocket class="w-12 h-12 text-indigo-600 animate-pulse" />
            </div>
            <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-2">No sequence design found.</h3>
            <p class="text-slate-400 font-bold max-w-sm mx-auto leading-relaxed">Create your first modul to define the learning architecture of this program.</p>
            <button @click="openSectionModal()" 
                    class="mt-10 bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black text-sm shadow-2xl hover:bg-slate-900 transition-all">
                Initialize First Modul
            </button>
        </div>

        <!-- Section Modal -->
        <div v-if="showSectionModal"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[100] flex items-center justify-center p-6">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                    <h4 class="text-xl font-black text-slate-900 tracking-tight">{{ isEditing ? 'Edit Modul' : 'Initialize Modul' }}</h4>
                    <button @click="closeModals" class="p-2 hover:bg-white rounded-xl text-slate-400">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="handleSectionSubmit" class="p-8 space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Modul Designation</label>
                        <input v-model="sectionForm.title" type="text" required
                               placeholder="e.g. Fundamental Logic Architecture"
                               class="w-full bg-slate-50 border-2 border-transparent rounded-[1.25rem] px-5 py-4 font-bold text-slate-800 focus:border-indigo-300 focus:bg-white outline-none transition-all" />
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black hover:bg-slate-900 transition-all shadow-2xl shadow-indigo-100 active:scale-95">
                        {{ isEditing ? 'Confirm Update' : 'Establish Modul' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Lesson Modal -->
        <div v-if="showLessonModal"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[100] flex items-center justify-center p-6 overflow-y-auto">
            <div class="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 my-4">
                <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between"
                     :class="lessonForm.type === 'exercise' ? 'bg-indigo-50/50' : 'bg-slate-50/30'">
                    <div class="flex items-center gap-4">
                        <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm', getMeta(lessonForm.type).bg]">
                            <component :is="getMeta(lessonForm.type).icon" :class="['w-6 h-6', getMeta(lessonForm.type).color]" />
                        </div>
                        <div>
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ isEditing ? 'Refine Unit' : 'Deploy New Unit' }}</h4>
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ getMeta(lessonForm.type).label }}</p>
                        </div>
                    </div>
                    <button @click="closeModals" class="p-2 hover:bg-white rounded-xl text-slate-400 transition-all">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-10 space-y-8">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Unit Title</label>
                        <input v-model="lessonForm.title" type="text" required
                               placeholder="e.g. Masterclass: Technical Implementation"
                               class="w-full bg-slate-50 border-2 border-transparent rounded-2xl px-6 py-4 font-bold text-slate-800 focus:border-indigo-300 focus:bg-white outline-none transition-all" />
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">Unit Methodology</label>
                        <div class="grid grid-cols-5 gap-3">
                            <button v-for="(meta, key) in TYPE_META" :key="key"
                                    type="button"
                                    @click="lessonForm.type = key"
                                    :class="[
                                        'flex flex-col items-center gap-2 py-4 rounded-2xl border-2 text-center transition-all',
                                        lessonForm.type === key
                                            ? 'border-indigo-400 bg-indigo-50/50 shadow-sm'
                                            : 'border-transparent bg-slate-50/50 hover:border-slate-100 hover:bg-slate-50',
                                    ]">
                                <component :is="meta.icon" :class="['w-5 h-5', lessonForm.type === key ? meta.color : 'text-slate-300']" />
                                <span :class="['text-[9px] font-black uppercase tracking-wide', lessonForm.type === key ? meta.color : 'text-slate-400']">
                                    {{ key }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div v-if="lessonForm.type !== 'exercise'">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">
                            {{ lessonForm.type === 'video' ? 'Source Reference (URL/ID)' : 'Core Content Data' }}
                        </label>
                        <textarea v-model="lessonForm.content" rows="4"
                                  class="w-full bg-slate-50 border-2 border-transparent rounded-2xl px-6 py-4 font-medium text-slate-700 focus:border-indigo-300 focus:bg-white outline-none transition-all resize-none"></textarea>
                    </div>

                    <div v-if="lessonForm.type === 'exercise'" class="space-y-8 animate-in fade-in duration-500">
                        <div>
                            <label class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-2 block">Behavioral Mission Objective</label>
                            <textarea v-model="lessonForm.description" rows="4"
                                      class="w-full bg-indigo-50/30 border-2 border-indigo-100/50 rounded-2xl px-6 py-4 font-medium text-slate-700 focus:border-indigo-400 focus:bg-white outline-none transition-all resize-none"></textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Assessment Matrix</label>
                                <button type="button" @click="addCriteria"
                                        class="flex items-center gap-1.5 text-xs font-black text-indigo-600 hover:text-slate-900 transition-colors">
                                    <PlusCircle class="w-4 h-4" /> Add Benchmark
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div v-for="(criteria, idx) in lessonForm.rubric_json" :key="idx"
                                     class="flex items-center gap-4 p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                                    <input v-model="criteria.label" type="text" placeholder="Benchmark Title"
                                           class="flex-1 bg-slate-50 border-none rounded-xl px-4 py-2 text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-indigo-100" />
                                    <div class="flex items-center gap-3">
                                        <input v-model.number="criteria.max_points" type="number"
                                               class="w-16 bg-slate-50 border-none rounded-xl px-2 py-2 text-sm font-black text-center text-slate-700 outline-none" />
                                        <button type="button" @click="removeCriteria(idx)"
                                                class="text-slate-300 hover:text-rose-500 transition-colors">
                                            <Minus class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button @click="handleLessonSubmit"
                            :disabled="lessonForm.processing"
                            class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black text-sm shadow-2xl hover:bg-slate-900 transition-all active:scale-95 disabled:opacity-50">
                        {{ isEditing ? 'Apply Changes' : 'Commit to Modul' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
