<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Plus, 
    Video, 
    FileText, 
    Trash2, 
    Edit2, 
    ChevronRight, 
    BookOpen, 
    HelpCircle,
    ArrowLeft,
    Save,
    Settings,
    Clock,
    Link as LinkIcon,
    CheckCircle2,
    Circle,
    Shuffle,
    Copy,
    Check as CheckIcon,
    Sparkles,
    Loader2,
    Rocket
} from 'lucide-vue-next';
import ArchitectStudio from '@/Components/Expert/ArchitectStudio.vue';

const props = defineProps({
    course: Object,
    tab: {
        type: String,
        default: 'curriculum'
    }
});

const activeTab = ref(props.tab || 'curriculum'); // curriculum | assessment | settings
const showMaterialModal = ref(false);
const showQuestionModal = ref(false);
const editingMaterial = ref(null);
const editingQuestion = ref(null);

const materialForm = useForm({
    title: '',
    type: 'video',
    content: '',
    order_index: 0,
});

const questionForm = useForm({
    question_text: '',
    type: 'pretest',
    options: [
        { key: 'a', text: '' },
        { key: 'b', text: '' },
        { key: 'c', text: '' },
        { key: 'd', text: '' },
    ],
    correct_answer: 'a',
    weight: 1,
    competency_tags: [],
});

const settingsForm = useForm({
    attendance_code: props.course.attendance_code ?? '',
    zoom_link: props.course.zoom_link ?? '',
    event_time: props.course.event_time ? new Date(props.course.event_time).toISOString().slice(0, 16) : '',
    passing_grade: props.course.passing_grade ?? 70,
});

const saveSettings = () => {
    settingsForm.patch(route('expert.courses.settings', props.course.id));
};

