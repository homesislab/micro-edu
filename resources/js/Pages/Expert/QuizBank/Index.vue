<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Plus, 
    Search, 
    Target, 
    Activity, 
    Award, 
    MoreVertical, 
    ArrowRight,
    Sparkles,
    Loader2,
    Check,
    X as CloseIcon
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    templates: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const currentTemplate = ref(null);

const form = useForm({
    title: '',
    sub_type: 'quiz',
    assessment_mode: 'diagnostic',
    passing_grade: 75,
    content: []
});

const showAIForgeModal = ref(false);
const showAIPreviewModal = ref(false);
const aiPreviewData = ref(null);
const aiForgeForm = useForm({
    prompt: ''
});

const isForgingPreview = ref(false);

const forgePreview = async () => {
    isForgingPreview.value = true;
    try {
        const response = await axios.post(route('expert.ai.generate-quiz-template'), {
            preview: true,
            prompt: aiForgeForm.prompt
        });
        aiPreviewData.value = response.data;
        showAIForgeModal.value = false;
        showAIPreviewModal.value = true;
    } catch (e) {
        console.error('Forge preview failed', e);
        alert('Failed to generate preview. Please try again.');
    } finally {
        isForgingPreview.value = false;
    }
};

const commitForge = () => {
    if (!aiPreviewData.value) return;
    
    useForm(aiPreviewData.value).post(route('expert.ai.generate-quiz-template'), {
        onSuccess: () => {
            showAIPreviewModal.value = false;
            aiPreviewData.value = null;
            aiForgeForm.reset();
        }
    });
};

