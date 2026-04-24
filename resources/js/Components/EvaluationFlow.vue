<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import { 
    CheckCircle2, 
    Circle, 
    BookOpen, 
    FileText, 
    Star, 
    UploadCloud, 
    Link as LinkIcon, 
    ArrowRight, 
    Check,
    Hourglass,
    Trophy,
    Lock,
    Key,
    ShieldCheck,
    ClipboardCheck,
    PlayCircle,
    Zap,
    Camera
} from 'lucide-vue-next';

const props = defineProps({
    enrollment: Object,
});

const questions = ref([]);
const loading = ref(false);

const testForm = useForm({
    answers: {},
});

const feedbackForm = useForm({
    rating: 0,
    review: '',
});

const assignmentForm = useForm({
    submission_type: 'file',
    file: null,
    link_url: '',
    description: '',
});

const attendanceForm = useForm({
    code: '',
});

const filePreview = ref(null);

const handleFileInput = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    assignmentForm.file = file;
    if (file.type.startsWith('image/')) {
        filePreview.value = URL.createObjectURL(file);
    } else {
        filePreview.value = null;
    }
};

const isHighScorer = computed(() => (props.enrollment?.pretest_score ?? 0) >= 90);

const fastTrackForm = useForm({});
const activateFastTrack = () => {
    fastTrackForm.post(route('evaluation.fastTrack', props.enrollment.id));
};

const fetchQuestions = async (type) => {
    loading.value = true;
    try {
        const response = await axios.get(route('courses.questions', { course: props.enrollment.course_id, type }));
        questions.value = response.data;
    } catch (error) {
        console.error('Failed to fetch questions');
    } finally {
        loading.value = false;
    }
};

const submitTest = (type) => {
    testForm.post(route('evaluation.submitTest', { enrollment: props.enrollment.id, type }), {
        onSuccess: () => {
            questions.value = [];
            testForm.reset();
        }
    });
};

const submitFeedback = () => {
    feedbackForm.post(route('evaluation.submitFeedback', props.enrollment.id));
};

const submitAssignment = () => {
    assignmentForm.post(route('evaluation.submitAssignment', props.enrollment.id));
};

const submitAttendance = () => {
    attendanceForm.post(route('evaluation.claimAttendance', props.enrollment.id), {
        onSuccess: () => attendanceForm.reset(),
    });
};

const isStepDone = (stepStatus) => {
    const sequence = ['enrolled', 'pre_test_done', 'content_done', 'attended', 'post_test_done', 'l1_done', 'l3_submitted', 'completed'];
    return sequence.indexOf(props.enrollment?.status) > sequence.indexOf(stepStatus);
};

const isStepActive = (stepStatus) => props.enrollment?.status === stepStatus;

const steps = [
    { key: 'enrolled', label: 'Pre-test', icon: FileText },
    { key: 'pre_test_done', label: 'Materi', icon: BookOpen },
    { key: 'content_done', label: 'Post-test', icon: ClipboardCheck },
    { key: 'post_test_done', label: 'Feedback', icon: Star },
    { key: 'l1_done', label: 'Assignment', icon: UploadCloud },
    { key: 'l3_submitted', label: 'Completed', icon: Trophy },
];

onMounted(() => {
    if (props.enrollment?.status === 'enrolled') {
        fetchQuestions('pretest');
    } else if (props.enrollment?.status === 'attended') {
        fetchQuestions('posttest');
    }
});

// Watch for status changes caused by Inertia prop updates (no remount happens)
watch(
    () => props.enrollment?.status,
    (newStatus) => {
        if (!newStatus) return;
        if (newStatus === 'attended' || newStatus === 'fast_tracked') {
            fetchQuestions('posttest');
        } else if (newStatus === 'enrolled') {
            fetchQuestions('pretest');
        }
    }
);
</script>

