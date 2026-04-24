<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { BookOpen, Award, TrendingUp, Compass, ChevronRight, PlayCircle, Clock, ExternalLink, ClipboardCheck, Pen, Zap, Check, Users } from 'lucide-vue-next';
import EvaluationFlow from '@/Components/EvaluationFlow.vue';
import LearningChart from '@/Components/LearningChart.vue';

console.log('Mounting Global Dashboard...');

const props = defineProps({
    // Student-specific props
    enrollments: { type: Array, default: () => [] },
    certificates: { type: Array, default: () => [] },
    availableCourses: { type: Array, default: () => [] },
    leaderboard: { type: Array, default: () => [] },
    // Expert-specific props
    assignmentsToReview: Array,
});

onMounted(() => console.log('Global Dashboard Mounted successfully.'));

const isExpertDashboard = computed(() => props.assignmentsToReview !== undefined);
const selectedAssignment = ref(null);

const selectAssignment = (assignment) => {
    selectedAssignment.value = assignment;
};

const activeTab = ref('hub'); // hub | leaderboard

const selectedEnrollmentId = ref(null);
const enrollForm = useForm({});

const activeEnrollment = computed(() => {
    if (!selectedEnrollmentId.value || !props.enrollments) return null;
    return props.enrollments.find(e => e.id === selectedEnrollmentId.value);
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
    
    return [
        { label: 'Courses in Progress', value: enrollments.length, icon: BookOpen, color: 'text-blue-600', bg: 'bg-blue-50/50', border: 'border-blue-100' },
        { label: 'Completed', value: completed, icon: Award, color: 'text-emerald-600', bg: 'bg-emerald-50/50', border: 'border-emerald-100' },
        { label: 'Total XP Points', value: totalXP, icon: TrendingUp, color: 'text-indigo-600', bg: 'bg-indigo-50/50', border: 'border-indigo-100', isXP: true, level, progress },
    ];
});
</script>

