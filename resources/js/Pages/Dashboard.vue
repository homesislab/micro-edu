<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { 
    BookOpen, Award, TrendingUp, Compass, ChevronRight, 
    PlayCircle, Clock, ExternalLink, ClipboardCheck, 
    Pen, Zap, Check, Users, Sparkles, Activity 
} from 'lucide-vue-next';
import EvaluationFlow from '@/Components/EvaluationFlow.vue';
import LearningChart from '@/Components/LearningChart.vue';

const props = defineProps({
    enrollments: { type: Array, default: () => [] },
    certificates: { type: Array, default: () => [] },
    availableCourses: { type: Array, default: () => [] },
    leaderboard: { type: Array, default: () => [] },
    assignmentsToReview: Array,
});

const isExpertDashboard = computed(() => props.assignmentsToReview !== undefined);
const selectedAssignment = ref(null);
const activeTab = ref('hub'); // hub | leaderboard
const selectedEnrollmentId = ref(null);
const enrollForm = useForm({});

const activeEnrollment = computed(() => {
    if (!selectedEnrollmentId.value || !props.enrollments) return null;
    return props.enrollments.find(e => e.id === selectedEnrollmentId.value);
});

// Most recent active course for "Focus Mode"
const primaryFocusCourse = computed(() => {
    if (!props.enrollments || props.enrollments.length === 0) return null;
    return props.enrollments.find(e => e.status !== 'completed') || props.enrollments[0];
});

const enroll = (courseId) => {
    enrollForm.post(route('courses.enroll', courseId));
};

const selectEnrollment = (enrollment) => {
    selectedEnrollmentId.value = enrollment.id;
};

// Analytics mock/computed logic
const stats = computed(() => {
    const enrollments = props.enrollments || [];
    const completed = enrollments.filter(e => e?.status === 'completed').length;
    const totalXP = enrollments.reduce((acc, curr) => acc + (curr?.points || 0), 0);
    const level = Math.floor(totalXP / 500) + 1;
    const nextLevelXP = level * 500;
    const progress = Math.min(((totalXP % 500) / 500) * 100, 100);
    
    return {
        level, progress, xp: totalXP, completed, active: enrollments.length - completed
    };
});
</script>