const codeCopied = ref(false);
const generateAttendanceCode = () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no confusing chars (0,O,1,I)
    const code = Array.from({ length: 8 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    settingsForm.attendance_code = code;
};

const copyCode = async () => {
    if (!settingsForm.attendance_code) return;
    await navigator.clipboard.writeText(settingsForm.attendance_code);
    codeCopied.value = true;
    setTimeout(() => codeCopied.value = false, 2000);
};

const openMaterialModal = (material = null) => {
    editingMaterial.value = material;
    if (material) {
        materialForm.title = material.title;
        materialForm.type = material.type;
        materialForm.content = material.content;
        materialForm.order_index = material.order_index ?? 0;
    } else {
        materialForm.reset();
    }
    showMaterialModal.value = true;
};

const openQuestionModal = (question = null) => {
    editingQuestion.value = question;
    if (question) {
        questionForm.question_text = question.question_text;
        questionForm.type = question.type;
        questionForm.options = question.options;
        questionForm.correct_answer = question.correct_answer ?? question.correct_key;
        questionForm.weight = question.weight ?? 1;
        questionForm.competency_tags = question.competency_tags ?? [];
    } else {
        questionForm.reset();
        questionForm.weight = 1;
        questionForm.competency_tags = [];
    }
    showQuestionModal.value = true;
};

const saveMaterial = () => {
    if (editingMaterial.value) {
        materialForm.patch(route('expert.materials.update', editingMaterial.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        materialForm.post(route('expert.materials.store', props.course.id), {
            onSuccess: () => closeModal(),
        });
    }
};

const saveQuestion = () => {
    if (editingQuestion.value) {
        questionForm.patch(route('expert.questions.update', editingQuestion.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        questionForm.post(route('expert.questions.store', props.course.id), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteMaterial = (id) => {
    if (confirm('Delete this material?')) {
        useForm({}).delete(route('expert.materials.delete', id));
    }
};

const deleteQuestion = (id) => {
    if (confirm('Delete this question?')) {
        useForm({}).delete(route('expert.questions.delete', id));
    }
};

const closeModal = () => {
    showMaterialModal.value = false;
    showQuestionModal.value = false;
    editingMaterial.value = null;
    editingQuestion.value = null;
};

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }

    router.get(route('expert.dashboard'));
};

const isGeneratingCurriculum = ref(false);
const generateCurriculumAI = () => {
    if (!confirm('AI will generate initial materials based on your course vision. Existing materials will not be deleted. Continue?')) return;
    
    isGeneratingCurriculum.value = true;
    router.post(route('expert.ai.generate-curriculum', props.course.id), {}, {
        onFinish: () => isGeneratingCurriculum.value = false,
        preserveScroll: true,
    });
};

const isGeneratingQuestions = ref(false);
const generateQuestionsAI = () => {
    if (!confirm('AI will generate pre-test and post-test questions based on your course materials. Continue?')) return;
    
    isGeneratingQuestions.value = true;
    router.post(route('expert.ai.generate-questions', props.course.id), {}, {
        onFinish: () => isGeneratingQuestions.value = false,
        preserveScroll: true,
    });
};
const toggleStatus = () => {
    const newStatus = props.course.status === 'published' ? 'draft' : 'published';
    useForm({
        status: newStatus
    }).patch(route('expert.courses.status.update', props.course.id), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="'Builder - ' + course.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between py-6">
                <div class="flex items-center gap-6">
                    <button @click="goBack()" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center border border-slate-100 shadow-sm hover:shadow-xl hover:translate-x-1 transition-all group">
                        <ArrowLeft class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 transition-colors" />
                    </button>
                    <div>
                        <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                            <span class="w-8 h-px bg-indigo-600"></span> Architect Studio
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 tracking-tight">
                            {{ course.title }}<span class="text-indigo-600">.</span>
                        </h2>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Tabs: Glass Switcher -->
            <div class="flex items-center p-1.5 bg-slate-200/50 backdrop-blur-2xl rounded-[1.75rem] border border-white/50 shadow-inner w-fit">
                <button @click="activeTab = 'curriculum'" 
                        :class="activeTab === 'curriculum' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-900'"
                        class="px-8 py-3 rounded-2xl font-black text-xs transition-all flex items-center gap-2">
                    <BookOpen class="w-4 h-4" /> Curriculum
                </button>
                <button @click="activeTab = 'settings'" 
                        :class="activeTab === 'settings' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-900'"
                        class="px-8 py-3 rounded-2xl font-black text-xs transition-all flex items-center gap-2">
                    <Settings class="w-4 h-4" /> Parameters
                </button>
            </div>

            <!-- Tab Content: Curriculum (Architect Studio) -->
            <div v-if="activeTab === 'curriculum'" class="space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700">
                <ArchitectStudio 
                    :course="course" 
                    :is-generating="isGeneratingCurriculum"
                    @generate-ai="generateCurriculumAI"
                />
            </div>

            <!-- Tab Content: Parameters -->
            <div v-if="activeTab === 'settings'" class="max-w-4xl animate-in fade-in slide-in-from-bottom-5 duration-700">
                <div class="bg-white rounded-[3.5rem] p-16 border border-slate-100 shadow-2xl space-y-16 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-80 h-80 bg-slate-50 rounded-full blur-[100px] -mr-40 -mt-40"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
                        <div>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight">System Parameters</h3>
                            <p class="text-slate-400 font-bold text-sm mt-1">Configure live integration and success thresholds.</p>
                        </div>
                        <div class="bg-slate-900 px-6 py-4 rounded-2xl flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                                <Clock class="text-white w-5 h-5" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Sync</p>
                                <p class="text-xs text-white font-black">ACTIVE</p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="saveSettings" class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Attendance Authority Code</label>
                            <div class="flex gap-3 items-stretch">
                                <input v-model="settingsForm.attendance_code" type="text" placeholder="e.g. AGILE2026" 
                                       class="flex-1 px-8 py-5 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-8 focus:ring-indigo-50 transition-all font-black tracking-widest outline-none shadow-inner uppercase" />
                                <!-- Generate random code -->
                                <button type="button" @click="generateAttendanceCode"
                                        title="Generate random code"
                                        class="px-5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2 font-black text-xs shadow-lg shadow-indigo-200 whitespace-nowrap">
                                    <Shuffle class="w-5 h-5" />
                                </button>
                                <!-- Copy code -->
                                <button type="button" @click="copyCode"
                                        :disabled="!settingsForm.attendance_code"
                                        title="Copy to clipboard"
                                        :class="codeCopied ? 'bg-emerald-500' : 'bg-slate-900'"
                                        class="px-5 text-white rounded-2xl transition-all active:scale-95 flex items-center gap-2 font-black text-xs disabled:opacity-30">
                                    <CheckIcon v-if="codeCopied" class="w-5 h-5" />
                                    <Copy v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 font-bold px-4">REQUIRED FOR SESSION UNLOCK — Share this code with participants during live session</p>
                        </div>
                        
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Mastery Threshold (%)</label>
                            <div class="relative">
                                <input v-model="settingsForm.passing_grade" type="number" 
                                       class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-8 focus:ring-indigo-50 transition-all font-black text-xl outline-none shadow-inner" />
                                <span class="absolute right-8 top-1/2 -translate-y-1/2 font-black text-slate-300">%</span>
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Live Execution Portal (Zoom)</label>
                            <div class="relative group">
                                <LinkIcon class="absolute left-8 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-600 transition-colors w-5 h-5" />
                                <input v-model="settingsForm.zoom_link" type="url" placeholder="https://zoom.us/j/..." 
                                       class="w-full pl-16 pr-8 py-5 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-8 focus:ring-indigo-50 transition-all font-bold text-slate-600 outline-none shadow-inner" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Mission Execution Time</label>
                            <input v-model="settingsForm.event_time" type="datetime-local" 
                                   class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-8 focus:ring-indigo-50 transition-all font-black text-sm outline-none shadow-inner" />
                        </div>

                        <div class="md:col-span-2 pt-8">
                            <button type="submit" :disabled="settingsForm.processing" class="w-full bg-slate-900 text-white py-6 rounded-[2.5rem] font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-4">
                                <Save class="w-6 h-6 text-indigo-400" /> Save Parameter Configuration
                            </button>
                        </div>
                    </form>

                    <!-- Program Lifecycle Section -->
                    <div class="relative z-10 pt-16 border-t border-slate-50">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-8">Program Lifecycle</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Status Control -->
                            <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-2">Visibility Status</h4>
                                    <p class="text-xs text-slate-500 font-medium leading-relaxed">
                                        Current status: <span :class="course.status === 'published' ? 'text-emerald-600' : 'text-amber-600'" class="font-bold font-black capitalize">{{ course.status || 'Draft' }}</span>.
                                        {{ course.status === 'published' ? 'Your program is live and visible to registered students.' : 'Your program is currently in design mode and hidden from the catalog.' }}
                                    </p>
                                </div>
                                <button @click="toggleStatus" 
                                        :class="course.status === 'published' ? 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100' : 'bg-indigo-600 text-white shadow-xl shadow-indigo-100'"
                                        class="mt-6 w-full py-4 rounded-2xl font-black text-sm transition-all flex items-center justify-center gap-3 border-2 border-transparent">
                                    <Rocket v-if="course.status !== 'published'" class="w-4 h-4" />
                                    <Clock v-else class="w-4 h-4" />
                                    {{ course.status === 'published' ? 'Set to Draft (Unpublish)' : 'Publish to Catalog' }}
                                </button>
                            </div>

                            <!-- Danger Zone -->
                            <div class="bg-rose-50/30 rounded-3xl p-8 border border-rose-100 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-sm font-black text-rose-900 uppercase tracking-widest mb-2">Danger Zone</h4>
                                    <p class="text-xs text-rose-600 font-medium leading-relaxed opacity-70">
                                        Permanently delete this program, including all modules, quizzes, and participant records. This action cannot be reversed.
                                    </p>
                                </div>
                                <button @click="router.delete(route('expert.courses.delete', course.id))" 
                                        class="mt-6 w-full py-4 rounded-2xl font-black text-sm text-rose-600 border-2 border-rose-200 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center gap-3">
                                    <Trash2 class="w-4 h-4" /> Terminate Program
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Architect Modal: Material -->
        <div v-if="showMaterialModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xl z-[100] flex items-center justify-center p-6">
            <div class="bg-white w-full max-w-2xl rounded-[3.5rem] shadow-[0_0_100px_rgba(0,0,0,0.2)] overflow-hidden animate-in zoom-in-95 duration-500 border border-white">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h4 class="text-3xl font-black text-slate-900 tracking-tight">{{ editingMaterial ? 'Assemble Module' : 'Integrate Material' }}</h4>
                        <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-1">Configure learning delivery unit</p>
                    </div>
                    <button @click="closeModal" class="w-12 h-12 hover:bg-white rounded-2xl flex items-center justify-center transition-all border border-transparent hover:border-slate-100 shadow-sm">
                        <Plus class="w-6 h-6 text-slate-400 rotate-45" />
                    </button>
                </div>
                <form @submit.prevent="saveMaterial" class="p-10 space-y-8">
                    <div class="grid grid-cols-2 gap-8">
                        <div class="col-span-2 space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Material Specification</label>
                            <input v-model="materialForm.title" type="text" placeholder="e.g. Introduction to Kirkpatrick Model" 
                                   class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-bold text-slate-700 outline-none shadow-inner" />
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Medium</label>
                            <select v-model="materialForm.type" class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-black text-xs outline-none shadow-inner uppercase tracking-wider">
                                <option value="video">Visual Stream (Video)</option>
                                <option value="text">Literal Narrative (Text)</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Sequence Order</label>
                            <input v-model="materialForm.order_index" type="number" 
                                   class="w-full px-8 py-5 rounded-2xl bg-slate-50 border-none focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-black outline-none shadow-inner" />
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">
                            {{ materialForm.type === 'video' ? 'Source Identifier (ID/URL)' : 'Nucleus Body Content' }}
                        </label>
                        <textarea v-model="materialForm.content" rows="6" 
                                  class="w-full rounded-2xl border-none bg-slate-50 p-8 font-bold text-slate-700 focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all outline-none resize-none shadow-inner" 
                                  :placeholder="materialForm.type === 'video' ? 'Enter YouTube ID or URL' : 'Write your narrative here...'"></textarea>
                    </div>
                    <button type="submit" :disabled="materialForm.processing" class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-lg shadow-2xl hover:bg-emerald-600 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                        <Save class="w-5 h-5 text-emerald-400" /> Commit Module to Studio
                    </button>
                </form>
            </div>
        </div>

        <!-- Architect Modal: Question Bank -->
        <div v-if="showQuestionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xl z-[100] flex items-center justify-center p-6">
            <div class="bg-white w-full max-w-5xl rounded-[3.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-500 border border-white">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h4 class="text-3xl font-black text-slate-900 tracking-tight">{{ editingQuestion ? 'Refine Competency' : 'Forge Question' }}</h4>
                        <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-1">Instrument for understanding calibration</p>
                    </div>
                    <button @click="closeModal" class="w-12 h-12 hover:bg-white rounded-2xl flex items-center justify-center transition-all border border-transparent hover:border-slate-100 shadow-sm">
                        <Plus class="w-6 h-6 text-slate-400 rotate-45" />
                    </button>
                </div>
                <form @submit.prevent="saveQuestion" class="p-10 space-y-10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <div class="md:col-span-2 space-y-10">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Competency Query</label>
                                <textarea v-model="questionForm.question_text" rows="4" 
                                          class="w-full rounded-2xl border-none bg-slate-50 p-8 font-bold text-slate-900 focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all outline-none resize-none shadow-inner text-xl" 
                                          placeholder="State the core evaluation question..."></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-6">
                                <template v-for="(opt, idx) in questionForm.options" :key="idx">
                                    <div class="group space-y-2">
                                        <div class="flex items-center justify-between px-2">
                                            <label class="block text-[10px] font-black text-slate-300 group-focus-within:text-indigo-400 uppercase tracking-widest">Strategic Option {{ opt.key.toUpperCase() }}</label>
                                            <input type="radio" :value="opt.key" v-model="questionForm.correct_answer" class="text-emerald-500 focus:ring-emerald-500" />
                                        </div>
                                        <input v-model="opt.text" type="text" placeholder="Value..." 
                                               class="w-full px-6 py-4 rounded-xl bg-slate-50 border-none focus:ring-4 focus:ring-indigo-50 focus:bg-white transition-all font-bold text-slate-700 outline-none shadow-inner" />
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <div class="space-y-8">
                            <div class="bg-slate-50/50 p-8 rounded-[2.5rem] border border-slate-100 shadow-inner space-y-8">
                                <div class="space-y-3">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Mastery Phase</label>
                                    <select v-model="questionForm.type" class="w-full px-6 py-4 rounded-xl bg-white border-none focus:ring-4 focus:ring-indigo-100 transition-all font-black text-xs outline-none shadow-sm uppercase tracking-wider">
                                        <option value="pretest">Diagnostic (Pre)</option>
                                        <option value="posttest">Mastery (Post)</option>
                                    </select>
                                </div>
                                <div class="space-y-3">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Score Weight</label>
                                    <input v-model.number="questionForm.weight" type="number" min="1" max="20" class="w-full px-6 py-4 rounded-xl bg-white border-none focus:ring-4 focus:ring-indigo-100 transition-all font-black text-xs outline-none shadow-sm" />
                                </div>
                                <div class="space-y-3">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Competency Tags</label>
                                    <div class="flex flex-col gap-2">
                                        <label v-for="tag in ['Technical Skill', 'Conceptual', 'Safety', 'Communication', 'Leadership', 'Problem Solving']" :key="tag" class="flex items-center gap-3 bg-white p-3 rounded-xl shadow-sm border border-slate-100 cursor-pointer hover:border-indigo-200 transition-colors">
                                            <input type="checkbox" :value="tag" v-model="questionForm.competency_tags" class="text-indigo-600 focus:ring-indigo-500 rounded" />
                                            <span class="text-xs font-bold text-slate-700">{{ tag }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" :disabled="questionForm.processing" class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                                <Save class="w-6 h-6 text-indigo-400" /> Commit to Bank
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