<template>
    <Head :title="isExpertDashboard ? 'Expert Control Center' : 'Learning Hub'" />

    <AuthenticatedLayout>
        <template #header>
            <div v-if="isExpertDashboard" class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-4">
                <div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">
                        Expert Control Center.
                    </h2>
                    <p class="text-slate-500 mt-2 font-semibold flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Architecting Behavior Growth
                    </p>
                </div>
            </div>
            <div v-else class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-4">
                <!-- Student Header -->
            </div>
        </template>

        <!-- EXPERT DASHBOARD (Inbox Zero Workflow) -->
        <div v-if="isExpertDashboard" class="grid grid-cols-12 gap-8 h-[75vh] animate-in fade-in duration-500">
            
            <!-- Left Column: Inbox -->
            <div class="col-span-4 h-full">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm h-full flex flex-col">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex-shrink-0">Review Inbox ({{ assignmentsToReview.length }})</h3>
                    <div class="flex-1 overflow-y-auto -mr-2 pr-2">
                        <div v-if="assignmentsToReview.length > 0" class="space-y-3">
                            <div v-for="assignment in (assignmentsToReview || [])" :key="assignment?.id"
                                 v-if="assignment"
                                 @click="selectAssignment(assignment)"
                                 :class="[selectedAssignment?.id === assignment?.id ? 'bg-indigo-50 border-indigo-200 shadow-md' : 'border-slate-100 hover:border-indigo-100 hover:bg-slate-50']"
                                 class="p-4 border rounded-xl cursor-pointer transition-all">
                                <p class="font-bold text-slate-800 text-sm">{{ assignment?.enrollment?.course?.title || 'Unknown Program' }}</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    By <span class="font-semibold">{{ assignment?.enrollment?.user?.name || 'Unknown User' }}</span>
                                    <span class="text-slate-300 mx-1">•</span>
                                    <span>{{ assignment?.created_at ? new Date(assignment.created_at).toLocaleDateString() : '' }}</span>
                                </p>
                            </div>
                        </div>
                        <div v-else class="text-center flex flex-col justify-center items-center h-full text-slate-400">
                             <div class="w-16 h-16 border-2 border-dashed border-slate-300 rounded-full flex items-center justify-center mb-4">
                                <ClipboardCheck class="w-7 h-7" />
                            </div>
                            <p class="font-semibold text-sm">Inbox is clear!</p>
                            <p class="text-xs mt-1">Great job, all submissions have been reviewed.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Workspace -->
            <div class="col-span-8 h-full">
                <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm h-full flex flex-col justify-center items-center">
                    <div v-if="selectedAssignment" class="w-full h-full flex flex-col text-left">
                        <h3 class="text-xl font-bold text-slate-800">Reviewing: {{ selectedAssignment.enrollment?.course?.title }}</h3>
                        <p class="text-sm text-slate-600 mb-6">Submission by {{ selectedAssignment.enrollment?.user?.name }}</p>
                        
                        <div class="mt-4 p-6 bg-slate-50 rounded-lg border border-slate-200 flex-1 overflow-y-auto">
                             <p class="text-xs font-bold text-slate-400 uppercase mb-2">Submission Link</p>
                             <a :href="selectedAssignment.link_url || '#'" target="_blank" class="text-indigo-600 font-bold underline break-words">{{ selectedAssignment.link_url || 'No link provided' }}</a>
                             
                             <p class="text-xs font-bold text-slate-400 uppercase mt-6 mb-2">Description</p>
                             <p class="text-slate-700 whitespace-pre-wrap">{{ selectedAssignment.description || 'No description.' }}</p>
                        </div>
                        
                        <!-- Rubric form will go here -->
                        <div class="mt-auto pt-6 text-right flex-shrink-0">
                             <button class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-100">Submit Review</button>
                        </div>
                    </div>
                    <div v-else class="text-center text-slate-400">
                        <div class="w-20 h-20 border-2 border-dashed border-slate-300 rounded-full flex items-center justify-center mb-6">
                            <Pen class="w-8 h-8" />
                        </div>
                        <p class="font-semibold">Select a submission from the inbox</p>
                        <p class="text-sm">to begin your review.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- STUDENT DASHBOARD -->
        <div v-else class="space-y-10">
        <div v-if="activeTab === 'hub'" class="space-y-10">
            <!-- Certificates Section -->
            <div v-if="certificates && certificates.length > 0" class="col-span-12 space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-black text-slate-900">Earning Achievements</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(cert, idx) in certificates" :key="cert.id"
                         :style="{ animationDelay: `${idx * 100}ms` }"
                         class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all flex items-center gap-6 group animate-in fade-in zoom-in-95 duration-500">
                        <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center group-hover:bg-indigo-600 transition-all">
                            <Award class="w-10 h-10 text-indigo-600 group-hover:text-white transition-all" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-black text-slate-900 leading-tight mb-1">{{ cert.course?.title }}</h4>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-4">Issued: {{ new Date(cert.issued_at).toLocaleDateString() }}</p>
                            <a :href="'/storage/' + cert.file_path" target="_blank"
                               class="inline-flex items-center gap-2 text-indigo-600 font-black text-xs hover:underline">
                                Download Badge <ExternalLink class="w-3 h-3" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Learning Map -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="(stat, idx) in stats" :key="stat.label"
                     :style="{ animationDelay: `${idx * 150}ms` }"
                     class="bg-white/60 backdrop-blur-2xl p-8 rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 flex flex-col gap-6 transition-all hover:scale-[1.02] hover:shadow-2xl duration-500 animate-in fade-in zoom-in-95">
                    <div class="flex items-center gap-5">
                        <div :class="[stat.bg, stat.color, stat.border]" class="w-16 h-16 rounded-3xl flex items-center justify-center border shadow-sm">
                            <component :is="stat.icon" class="w-8 h-8" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ stat.label }}</p>
                            <p class="text-3xl font-black text-slate-900 leading-none mt-1">{{ stat.value }}</p>
                        </div>
                    </div>
                    
                    <div v-if="stat.isXP" class="space-y-3">
                        <div class="flex justify-between items-end">
                            <span class="text-xs font-black text-indigo-600">Level {{ stat.level }}</span>
                            <span class="text-[10px] font-bold text-slate-400">Next Level: {{ stat.progress.toFixed(0) }}%</span>
                        </div>
                        <div class="h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-50">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(99,102,241,0.5)]"
                                 :style="{ width: stat.progress + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail View (Active Enrollment) -->
            <transition name="fade">
                <div v-if="activeEnrollment" class="bg-white overflow-hidden shadow-2xl rounded-[2rem] border border-slate-100 relative">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500"></div>
                    
                    <div class="p-8 md:p-12">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-12">
                            <div class="max-w-2xl">
                                <button @click="selectedEnrollmentId = null" 
                                        class="text-indigo-600 font-bold text-sm flex items-center gap-2 mb-4 hover:gap-3 transition-all">
                                    <ChevronRight class="w-4 h-4 rotate-180" />
                                    Back to Hub
                                </button>
                                <h3 class="text-4xl font-black text-slate-900 leading-tight">
                                    {{ activeEnrollment?.course?.title }}
                                </h3>
                                <div class="flex items-center gap-4 mt-4">
                                    <div class="flex items-center gap-1 text-slate-400 text-xs font-bold uppercase tracking-wider">
                                        <Clock class="w-4 h-4" />
                                        Self-Paced
                                    </div>
                                    <div class="h-4 w-[1px] bg-slate-200"></div>
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase rounded-full">
                                        Level 3 behavior focus
                                    </span>
                                </div>
                            </div>

                            <!-- Score Growth Chart for active enrollment IF post-test is done -->
                            <div v-if="activeEnrollment?.posttest_score" class="w-full md:w-80 bg-slate-50 p-6 rounded-3xl border border-slate-100">
                                <h4 class="text-xs font-bold text-slate-400 uppercase mb-4 text-center">Score Delta Performance</h4>
                                <LearningChart :pretest="activeEnrollment?.pretest_score || 0" :posttest="activeEnrollment?.posttest_score || 0" />
                                <p class="text-center mt-4 text-indigo-600 font-black text-lg">+{{ activeEnrollment?.score_delta || 0 }}% Improvement</p>
                            </div>
                        </div>
                        
                        <EvaluationFlow :enrollment="activeEnrollment" :key="activeEnrollment.status" />
                    </div>
                </div>
            </transition>

            <!-- Main Hub Grid -->
            <div v-if="!activeEnrollment" class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <!-- Enrolled Column -->
                <div class="lg:col-span-7 space-y-6">
                    <h3 class="text-2xl font-black text-slate-900 flex items-center gap-3">
                        My Courses
                        <div class="bg-indigo-600 w-2 h-2 rounded-full animate-pulse"></div>
                    </h3>
                    
                    <div v-if="enrollments.length === 0" class="bg-white p-12 rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                        <div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <BookOpen class="text-slate-300 w-10 h-10" />
                        </div>
                        <p class="text-slate-500 font-bold">No active learning paths found.</p>
                        <p class="text-slate-400 text-sm mt-1">Start by enrolling in a new course from the discovery panel.</p>
                    </div>

                    <div v-for="enrollment in enrollments" :key="enrollment.id" 
                         @click="selectEnrollment(enrollment)"
                         class="group bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl hover:border-indigo-200 transition-all duration-500 cursor-pointer relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 opacity-[0.03] group-hover:scale-125 transition-transform duration-700">
                            <BookOpen class="w-48 h-48 text-indigo-900" />
                        </div>
                        
                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-3">
                                    <span :class="[
                                        enrollment?.status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700'
                                    ]" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                        {{ (enrollment?.status || '').replace(/_/g, ' ') }}
                                    </span>
                                </div>
                                <h4 class="text-xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors">
                                    {{ enrollment?.course?.title || 'Unknown Program' }}
                                </h4>
                                
                                <div class="mt-6 flex items-center gap-6">
                                    <div class="flex-1 max-w-xs">
                                        <div class="flex justify-between text-[10px] font-bold text-slate-400 uppercase mb-2">
                                            <span>Progress</span>
                                            <span>{{ enrollment.status === 'completed' ? '100' : (enrollment.status === 'enrolled' ? '10' : '65') }}%</span>
                                        </div>
                                        <div class="bg-slate-100 h-2 rounded-full overflow-hidden">
                                            <div :class="[enrollment.status === 'completed' ? 'bg-emerald-500' : 'bg-indigo-600']"
                                                 class="h-full rounded-full transition-all duration-1000"
                                                 :style="{ width: enrollment.status === 'completed' ? '100%' : (enrollment.status === 'enrolled' ? '10%' : '65%') }">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex -space-x-2">
                                        <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                            {{ i }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-6 bg-slate-50 w-14 h-14 rounded-2xl flex items-center justify-center text-slate-300 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                                <PlayCircle class="w-7 h-7" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discovery Column -->
                <div class="lg:col-span-5 space-y-6">
                    <h3 class="text-2xl font-black text-slate-900">Explore New Skills</h3>
                    
                    <div v-for="course in availableCourses" :key="course.id" 
                         class="bg-indigo-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl group cursor-default">
                        <div class="absolute -right-16 -top-16 bg-white/10 w-48 h-48 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
                        
                        <div class="relative z-10">
                            <h4 class="text-2xl font-black leading-tight mb-3">{{ course?.title }}</h4>
                            <p class="text-indigo-100 text-sm opacity-80 mb-8 font-medium line-clamp-3">
                                {{ course?.description }}
                            </p>
                            <div class="flex items-center justify-between">
                                <div class="bg-white/10 px-4 py-2 rounded-2xl backdrop-blur-sm border border-white/10">
                                    <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest leading-none">Min. Passing</p>
                                    <p class="text-lg font-black mt-1">{{ course?.passing_grade }}%</p>
                                </div>
                                <button @click="enroll(course.id)" 
                                        :disabled="enrollForm.processing"
                                        class="bg-white text-indigo-900 px-8 py-4 rounded-3xl font-black text-sm shadow-xl hover:bg-indigo-50 transition-all active:scale-95 disabled:opacity-50">
                                    Enroll Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Leaderboard Tab -->
            <div v-if="activeTab === 'leaderboard'" class="space-y-12 animate-in fade-in slide-in-from-bottom-10 duration-700">
                <div class="max-w-4xl mx-auto">
                    <div class="text-center mb-16 relative">
                        <div class="absolute inset-0 bg-indigo-500/5 blur-[120px] rounded-full"></div>
                        <div class="inline-block p-6 bg-indigo-50 rounded-[2.5rem] mb-8 relative shadow-inner">
                            <TrendingUp class="w-12 h-12 text-indigo-600" />
                        </div>
                        <h3 class="text-5xl font-black text-slate-900 mb-4 tracking-tight">Community Leaderboard</h3>
                        <p class="text-slate-500 font-bold bg-white/50 backdrop-blur-sm inline-block px-6 py-2 rounded-2xl shadow-sm border border-white">Ranking by behavior growth XP and course mastery.</p>
                    </div>

                    <div class="bg-white/80 backdrop-blur-2xl rounded-[3.5rem] border border-white shadow-2xl overflow-hidden relative">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500/20 via-indigo-500 to-indigo-500/20"></div>
                        <div v-for="(user, idx) in (leaderboard || [])" :key="idx" 
                             class="flex items-center justify-between p-10 border-b border-slate-100/50 last:border-0 hover:bg-slate-50/50 transition-all duration-300 group">
                            <div class="flex items-center gap-8">
                                <div class="w-12 text-center">
                                    <span v-if="idx < 3" :class="[
                                        idx === 0 ? 'bg-amber-100 text-amber-600 border-amber-200' : 
                                        idx === 1 ? 'bg-slate-100 text-slate-500 border-slate-200' : 
                                        'bg-orange-100 text-orange-700 border-orange-200'
                                    ]" class="w-10 h-10 rounded-xl border flex items-center justify-center text-xl font-black shadow-sm">
                                        {{ idx + 1 }}
                                    </span>
                                    <span v-else class="text-2xl font-black text-slate-300 group-hover:text-indigo-400 transition-colors">#{{ idx + 1 }}</span>
                                </div>
                                <div class="relative" v-if="user">
                                    <img :src="user?.avatar || '/default-avatar.png'" class="w-16 h-16 rounded-3xl shadow-lg border-2 border-white ring-4 ring-slate-50 group-hover:scale-110 transition-transform duration-500" />
                                    <div v-if="idx === 0" class="absolute -top-3 -right-3 bg-amber-400 text-white p-1 rounded-full shadow-lg border-2 border-white">
                                        <Award class="w-4 h-4" />
                                    </div>
                                </div>
                                <div v-if="user">
                                    <h4 class="font-black text-slate-900 text-xl group-hover:translate-x-1 transition-transform">{{ user?.name || 'Anonymous' }}</h4>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">{{ idx === 0 ? 'Master of Behavior' : 'Learning Enthusiast' }}</p>
                                </div>
                            </div>
                            <div class="text-right" v-if="user">
                                <div class="flex items-center justify-end gap-2 text-3xl font-black text-indigo-600">
                                    <span>{{ user?.total_points || 0 }}</span>
                                    <span class="text-xs text-indigo-300 font-black uppercase">XP</span>
                                </div>
                                <div class="h-1.5 w-24 bg-slate-100 rounded-full mt-2 ml-auto overflow-hidden">
                                    <div class="h-full bg-indigo-500/30 rounded-full" :style="{ width: Math.max(10, 100 - (idx * 10)) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
