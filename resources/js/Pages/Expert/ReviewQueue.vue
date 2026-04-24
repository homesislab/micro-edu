<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
    Search, ClipboardList, CheckCircle2, RotateCcw,
    ExternalLink, FileText, Clock, Inbox, ShieldAlert,
    TrendingUp, Award, Users, BookOpen, ChevronRight,
    UploadCloud
} from 'lucide-vue-next';

// ─── Props from ExpertController::reviewQueue() ───────────────────────────────
const props = defineProps({
    assignments: Array,  // EvaluationL3Assignment with enrollment.user, enrollment.course
    defaultTemplate: Object,
});

// ─── State ────────────────────────────────────────────────────────────────────
const searchQuery          = ref('');
const selectedAssignment   = ref(null);

// ─── Computed ─────────────────────────────────────────────────────────────────
const filteredAssignments = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.assignments ?? [];
    return (props.assignments ?? []).filter(a =>
        a.enrollment?.user?.name?.toLowerCase().includes(q) ||
        a.enrollment?.course?.title?.toLowerCase().includes(q)
    );
});

// Build the rubric criteria from the assignment's lesson rubric, or default
const activeCriteria = computed(() => {
    const jsonCriteria = selectedAssignment.value?.rubric_json;
    if (jsonCriteria?.length) return jsonCriteria;
    return [
        { label: 'Accuracy',     max_points: 5 },
        { label: 'Creativity',   max_points: 5 },
        { label: 'Completeness', max_points: 5 },
    ];
});

// ─── Grading form (Inertia) ───────────────────────────────────────────────────
const form = useForm({
    scores:       {},
    status:       'approved',
    expert_notes: '',
});

const resetForm = () => {
    const s = {};
    activeCriteria.value.forEach(c => { s[c.label] = 0; });
    form.scores       = s;
    form.status       = 'approved';
    form.expert_notes = '';
};

watch(selectedAssignment, resetForm, { immediate: false });

const selectAssignment = (a) => {
    selectedAssignment.value = a;
    resetForm();
};

// Score totals
const totalScore = computed(() =>
    Object.values(form.scores).reduce((acc, v) => acc + (v || 0), 0)
);
const maxScore = computed(() =>
    activeCriteria.value.reduce((acc, c) => acc + (c.max_points || 5), 0)
);
const scorePct = computed(() =>
    maxScore.value > 0 ? Math.round((totalScore.value / maxScore.value) * 100) : 0
);

const submitReview = () => {
    if (!selectedAssignment.value) return;
    form.post(route('expert.rubric.store', selectedAssignment.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            selectedAssignment.value = null;
        },
    });
};

// ─── Avatar helpers ───────────────────────────────────────────────────────────
const AVATAR_COLORS = [
    'bg-violet-100 text-violet-700', 'bg-sky-100 text-sky-700',
    'bg-rose-100 text-rose-700',     'bg-amber-100 text-amber-700',
    'bg-emerald-100 text-emerald-700','bg-indigo-100 text-indigo-700',
];
const getAvatarColor = (id) => AVATAR_COLORS[(id || 0) % AVATAR_COLORS.length];
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
};

const formatDate = (dt) => {
    if (!dt) return '';
    const d = new Date(dt);
    const now = new Date();
    const diffH = Math.round((now - d) / 3_600_000);
    if (diffH < 1)  return 'Just now';
    if (diffH < 24) return `${diffH}h ago`;
    if (diffH < 48) return 'Yesterday';
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};

const ratingLabels = ['Poor', 'Fair', 'Good', 'Great', 'Excellent'];
</script>