<template>
    <div class="space-y-12">
        <!-- Modern Horizontal Stepper: The Professional Roadmap -->
        <nav aria-label="Progress" class="relative py-8">
            <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 transition-all duration-1000 ease-out" 
                     :style="{ width: (steps.findIndex(s => s.key === enrollment?.status) / (steps.length - 1)) * 100 + '%' }"></div>
            </div>
            
            <ol role="list" class="relative flex items-center justify-between gap-4 max-w-5xl mx-auto">
                <li v-for="(step, index) in steps" :key="step.key" class="flex-1 flex flex-col items-center group">
                    <div class="relative">
                        <!-- Outer Glow for Active Step -->
                        <div v-if="isStepActive(step.key)" class="absolute inset-0 bg-indigo-500/20 blur-xl rounded-full animate-pulse"></div>
                        
                        <div :class="[
                            isStepDone(step.key) ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-100' : 
                            isStepActive(step.key) ? 'bg-slate-900 border-slate-900 text-white shadow-2xl scale-125 ring-8 ring-indigo-50' : 
                            'bg-white border-slate-200 text-slate-400'
                        ]" class="w-12 h-12 rounded-[1.25rem] border-2 flex items-center justify-center transition-all duration-700 relative z-10">
                            <Check v-if="isStepDone(step.key)" class="w-6 h-6" />
                            <component v-else :is="step.icon" class="w-5 h-5" />
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <p :class="[
                            isStepActive(step.key) ? 'text-slate-900 font-black' : 'text-slate-400 font-bold'
                        ]" class="text-[10px] tracking-[0.2em] uppercase transition-colors duration-500">
                            {{ step.label }}
                        </p>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Dynamic Content Area -->
        <div class="relative min-h-[500px]">
            
            <!-- 1. PRE-TEST -->
            <div v-if="enrollment.status === 'enrolled'" class="bg-white rounded-[3.5rem] p-12 md:p-16 shadow-2xl shadow-indigo-100/50 border border-white animate-in slide-in-from-bottom-10 duration-1000 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 -mr-20 -mt-20"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8 mb-16">
                    <div>
                        <div class="flex items-center gap-3 text-indigo-600 font-black text-xs uppercase tracking-[0.3em] mb-4">
                            <span class="w-8 h-px bg-indigo-600"></span> Diagnostic Assessment
                        </div>
                        <h4 class="text-4xl font-black text-slate-900 tracking-tight leading-none">Baseline Knowledge.</h4>
                        <p class="text-slate-500 mt-4 text-lg font-medium">This assessment calibrates the system to your current expertise level.</p>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                            <Hourglass class="text-indigo-600 w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Est. Time</p>
                            <p class="text-sm font-black text-slate-900">~ 5 Minutes</p>
                        </div>
                    </div>
                </div>

                <div v-if="loading" class="space-y-8">
                    <div v-for="i in 3" :key="i" class="h-48 bg-slate-50 rounded-[2.5rem] animate-pulse"></div>
                </div>

                <form v-else @submit.prevent="submitTest('pretest')" class="space-y-12">
                    <div v-for="(q, idx) in questions" :key="q.id" class="group relative bg-slate-50/50 p-10 rounded-[2.5rem] border border-transparent transition-all duration-500 hover:bg-white hover:border-indigo-100 hover:shadow-xl hover:shadow-indigo-100/30">
                        <div class="flex gap-6 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center shrink-0 shadow-sm font-black text-indigo-600 text-lg group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                {{ idx + 1 }}
                            </div>
                            <p class="font-black text-slate-900 text-2xl leading-tight pt-1">{{ q.question_text }}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <label v-for="opt in q.options" :key="opt.key" 
                                   class="premium-option relative flex items-center p-6 rounded-[1.75rem] border-2 cursor-pointer transition-all duration-500 bg-white"
                                   :class="testForm.answers[q.id] === opt.key ? 'border-indigo-500 ring-8 ring-indigo-50 shadow-lg' : 'border-slate-100 hover:border-indigo-200 shadow-sm'">
                                <input type="radio" :name="'q'+q.id" :value="opt.key" v-model="testForm.answers[q.id]" class="sr-only" />
                                <div :class="testForm.answers[q.id] === opt.key ? 'bg-indigo-600 border-indigo-600' : 'bg-slate-100 border-slate-200'"
                                     class="w-7 h-7 rounded-xl border-2 flex items-center justify-center mr-5 transition-all">
                                    <Check v-if="testForm.answers[q.id] === opt.key" class="w-4 h-4 text-white" />
                                </div>
                                <span class="text-slate-700 font-bold text-lg leading-tight">{{ opt.text }}</span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" :disabled="testForm.processing" class="w-full py-6 bg-slate-900 text-white rounded-3xl font-black text-xl shadow-2xl hover:bg-indigo-600 transition-all hover:-translate-y-1 active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3">
                        Commit Assessment <ArrowRight class="w-6 h-6" />
                    </button>
                </form>
            </div>

            <!-- 2. CONTENT (Study Session) -->
            <div v-if="enrollment.status === 'pre_test_done'" class="glass-card rounded-[2.5rem] p-14 text-center animate-in zoom-in duration-700">
                <div class="max-w-2xl mx-auto">
                    <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner shadow-indigo-100/50">
                        <BookOpen class="text-indigo-600 w-10 h-10" />
                    </div>
                    <h4 class="text-3xl font-black text-slate-900 mb-4">Mastering the Core Materials</h4>
                    <p class="text-slate-500 text-lg font-medium leading-relaxed mb-10">
                        Your pre-test score of <span class="text-indigo-600 font-black">{{ enrollment?.pretest_score || 0 }}%</span> has been logged. 
                        Now, please engage with the course materials before moving to the final evaluation.
                    </p>

                    <!-- Adaptive Learning: Fast Track if score >= 90 -->
                    <div v-if="isHighScorer" class="mb-10 bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-[2.5rem] p-10 text-left relative overflow-hidden">
                        <div class="absolute top-4 right-4">
                            <span class="flex items-center gap-1 bg-amber-500 text-white text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                                <Zap class="w-3 h-3" /> Adaptive
                            </span>
                        </div>
                        <Zap class="w-10 h-10 text-amber-500 mb-4" />
                        <h5 class="text-2xl font-black text-slate-900 mb-2">Fast Track Available!</h5>
                        <p class="text-slate-600 font-medium mb-6 leading-relaxed">
                            Your pre-test score of <strong class="text-amber-600">{{ enrollment?.pretest_score || 0 }}%</strong> shows you already have strong foundational knowledge. 
                            You can skip the materials and go directly to the post-test — or continue for enrichment.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button @click="activateFastTrack" :disabled="fastTrackForm.processing"
                                    class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-amber-500 text-white rounded-2xl font-black hover:bg-amber-600 transition-all active:scale-95 shadow-lg shadow-amber-200">
                                <Zap class="w-5 h-5" /> Skip to Post-Test
                            </button>
                            <button @click="router.post(route('dashboard'), { action: 'simulate_finish_content' })"
                                    class="flex-1 py-3.5 bg-white text-slate-600 border border-slate-200 rounded-2xl font-black hover:bg-slate-50 transition-all">
                                Continue with Materials
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-8 bg-slate-900 rounded-3xl text-left relative overflow-hidden group mb-10">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent"></div>
                        <div class="relative z-10 flex items-start gap-5">
                            <PlayCircle class="text-white/40 w-8 h-8 flex-shrink-0" />
                            <div>
                                <p class="text-white/60 text-xs font-black uppercase tracking-widest mb-2">Active Module</p>
                                <p class="text-white text-xl font-bold leading-tight">Advanced Implementation of Behavior Assessment Platforms.</p>
                            </div>
                        </div>
                    </div>

                    <button @click="router.post(route('dashboard'), { action: 'simulate_finish_content' })"
                            class="bg-indigo-600 text-white px-10 py-5 rounded-[2rem] font-black text-lg shadow-2xl shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95">
                        Mark Content as Completed
                    </button>
                </div>
            </div>

            <!-- 2.5 SESSION UNLOCK -->
            <div v-if="enrollment.status === 'content_done'" class="max-w-2xl mx-auto animate-in zoom-in duration-700">
                <div class="bg-slate-900 rounded-[3.5rem] p-16 shadow-2xl relative overflow-hidden text-center">
                    <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/10 to-transparent"></div>
                    <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <div class="w-24 h-24 bg-white/10 backdrop-blur-md rounded-[2rem] flex items-center justify-center mx-auto mb-10 border border-white/10 shadow-2xl">
                            <Lock class="text-white w-10 h-10" />
                        </div>
                        <h4 class="text-4xl font-black text-white mb-4 tracking-tight">Unlock Evaluation.</h4>
                        <p class="text-indigo-100/60 font-medium mb-12 text-lg">
                            Please enter the <span class="text-white font-black">Attendance Claim Code</span> shared by your expert <br/> at the end of the live session.
                        </p>

                        <form @submit.prevent="submitAttendance" class="max-w-md mx-auto space-y-8">
                            <div class="relative group">
                                <Key class="absolute left-8 top-1/2 -translate-y-1/2 text-white/30 w-6 h-6 group-focus-within:text-indigo-400 transition-colors" />
                                <input v-model="attendanceForm.code" type="text" placeholder="CLAIM-CODE-2026" 
                                       class="w-full pl-20 pr-8 py-6 rounded-[2.5rem] border-white/10 bg-white/5 font-black tracking-[0.2em] uppercase text-white text-xl focus:ring-8 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-center placeholder:text-white/20" />
                            </div>
                            <button type="submit" :disabled="attendanceForm.processing"
                                    class="w-full py-6 bg-white text-slate-900 rounded-[2.5rem] font-black text-lg shadow-2xl hover:bg-indigo-400 hover:text-white transition-all active:scale-95 disabled:opacity-30 flex items-center justify-center gap-3">
                                <ShieldCheck class="w-6 h-6" /> Authorize Session Access
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3. POST-TEST -->
            <div v-if="enrollment.status === 'attended'" class="glass-card rounded-[2.5rem] p-10 md:p-14 animate-in fade-in slide-in-from-bottom-6 duration-700">
                <div class="flex items-center gap-4 mb-10">
                    <div class="bg-indigo-600 w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
                        <ClipboardCheck class="text-white w-6 h-6" />
                    </div>
                    <div>
                        <h4 class="text-2xl font-black text-slate-900">Step 3: Learning Mastery Evaluation</h4>
                        <p class="text-slate-500 font-medium">Let's see how much your knowledge has improved after the training.</p>
                    </div>
                </div>

                <form @submit.prevent="submitTest('posttest')" class="space-y-8">
                    <div v-for="(q, idx) in questions" :key="q.id" class="bg-slate-50/50 p-8 rounded-3xl border border-slate-100/50">
                        <p class="font-black text-slate-900 text-lg mb-6 leading-relaxed">{{ idx + 1 }}. {{ q.question_text }}</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label v-for="opt in q.options" :key="opt.key" 
                                   class="relative flex items-center p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 shadow-sm"
                                   :class="testForm.answers[q.id] === opt.key ? 'border-indigo-500 bg-white ring-4 ring-indigo-50' : 'border-transparent bg-white'">
                                <input type="radio" :name="'q'+q.id" :value="opt.key" v-model="testForm.answers[q.id]" class="sr-only" />
                                <div :class="testForm.answers[q.id] === opt.key ? 'bg-indigo-600 border-indigo-600' : 'bg-slate-100 border-slate-200'"
                                     class="w-6 h-6 rounded-lg border-2 flex items-center justify-center mr-4">
                                    <Check v-if="testForm.answers[q.id] === opt.key" class="w-4 h-4 text-white" />
                                </div>
                                <span class="text-slate-700 font-bold">{{ opt.text }}</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="w-full btn-premium btn-primary py-5 text-lg">
                        Finalize Score Calculation
                    </button>
                </form>
            </div>

            <!-- 4. FEEDBACK (L1) -->
            <div v-if="enrollment.status === 'post_test_done'" class="max-w-3xl mx-auto bg-white rounded-[4rem] p-16 shadow-2xl border border-indigo-50 animate-in zoom-in-95 duration-1000 relative overflow-hidden">
                <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-50/50 rounded-full blur-[100px]"></div>
                
                <div class="relative z-10 text-center mb-16">
                    <div class="flex items-center justify-center gap-3 text-indigo-600 font-black text-xs uppercase tracking-[0.3em] mb-4">
                        <span class="w-12 h-px bg-indigo-600"></span> Kirkpatrick Level 1
                    </div>
                    <h4 class="text-5xl font-black text-slate-900 tracking-tight leading-none mb-4">Reaction & Value.</h4>
                    <p class="text-slate-500 font-medium text-lg leading-relaxed max-w-lg mx-auto">Your perception of this experience drives <br/> our continuous improvement.</p>
                </div>

                <form @submit.prevent="submitFeedback" class="relative z-10 space-y-16">
                    <div class="flex flex-col items-center gap-8">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Rate your overall experience</p>
                        <div class="flex justify-center gap-6 flex-row-reverse">
                            <template v-for="i in [5,4,3,2,1]" :key="i">
                                <input type="radio" :id="'star'+i" :value="i" v-model="feedbackForm.rating" class="sr-only" />
                                <label :for="'star'+i" class="cursor-pointer transition-all hover:scale-125 duration-300 transform group"
                                       :class="feedbackForm.rating >= i ? 'text-indigo-600 scale-110 drop-shadow-xl' : 'text-slate-100'">
                                    <Star class="w-16 h-16 fill-current group-hover:drop-shadow-lg" />
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <label class="block text-xs font-black text-slate-900 uppercase tracking-[0.2em] px-4">Specific Insights</label>
                        <textarea v-model="feedbackForm.review" rows="6" 
                                  class="w-full rounded-[2.5rem] border-2 border-slate-50 bg-slate-50 focus:border-indigo-500 focus:bg-white focus:ring-[16px] focus:ring-indigo-50 transition-all p-10 text-slate-700 font-bold text-lg outline-none shadow-inner" 
                                  placeholder="What was the most transformative moment for you?"></textarea>
                    </div>

                    <button type="submit" :disabled="!feedbackForm.rating"
                            class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-xl shadow-2xl hover:bg-emerald-600 transition-all hover:-translate-y-1 active:scale-95 disabled:opacity-30">
                        Finalize Reaction Report
                    </button>
                </form>
            </div>

            <!-- 5. ASSIGNMENT (L3) -->
            <div v-if="enrollment.status === 'l1_done'" class="bg-white rounded-[3.5rem] p-12 md:p-16 shadow-2xl border border-white animate-in slide-in-from-bottom-10 duration-1000 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-50 rounded-full blur-[100px] opacity-40 -mr-20 -mt-20"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8 mb-16">
                    <div>
                        <div class="flex items-center gap-3 text-indigo-600 font-black text-xs uppercase tracking-[0.3em] mb-4">
                            <span class="w-8 h-px bg-indigo-600"></span> Behavior Implementation
                        </div>
                        <h4 class="text-4xl font-black text-slate-900 tracking-tight leading-none">Practice & Mastery.</h4>
                        <p class="text-slate-500 mt-4 text-lg font-medium">Demonstrate how you've applied these professional concepts in your actual ecosystem.</p>
                    </div>
                </div>

                <form @submit.prevent="submitAssignment" class="relative z-10 grid grid-cols-1 lg:grid-cols-5 gap-12">
                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-slate-50/50 p-8 rounded-[2.5rem] border border-slate-100/50 flex flex-col gap-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4">Evidence Protocol</p>
                            <div class="flex flex-col gap-4">
                                <button type="button" @click="assignmentForm.submission_type = 'file'" 
                                        :class="assignmentForm.submission_type === 'file' ? 'bg-slate-900 text-white shadow-xl translate-x-1' : 'bg-white text-slate-500' "
                                        class="flex items-center gap-4 px-8 py-5 rounded-2xl font-black text-sm transition-all border border-slate-100 shadow-sm group">
                                    <FileText class="w-5 h-5 group-hover:scale-110 transition-transform" /> Document Upload
                                </button>
                                <button type="button" @click="assignmentForm.submission_type = 'link'" 
                                        :class="assignmentForm.submission_type === 'link' ? 'bg-slate-900 text-white shadow-xl translate-x-1' : 'bg-white text-slate-500'"
                                        class="flex items-center gap-4 px-8 py-5 rounded-2xl font-black text-sm transition-all border border-slate-100 shadow-sm group">
                                    <LinkIcon class="w-5 h-5 group-hover:scale-110 transition-transform" /> Portfolio Link
                                </button>
                            </div>
                        </div>

                        <div v-if="assignmentForm.submission_type === 'file'" class="space-y-4">
                            <!-- Mobile: Camera capture button -->
                            <div class="flex gap-3">
                                <div class="relative flex-1">
                                    <input type="file" @change="handleFileInput" id="file-upload" accept="image/*,application/pdf,.doc,.docx" class="hidden" />
                                    <label for="file-upload" class="cursor-pointer flex items-center justify-center gap-3 p-5 rounded-[2rem] border-2 border-dashed border-indigo-200 bg-indigo-50/30 hover:bg-white hover:border-indigo-400 transition-all font-black text-slate-700 text-sm">
                                        <UploadCloud class="text-indigo-600 w-6 h-6 flex-shrink-0" />
                                        <span class="truncate">{{ assignmentForm.file ? assignmentForm.file.name : 'Browse File' }}</span>
                                    </label>
                                </div>
                                <!-- Camera shortcut for mobile -->
                                <div class="relative">
                                    <input type="file" @change="handleFileInput" id="camera-capture" accept="image/*" capture="environment" class="hidden" />
                                    <label for="camera-capture" class="cursor-pointer w-16 h-full flex items-center justify-center rounded-[2rem] bg-slate-900 text-white hover:bg-indigo-600 transition-all">
                                        <Camera class="w-6 h-6" />
                                    </label>
                                </div>
                            </div>
                            <!-- Image preview -->
                            <div v-if="filePreview" class="relative rounded-[2rem] overflow-hidden border-2 border-indigo-100">
                                <img :src="filePreview" class="w-full max-h-48 object-cover" alt="Preview" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent flex items-end p-4">
                                    <span class="text-white text-xs font-black">{{ assignmentForm.file?.name }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-else class="bg-white p-10 rounded-[3rem] border-2 border-slate-50 shadow-inner">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 px-2">External Portfolio URL</label>
                            <div class="relative">
                                <LinkIcon class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 w-5 h-5" />
                                <input v-model="assignmentForm.link_url" type="url" placeholder="https://portfolio.me/project" 
                                       class="w-full pl-16 pr-6 py-5 rounded-2xl border-none bg-slate-50 font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 focus:bg-white transition-all outline-none" />
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-3 space-y-10">
                        <div class="bg-white p-10 rounded-[3rem] border-2 border-slate-50 shadow-inner">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 px-2">Implementation Narrative</label>
                            <textarea v-model="assignmentForm.description" rows="10" 
                                      class="w-full rounded-2xl border-none bg-slate-50 p-8 font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 focus:bg-white transition-all outline-none resize-none" 
                                      placeholder="Describe the real-world impact of your implementation..."></textarea>
                        </div>
                        <button type="submit" :disabled="assignmentForm.processing" class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-xl shadow-2xl hover:bg-indigo-600 transition-all hover:-translate-y-1 active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3">
                            <ShieldCheck class="w-7 h-7" /> Submit for Professional Certification
                        </button>
                    </div>
                </form>
            </div>

            <!-- 6. PENDING REVIEW -->
            <div v-if="enrollment.status === 'l3_submitted'" class="glass-card rounded-[2.5rem] p-16 text-center animate-pulse">
                <div class="w-24 h-24 bg-amber-50 rounded-3xl flex items-center justify-center mx-auto mb-8">
                    <Hourglass class="text-amber-600 w-12 h-12" />
                </div>
                <h4 class="text-3xl font-black text-slate-900 mb-4">Pending Professional Review</h4>
                <p class="text-slate-500 text-lg font-medium leading-relaxed max-w-lg mx-auto">
                    Excellent work! Your Level 3 submission is currently being evaluated by one of our subject experts. You will be notified once the review is complete.
                </p>
            </div>

            <!-- 7. COMPLETED SUCCESS SCREEN -->
            <div v-if="enrollment.status === 'completed'" class="bg-white rounded-[4rem] p-20 text-center animate-in zoom-in-95 duration-1000 overflow-hidden relative border border-white shadow-2xl">
                <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-50 rounded-full blur-[100px] opacity-60"></div>
                <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-50 rounded-full blur-[100px] opacity-60"></div>
                
                <div class="relative z-10 max-w-3xl mx-auto">
                    <div class="relative mb-12">
                        <div class="absolute inset-0 bg-emerald-500/10 blur-3xl rounded-full animate-pulse"></div>
                        <div class="w-40 h-40 bg-emerald-500 rounded-[3rem] flex items-center justify-center mx-auto relative shadow-2xl shadow-emerald-200/50 rotate-3 transform hover:rotate-0 transition-transform duration-700">
                            <Trophy class="text-white w-20 h-20" />
                        </div>
                    </div>

                    <div class="space-y-4 mb-16">
                        <div class="flex items-center justify-center gap-3 text-emerald-600 font-black text-xs uppercase tracking-[0.4em]">
                            <span class="w-12 h-px bg-emerald-600"></span> Certification Unlocked
                        </div>
                        <h4 class="text-6xl font-black text-slate-900 tracking-tight leading-tight">Mastery Achieved<span class="text-emerald-500">.</span></h4>
                        <p class="text-slate-500 text-xl font-medium leading-relaxed max-w-xl mx-auto mt-6">
                            Congratulations. You have completed the full Kirkpatrick cycle, demonstrating verifiable growth and behavioral implementation.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                        <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 shadow-inner">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Growth Delta</p>
                            <p class="text-4xl font-black text-emerald-600">+{{ enrollment?.score_delta || 0 }}%</p>
                        </div>
                        <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 shadow-inner">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">XP Earned</p>
                            <p class="text-4xl font-black text-indigo-600">{{ enrollment?.xp_earned || 0 }}</p>
                        </div>
                        <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 shadow-inner">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status</p>
                            <p class="text-4xl font-black text-slate-900 uppercase tracking-tighter">Elite</p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-center gap-6">
                        <button class="w-full md:w-auto px-12 py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-xl shadow-2xl hover:bg-emerald-600 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                            <ShieldCheck class="w-6 h-6" /> Download Verifiable Certificate
                        </button>
                        <button class="w-full md:w-auto px-12 py-6 bg-white text-slate-400 border border-slate-100 rounded-[2.5rem] font-black text-xl hover:bg-slate-50 transition-all active:scale-95">
                            Share Achievement
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.glass-card {
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-in {
    animation: fadeIn 0.8s ease-out forwards;
}
</style>