const openModal = (template = null) => {
    isEditing.value = !!template;
    currentTemplate.value = template;
    if (template) {
        form.title = template.title;
        form.sub_type = template.sub_type;
        form.assessment_mode = template.assessment_mode;
        form.passing_grade = template.passing_grade;
        form.content = template.content || [];
    } else {
        form.reset();
    }
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.patch(route('expert.quiz-bank.update', currentTemplate.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('expert.quiz-bank.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const deleteTemplate = (id) => {
    if (confirm('Are you sure you want to delete this template?')) {
        form.delete(route('expert.quiz-bank.delete', id));
    }
};

const getIcon = (sub_type) => {
    if (sub_type === 'pre_test') return Activity;
    if (sub_type === 'final_exam') return Award;
    return Target;
};

const getColorClass = (sub_type) => {
    if (sub_type === 'pre_test') return 'text-yellow-500 bg-yellow-50';
    if (sub_type === 'final_exam') return 'text-amber-600 bg-amber-50';
    return 'text-emerald-500 bg-emerald-50';
};
</script>

<template>
    <Head title="Quiz Bank" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">Quiz Bank</h1>
                    <p class="text-slate-400 mt-2 font-bold uppercase tracking-widest text-xs">Manage reusable assessments</p>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="showAIForgeModal = true" 
                            class="px-8 py-4 bg-white border-2 border-slate-100 text-indigo-600 rounded-3xl font-black flex items-center gap-3 hover:border-indigo-100 shadow-sm transition-all whitespace-nowrap">
                        <Sparkles class="w-5 h-5" />
                        <span>Forge with AI</span>
                    </button>
                    <button @click="openModal()" 
                            class="px-8 py-4 bg-slate-900 text-white rounded-3xl font-black flex items-center gap-3 hover:scale-105 active:scale-95 transition-all shadow-xl shadow-slate-200 whitespace-nowrap">
                        <Plus class="w-5 h-5" />
                        <span>Create Template</span>
                    </button>
                </div>
            </div>

            <div v-if="templates.length === 0" 
                 class="bg-white rounded-[3rem] p-20 border-2 border-dashed border-slate-100 flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                    <FileText class="w-10 h-10 text-slate-200" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2">No templates yet</h3>
                <p class="text-slate-400 max-w-sm font-bold">Start by creating your first reusable quiz template to save time in Architect Studio.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="template in templates" :key="template.id" 
                     @click="openModal(template)"
                     class="group bg-white rounded-[2.5rem] p-8 border border-slate-100 hover:border-slate-200 hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500 cursor-pointer">
                    <div class="flex justify-between items-start mb-6">
                        <div :class="[getColorClass(template.sub_type), 'p-4 rounded-2xl']">
                            <component :is="getIcon(template.sub_type)" class="w-6 h-6" />
                        </div>
                        <div class="flex gap-2">
                            <button @click.stop="deleteTemplate(template.id)" class="p-2 hover:bg-red-50 rounded-xl transition-all group/del">
                                <Trash2 class="w-4 h-4 text-slate-400 group-hover/del:text-red-500" />
                            </button>
                        </div>
                    </div>

                    <h3 class="text-xl font-black text-slate-900 mb-2 group-hover:text-indigo-600 transition-colors">{{ template.title }}</h3>
                    
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="px-3 py-1 bg-slate-50 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-widest">
                            {{ template.sub_type.replace('_', ' ') }}
                        </span>
                        <span class="px-3 py-1 bg-slate-50 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-widest">
                            {{ template.assessment_mode }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between mt-8 pt-6 border-t border-slate-50">
                        <div class="text-left">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Passing Grade</p>
                            <p class="text-lg font-black text-slate-900">{{ template.passing_grade }}%</p>
                        </div>
                        <ArrowRight class="w-5 h-5 text-slate-200 group-hover:text-indigo-600 group-hover:translate-x-2 transition-all" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Creating/Editing Template -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
                
                <div class="relative w-full max-w-2xl bg-white rounded-[3rem] shadow-2xl p-12 overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="flex justify-between items-start mb-10 shrink-0">
                        <div>
                            <h2 class="text-3xl font-black text-slate-900">{{ isEditing ? 'Template Details' : 'New Template' }}</h2>
                            <p class="text-slate-400 mt-2 font-bold uppercase tracking-widest text-[10px]">Configure assessment parameters</p>
                        </div>
                        <button @click="closeModal" class="p-3 bg-slate-50 rounded-2xl hover:bg-slate-100 transition-all">
                            <CloseIcon class="w-5 h-5 text-slate-400" />
                        </button>
                    </div>

                    <div class="overflow-y-auto pr-4 custom-scrollbar flex-1">
                        <form @submit.prevent="submit" class="space-y-10">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Quiz Title</label>
                            <input v-model="form.title" type="text" 
                                   class="w-full px-6 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-900" 
                                   placeholder="e.g. Fundamental SEO Certification">
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Assessment Type</label>
                                <select v-model="form.sub_type" 
                                        class="w-full px-6 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-900">
                                    <option value="pre_test">Pre-Test</option>
                                    <option value="quiz">Module Quiz</option>
                                    <option value="final_exam">Final Exam</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Evaluation Mode</label>
                                <select v-model="form.assessment_mode" 
                                        class="w-full px-6 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-900">
                                    <option value="diagnostic">Diagnostic (Diagnostic)</option>
                                    <option value="mastery">Mastery (Lock Progress)</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="form.assessment_mode === 'mastery'">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Passing Grade (%)</label>
                            <input v-model="form.passing_grade" type="number" 
                                   class="w-full px-6 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-900">
                        </div>

                        <!-- Questions Preview/Detail -->
                        <div v-if="isEditing && form.content && form.content.length > 0" class="pt-8 border-t border-slate-100">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-6 px-2">Assessment Content ({{ form.content.length }} Questions)</label>
                            <div class="space-y-4">
                                <div v-for="(q, qIdx) in form.content" :key="qIdx" class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div class="flex justify-between items-start mb-4">
                                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-lg">Question {{ qIdx + 1 }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-slate-900 mb-4 leading-relaxed">{{ q.question_text }}</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div v-for="opt in q.options" :key="opt.key" 
                                             :class="opt.key === q.correct_answer ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-white border-slate-100 text-slate-400'"
                                             class="p-3 rounded-xl border text-[10px] font-bold flex items-center gap-2">
                                            <span class="w-5 h-5 flex items-center justify-center rounded-lg bg-slate-50 border border-current/10 uppercase">{{ opt.key }}</span>
                                            {{ opt.text }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sticky bottom-0 bg-white pt-6 flex gap-4 mt-10">
                            <button type="submit" 
                                    class="flex-1 bg-slate-900 text-white rounded-3xl py-4 font-black shadow-xl shadow-slate-200 hover:scale-105 active:scale-95 transition-all">
                                {{ isEditing ? 'Save Changes' : 'Create Template' }}
                            </button>
                            <button type="button" @click="closeModal" 
                                    class="px-8 py-4 bg-slate-50 text-slate-400 rounded-3xl font-black hover:bg-slate-100 transition-all">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <!-- AI Forge Modal -->
        <div v-if="showAIForgeModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-indigo-900/40 backdrop-blur-sm transition-opacity" @click="showAIForgeModal = false"></div>
                
                <div class="relative w-full max-w-xl bg-white rounded-[3rem] shadow-2xl p-12 overflow-hidden border border-indigo-50">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-16 -mt-16 blur-3xl opacity-50"></div>
                    
                        <div class="relative z-10 flex justify-between items-start mb-10">
                            <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                                <Sparkles class="w-4 h-4" /> AI Forge
                            </div>
                            <h2 class="text-3xl font-black text-slate-900">Forge Assessment</h2>
                            <p class="text-slate-400 mt-2 font-bold leading-relaxed">Tell the AI what topic or competency you want to measure.</p>
                        </div>

                        <form @submit.prevent="forgePreview" class="relative z-10 space-y-8">
                            <div>
                                <textarea v-model="aiForgeForm.prompt" 
                                          rows="4"
                                          class="w-full px-8 py-6 bg-slate-50 border-0 rounded-[2rem] focus:ring-4 focus:ring-indigo-50 font-bold text-slate-900 resize-none transition-all outline-none" 
                                          placeholder="e.g. Generate a mastery-mode quiz for Project Management Fundamentals focusing on Agile methodologies..."></textarea>
                                <p class="text-[10px] text-slate-400 font-bold mt-4 px-2 uppercase tracking-widest">PROMPT TIP: Mention the difficulty level and target audience for better results.</p>
                            </div>

                            <div class="pt-6 flex gap-4">
                                <button type="submit" 
                                        :disabled="isForgingPreview || !aiForgeForm.prompt"
                                        class="flex-1 bg-indigo-600 text-white rounded-3xl py-4 font-black shadow-xl shadow-indigo-100 hover:bg-slate-900 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:scale-100">
                                    <Loader2 v-if="isForgingPreview" class="w-5 h-5 animate-spin" />
                                    <Sparkles v-else class="w-5 h-5" />
                                    <span>{{ isForgingPreview ? 'Forging Architecture...' : 'Generate Preview' }}</span>
                                </button>
                                <button type="button" @click="showAIForgeModal = false" 
                                        class="px-8 py-4 bg-slate-50 text-slate-400 rounded-3xl font-black hover:bg-slate-100 transition-all">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <!-- AI Preview Modal -->
        <div v-if="showAIPreviewModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showAIPreviewModal = false"></div>
                
                <div class="relative w-full max-w-2xl bg-white rounded-[3rem] shadow-2xl p-12 overflow-hidden">
                    <div class="flex justify-between items-start mb-10">
                        <div>
                            <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                                <Sparkles class="w-4 h-4" /> Review Architecture
                            </div>
                            <h2 class="text-3xl font-black text-slate-900">{{ aiPreviewData?.title }}</h2>
                            <div class="flex gap-2 mt-4">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                    {{ aiPreviewData?.sub_type.replace('_', ' ') }}
                                </span>
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                    {{ aiPreviewData?.assessment_mode }} Mode
                                </span>
                                <span class="px-3 py-1 bg-slate-50 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                    {{ aiPreviewData?.passing_grade }}% Pass Grade
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 max-h-[50vh] overflow-y-auto pr-4 custom-scrollbar">
                        <div v-for="(q, idx) in aiPreviewData?.content" :key="idx" class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Question {{ idx + 1 }}</p>
                            <p class="text-slate-900 font-bold mb-4">{{ q.question_text }}</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="opt in q.options" :key="opt.key" 
                                     :class="opt.key === q.correct_answer ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-white border-slate-100 text-slate-500'"
                                     class="p-3 rounded-xl border text-[11px] font-bold flex items-center gap-2">
                                    <span class="uppercase w-5 h-5 flex items-center justify-center rounded-lg bg-white/50 border border-current/20">{{ opt.key }}</span>
                                    {{ opt.text }}
                                    <Check v-if="opt.key === q.correct_answer" class="w-3 h-3 ml-auto text-emerald-500" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-10 flex gap-4 mt-6 border-t border-slate-50">
                        <button @click="commitForge" 
                                class="flex-1 bg-slate-900 text-white rounded-3xl py-4 font-black shadow-xl shadow-slate-200 hover:bg-emerald-600 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-3">
                            <Check class="w-5 h-5" />
                            <span>Commit to Bank</span>
                        </button>
                        <button @click="showAIPreviewModal = false; showAIForgeModal = true" 
                                class="px-8 py-4 bg-slate-50 text-slate-400 rounded-3xl font-black hover:bg-slate-100 transition-all">
                            Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