<template>
    <Head title="Review Queue" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between py-6">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                        <span class="w-8 h-px bg-indigo-600"></span> Evaluation & Quality
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">
                        Review Queue<span class="text-indigo-600">.</span>
                    </h2>
                </div>
            </div>
        </template>

        <div class="h-[calc(100vh-16rem)] bg-white rounded-[3rem] border border-slate-100 shadow-2xl overflow-hidden flex animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Left Column: Master List (30%) -->
            <div class="w-[30%] border-r border-slate-50 flex flex-col bg-white">
                <div class="p-6 border-b border-slate-50">
                    <div class="relative group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-indigo-500 transition-colors" />
                        <input v-model="searchQuery" type="text" placeholder="Search by name or mission..."
                               class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-600 focus:ring-4 focus:ring-indigo-50 focus:bg-white transition-all outline-none" />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto no-scrollbar">
                    <div v-if="filteredAssignments.length === 0" class="p-12 text-center">
                        <Inbox class="w-12 h-12 text-slate-100 mx-auto mb-4" />
                        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">Queue Clear</p>
                    </div>
                    
                    <button v-for="a in filteredAssignments" :key="a.id"
                            @click="selectAssignment(a)"
                            :class="selectedAssignment?.id === a.id ? 'bg-indigo-50 border-l-4 border-indigo-600' : 'hover:bg-slate-50 border-l-4 border-transparent'"
                            class="w-full text-left p-6 border-b border-slate-50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div :class="[getAvatarColor(a.enrollment?.user?.id), 'w-12 h-12 rounded-2xl flex items-center justify-center font-black text-sm shadow-sm transition-transform group-hover:scale-105']">
                                {{ getInitials(a.enrollment?.user?.name) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-slate-900 text-sm truncate leading-none mb-1.5">{{ a.enrollment?.user?.name }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate leading-none">{{ a.enrollment?.course?.title }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ formatDate(a.submitted_at) }}</p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Right Column: Detail Canvas (70%) -->
            <div class="flex-1 flex flex-col bg-slate-50/10">
                <div v-if="selectedAssignment" class="flex flex-col h-full overflow-hidden animate-in fade-in duration-500">
                    <!-- Detail Header -->
                    <div class="bg-white p-8 border-b border-slate-50 flex items-center justify-between shadow-sm relative z-10 font-sans">
                        <div class="flex items-center gap-6">
                            <div :class="[getAvatarColor(selectedAssignment.enrollment?.user?.id), 'w-16 h-16 rounded-[1.5rem] flex items-center justify-center font-black text-2xl shadow-lg']">
                                {{ getInitials(selectedAssignment.enrollment?.user?.name) }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ selectedAssignment.enrollment?.user?.name }}</h3>
                                <div class="flex items-center gap-4 mt-1">
                                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></div>
                                        {{ selectedAssignment.enrollment?.course?.title }}
                                    </span>
                                    <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                    <button class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">View Original Mission</button>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <a v-if="selectedAssignment.submission_url" :href="selectedAssignment.submission_url" target="_blank"
                               class="bg-white text-slate-900 px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all flex items-center gap-3">
                                <ExternalLink class="w-4 h-4 text-indigo-600" /> Open Proof Bucket
                            </a>
                        </div>
                    </div>

                    <!-- Detail Content (Evidence Area) -->
                    <div class="flex-1 overflow-y-auto p-12 scroll-smooth">
                        <div class="max-w-4xl mx-auto space-y-10">
                            <!-- Evidence Canvas -->
                            <div class="bg-white p-12 rounded-[3.5rem] border border-slate-100 shadow-xl relative overflow-hidden group">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-full blur-3xl opacity-50 -mr-16 -mt-16 transition-colors group-hover:bg-indigo-50"></div>
                                <div class="relative z-10">
                                    <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-3 mb-10">
                                        <FileText class="w-4 h-4 text-indigo-600 font-black" /> Participant Workspace Output
                                    </h5>
                                    
                                    <div class="bg-gray-50 rounded-[2.5rem] p-16 border border-slate-100 min-h-[350px] flex items-center justify-center text-center relative">
                                        <div class="max-w-md">
                                            <div class="w-24 h-24 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-sm border border-slate-50 animate-bounce">
                                                <UploadCloud class="w-12 h-12 text-slate-200" />
                                            </div>
                                            <p class="text-slate-900 font-black text-2xl mb-3 tracking-tight">Evidence Repository</p>
                                            <p class="text-slate-400 font-medium text-sm leading-relaxed">
                                                Practical behavioral evidence is hosted in the secure participant vault. Review the mission milestones through the "Open Proof Bucket" action above.
                                            </p>
                                        </div>
                                    </div>

                                    <div v-if="selectedAssignment.submission_notes" class="mt-12 p-10 bg-indigo-900 rounded-[3rem] text-indigo-100 space-y-4 shadow-2xl shadow-indigo-200 relative overflow-hidden">
                                        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/5 to-transparent pointer-events-none"></div>
                                        <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">Participant Context</p>
                                        <p class="text-lg font-medium leading-relaxed italic text-white/90">
                                            "{{ selectedAssignment.submission_notes }}"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grading Footer (Panel) -->
                    <div class="bg-white p-12 border-t border-slate-100 shadow-[0_-20px_50px_rgba(0,0,0,0.05)] relative z-20">
                        <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-end gap-16">
                            <!-- Rubric Scaling -->
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-12 w-full">
                                <div v-for="c in activeCriteria" :key="c.label" class="space-y-4">
                                    <div class="flex items-center justify-between px-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ c.label }}</label>
                                        <div class="text-[10px] font-black flex items-baseline gap-1">
                                            <span class="text-indigo-600 text-sm leading-none">{{ form.scores[c.label] ?? 0 }}</span>
                                            <span class="text-slate-300 leading-none">/ {{ c.max_points }}</span>
                                        </div>
                                    </div>
                                    <!-- 1-5 scale circles -->
                                    <div class="flex items-center justify-between gap-1 p-2 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                        <button v-for="point in 5" :key="point"
                                                type="button"
                                                @click="form.scores[c.label] = point"
                                                :class="form.scores[c.label] >= point ? 'bg-indigo-600 text-white shadow-lg scale-105' : 'bg-white text-slate-400 hover:bg-white/80'"
                                                class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-sm transition-all active:scale-90">
                                            {{ point }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Total & Actions -->
                            <div class="w-full md:w-auto flex items-center gap-12 pl-12 border-l border-slate-100 min-w-[400px]">
                                <div class="text-center group">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Impact</p>
                                    <div class="flex items-baseline gap-1 group-hover:scale-110 transition-transform duration-300">
                                        <span class="text-5xl font-black text-slate-900 tracking-tighter">{{ totalScore }}</span>
                                        <span class="text-sm font-bold text-slate-300">/ {{ maxScore }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 flex flex-col gap-3">
                                    <button @click="form.status = 'approved'; submitReview()"
                                            :disabled="form.processing"
                                            class="w-full bg-indigo-600 text-white px-8 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl shadow-indigo-100 hover:bg-slate-900 transition-all hover:-translate-y-1 active:scale-95 disabled:opacity-50">
                                        Approve & Release Certificate
                                    </button>
                                    <button @click="form.status = 'rejected'; submitReview()"
                                            :disabled="form.processing"
                                            class="w-full bg-white border border-slate-200 text-slate-400 px-6 py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 hover:text-rose-600 transition-all disabled:opacity-50">
                                        Request Revision
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Idle State -->
                <div v-else class="h-full flex flex-col items-center justify-center p-20 text-center animate-in fade-in zoom-in-95 duration-700 w-full">
                    <div class="w-48 h-48 bg-white rounded-[3.5rem] border border-slate-100 shadow-2xl flex items-center justify-center mx-auto mb-10 relative group">
                        <div class="absolute inset-0 bg-indigo-50 rounded-[3.5rem] scale-90 opacity-0 group-hover:scale-100 group-hover:opacity-100 transition-all duration-700"></div>
                        <ClipboardList class="w-20 h-20 text-slate-100 relative z-10 transition-colors group-hover:text-indigo-400" />
                    </div>
                    <h4 class="text-4xl font-black text-slate-900 tracking-tight mb-4">Master Console Idle</h4>
                    <p class="text-slate-400 font-bold max-w-sm mx-auto leading-relaxed text-lg">
                        Choose a participant from the queue to start assessing their practical benchmarks.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