<template>
    <Head :title="isExpertDashboard ? 'Expert Control Center' : 'Learning Hub'" />

    <AuthenticatedLayout>
        <div class="bg-slate-50 min-h-[calc(100vh-73px)] pb-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

                <!-- EXPERT DASHBOARD INBOX MODE -->
                <div v-if="isExpertDashboard" class="space-y-8 h-[80vh] flex flex-col">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Review Center</h2>
                            <p class="text-slate-500 mt-1 font-medium">Verify level 3 behavioral implementations.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 flex-1 min-h-0">
                        <!-- Left Column: Inbox -->
                        <div class="md:col-span-4 h-full bg-white rounded-[2.5rem] border border-slate-200/50 shadow-sm p-6 flex flex-col overflow-hidden">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-black text-slate-800">Inbox</h3>
                                <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-xs font-black">{{ assignmentsToReview?.length || 0 }}</span>
                            </div>
                            
                            <div class="flex-1 overflow-y-auto pr-2 space-y-3 no-scrollbar">
                                <div v-if="assignmentsToReview?.length > 0">
                                    <div v-for="assignment in assignmentsToReview" :key="assignment?.id"
                                         @click="selectedAssignment = assignment"
                                         :class="[
                                            'p-5 rounded-2xl cursor-pointer transition-all border block relative overflow-hidden group',
                                            selectedAssignment?.id === assignment?.id 
                                                ? 'bg-[#0B0F19] text-white border-transparent' 
                                                : 'bg-white border-slate-100 hover:border-indigo-200 hover:bg-slate-50'
                                         ]">
                                        <!-- Gradient overlay for active -->
                                        <div v-if="selectedAssignment?.id === assignment?.id" class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 to-transparent"></div>
                                        
                                        <div class="relative z-10">
                                            <p :class="selectedAssignment?.id === assignment?.id ? 'text-white' : 'text-slate-800'" class="font-black text-sm mb-1">
                                                {{ assignment?.enrollment?.course?.title || 'Unknown Program' }}
                                            </p>
                                            <div class="flex items-center justify-between mt-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full bg-slate-200 overflow-hidden">
                                                        <img :src="assignment?.enrollment?.user?.avatar || '/default-avatar.png'" class="w-full h-full object-cover" />
                                                    </div>
                                                    <span :class="selectedAssignment?.id === assignment?.id ? 'text-slate-300' : 'text-slate-500'" class="text-xs font-medium">
                                                        {{ assignment?.enrollment?.user?.name || 'Unknown User' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center flex flex-col justify-center items-center h-full text-slate-400">
                                     <div class="w-16 h-16 border-2 border-dashed border-slate-200 rounded-full flex items-center justify-center mb-4 bg-slate-50">
                                        <Check class="w-6 h-6 text-emerald-500" />
                                    </div>
                                    <p class="font-bold text-sm text-slate-600">Inbox is Clear</p>
                                    <p class="text-xs mt-1">All submissions have been reviewed.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Workspace -->
                        <div class="md:col-span-8 h-full bg-white rounded-[2.5rem] border border-slate-200/50 shadow-sm p-10 flex flex-col overflow-y-auto">
                            <div v-if="selectedAssignment" class="animate-in fade-in slide-in-from-bottom-4 duration-500 w-full max-w-3xl mx-auto flex flex-col h-full">
                                <div class="mb-8">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-purple-50 text-purple-700 rounded-full mb-4">
                                        <Award class="w-4 h-4" />
                                        <span class="text-[10px] font-black uppercase tracking-widest">Level 3 Behavior Validation</span>
                                    </div>
                                    <h3 class="text-3xl font-black text-slate-900 leading-tight">{{ selectedAssignment.enrollment?.course?.title }}</h3>
                                    
                                    <div class="flex items-center gap-4 mt-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                        <img :src="selectedAssignment.enrollment?.user?.avatar || '/default-avatar.png'" class="w-12 h-12 rounded-xl object-cover" />
                                        <div>
                                            <p class="text-sm font-black text-slate-900">{{ selectedAssignment.enrollment?.user?.name }}</p>
                                            <p class="text-xs font-medium text-slate-500">Submitted {{ new Date(selectedAssignment.created_at).toLocaleDateString() }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-6 flex-1">
                                    <div class="bg-indigo-50/50 border border-indigo-100 p-6 rounded-2xl">
                                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">Submission Link</p>
                                        <a :href="selectedAssignment.link_url || '#'" target="_blank" 
                                           class="text-indigo-600 font-bold hover:underline flex items-center gap-2 break-all bg-white px-4 py-3 rounded-xl border border-indigo-50">
                                            <ExternalLink class="w-4 h-4" /> {{ selectedAssignment.link_url || 'No external URL provided' }}
                                        </a>
                                    </div>
                                    
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Description / Proof</p>
                                        <div class="bg-slate-50 border border-slate-100 p-6 rounded-2xl whitespace-pre-wrap text-slate-700 text-sm leading-relaxed">
                                            {{ selectedAssignment.description || 'No description provided by the learner.' }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-10 pt-6 border-t border-slate-100 flex justify-end gap-4">
                                    <button class="px-6 py-3 bg-rose-50 text-rose-600 font-black rounded-xl hover:bg-rose-100 transition-colors">Needs Revision</button>
                                    <button class="px-8 py-3 bg-[#0B0F19] text-white font-black rounded-xl hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200">
                                        Approve Verification
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center text-slate-400 flex-1 flex flex-col items-center justify-center">
                                <div class="w-24 h-24 border-2 border-dashed border-slate-200 rounded-[2rem] flex items-center justify-center mb-6 bg-slate-50 text-slate-300">
                                    <Activity class="w-10 h-10" />
                                </div>
                                <h3 class="text-lg font-black text-slate-800 mb-1">Select a Submission</h3>
                                <p class="text-sm font-medium">Verify evidence to complete the Kirkpatrick Level 3 evaluation.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STUDENT DASHBOARD -->
                <div v-else class="space-y-12">
                    
                    <!-- Metrics Strip -->
                    <div v-if="!activeEnrollment" class="bg-[#0B0F19] rounded-[2rem] p-8 text-white shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8 border border-slate-800">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-bl from-indigo-500/20 to-purple-500/0 rounded-full blur-3xl pointer-events-none"></div>
                        
                        <div class="flex items-center gap-6 relative z-10 w-full md:w-auto">
                            <div class="relative">
                                <svg class="w-20 h-20 transform -rotate-90">
                                    <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="6" fill="transparent" class="text-slate-800" />
                                    <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="6" fill="transparent" :stroke-dasharray="2 * Math.PI * 36" :stroke-dashoffset="(2 * Math.PI * 36) * (1 - stats.progress / 100)" class="text-indigo-500 transition-all duration-1000 ease-out" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-xl font-black">{{ stats.level }}</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black">Level {{ stats.level }} Learner</h3>
                                <div class="flex items-center gap-4 mt-1 text-slate-400 text-sm font-medium">
                                    <span>{{ stats.xp }} XP</span>
                                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                    <span>{{ 500 - (stats.xp % 500) }} to next level</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-6 w-full md:w-auto relative z-10 border-t md:border-t-0 md:border-l border-slate-800 pt-6 md:pt-0 md:pl-8">
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Active</p>
                                <p class="text-3xl font-black text-indigo-400">{{ stats.active }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Mastered</p>
                                <p class="text-3xl font-black text-emerald-400">{{ stats.completed }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Focus Mode Layout: Active Enrollment -->
                    <transition name="fade" mode="out-in">
                        <div v-if="activeEnrollment" class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-xl overflow-hidden relative">
                             <!-- Header Area -->
                            <div class="bg-[#0B0F19] p-10 flex flex-col md:flex-row justify-between items-start gap-8 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-900/40 to-transparent"></div>
                                
                                <div class="relative z-10 max-w-2xl">
                                    <button @click="selectedEnrollmentId = null" 
                                            class="text-indigo-400 font-bold text-sm flex items-center gap-2 mb-6 hover:text-white transition-all">
                                        <ChevronRight class="w-4 h-4 rotate-180" /> Back to Hub
                                    </button>
                                    <h2 class="text-4xl md:text-5xl font-black text-white leading-[1.1] mb-6">
                                        {{ activeEnrollment?.course?.title }}
                                    </h2>
                                    
                                    <div class="flex items-center gap-4">
                                        <span class="px-4 py-1.5 bg-white/10 text-white rounded-full text-xs font-black border border-white/10">
                                            Status: <span class="capitalize text-indigo-300">{{ activeEnrollment.status.replace('_', ' ') }}</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Delta Score if available -->
                                <div v-if="activeEnrollment?.posttest_score" class="relative z-10 w-full md:w-80 bg-white/5 backdrop-blur-md p-6 rounded-3xl border border-white/10">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center mb-4">Growth Delta</h4>
                                    <LearningChart :pretest="activeEnrollment?.pretest_score || 0" :posttest="activeEnrollment?.posttest_score || 0" />
                                    <div class="mt-4 text-center">
                                        <span class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-400 font-black rounded-lg text-sm">
                                            +{{ activeEnrollment?.score_delta || 0 }}% Improvement
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Flow component -->
                            <div class="p-10">
                                <EvaluationFlow :enrollment="activeEnrollment" :key="activeEnrollment.status" />
                            </div>
                        </div>

                        <!-- Hub Grid -->
                        <div v-else class="space-y-12">
                            
                            <!-- Continue Learning (Focus Card) -->
                            <div v-if="primaryFocusCourse" class="relative group cursor-pointer" @click="selectEnrollment(primaryFocusCourse)">
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-[3rem] blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                                <div class="bg-white rounded-[3rem] p-8 md:p-12 relative flex flex-col md:flex-row items-center gap-10 shadow-sm border border-white">
                                    
                                    <div class="w-full md:w-1/3 aspect-video bg-slate-900 rounded-[2rem] overflow-hidden relative shadow-lg">
                                        <img :src="'/storage/' + primaryFocusCourse.course?.thumbnail_path" v-if="primaryFocusCourse.course?.thumbnail_path" class="w-full h-full object-cover opacity-80" />
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/10 transition-colors">
                                            <div class="w-16 h-16 bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center shadow-2xl">
                                                <PlayCircle class="w-8 h-8 text-white" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-4">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">In Progress</span>
                                        </div>
                                        <h3 class="text-3xl font-black text-slate-900 mb-4 group-hover:text-indigo-600 transition-colors leading-tight">
                                            {{ primaryFocusCourse.course?.title }}
                                        </h3>
                                        <p class="text-slate-500 font-medium line-clamp-2 md:pr-12 mb-8">
                                            {{ primaryFocusCourse.course?.description }}
                                        </p>
                                        
                                        <div class="flex items-center gap-6">
                                            <div class="flex-1 max-w-sm">
                                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                                    <div class="h-full bg-indigo-600 rounded-full transition-all" style="width: 45%;"></div>
                                                </div>
                                            </div>
                                            <span class="font-black text-slate-900">45%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Grid & Empty states -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                                
                                <!-- Other Courses -->
                                <div class="space-y-6">
                                    <h3 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                                        <BookOpen class="w-6 h-6 text-indigo-500" /> My Paths
                                    </h3>
                                    
                                    <div class="grid gap-6">
                                        <div v-for="enrollment in enrollments" :key="enrollment.id" 
                                             v-show="enrollment.id !== primaryFocusCourse?.id"
                                             @click="selectEnrollment(enrollment)"
                                             class="bg-white p-6 rounded-[2rem] border border-slate-100 hover:border-indigo-200 hover:shadow-xl transition-all cursor-pointer flex gap-6 group">
                                            <div class="w-24 h-24 bg-slate-100 rounded-2xl flex-shrink-0 overflow-hidden relative">
                                                <img :src="'/storage/' + enrollment.course?.thumbnail_path" v-if="enrollment.course?.thumbnail_path" class="w-full h-full object-cover" />
                                                <div v-else class="w-full h-full bg-indigo-50 flex items-center justify-center">
                                                    <BookOpen class="text-indigo-200 w-8 h-8" />
                                                </div>
                                            </div>
                                            <div class="flex-1 flex flex-col justify-center">
                                                <h4 class="font-black text-lg text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1 mb-2">
                                                    {{ enrollment.course?.title }}
                                                </h4>
                                                <span class="inline-block px-2.5 py-1 bg-slate-50 border border-slate-100 text-slate-500 text-[10px] font-black uppercase rounded-lg w-max mb-3">
                                                    {{ enrollment.status.replace('_', ' ') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Discover / Trophy Room -->
                                <div class="space-y-6">
                                    <!-- Trophy Room if certs exist -->
                                    <div v-if="certificates?.length > 0" class="bg-gradient-to-br from-[#0B0F19] to-indigo-950 p-8 rounded-[2.5rem] text-white shadow-xl">
                                        <h3 class="text-xl font-black mb-6 flex items-center gap-2">
                                            <Award class="w-5 h-5 text-amber-400" /> Trophy Room
                                        </h3>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div v-for="cert in certificates.slice(0, 4)" :key="cert.id"
                                                 class="bg-white/10 p-4 rounded-2xl border border-white/10 backdrop-blur-md">
                                                <Award class="w-8 h-8 text-amber-400 mb-3" />
                                                <p class="text-sm font-black line-clamp-2 leading-tight">{{ cert.course?.title }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Discovery -->
                                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                                        <h3 class="text-xl font-black text-slate-900 mb-6 flex items-center gap-2">
                                            <Sparkles class="w-5 h-5 text-indigo-500" /> Recommended Paths
                                        </h3>
                                        <div class="space-y-5">
                                            <div v-for="course in availableCourses.slice(0, 3)" :key="course.id"
                                                 class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                                <div>
                                                    <p class="font-black text-slate-900">{{ course.title }}</p>
                                                    <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Min. Pass: {{ course.passing_grade }}%</p>
                                                </div>
                                                <button @click="enroll(course.id)" :disabled="enrollForm.processing"
                                                        class="w-10 h-10 bg-[#0B0F19] text-white rounded-xl flex items-center justify-center hover:bg-indigo-600 transition-colors shadow-lg active:scale-95">
                                                    <Check class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                        <Link href="/catalog" class="block w-full text-center mt-6 text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                            Browse Full Catalog &rarr;
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
