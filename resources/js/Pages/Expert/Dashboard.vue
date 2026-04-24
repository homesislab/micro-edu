<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    ClipboardList, User, ExternalLink, FileText,
    ShieldCheck, Search, ChevronRight, BookOpen,
    Settings, BarChart3, Download, Users, Award,
    TrendingUp, Key, Copy, CheckCircle2, X, Plus,
    Sparkles, Loader2, LayoutGrid, Clock, Bell, Star
} from 'lucide-vue-next';
import ExpertReviewForm from '@/Components/Expert/ExpertReviewForm.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    assignments: Array,
    courses: {
        type: Array,
        default: () => []
    },
    defaultTemplate: Object,
});

const activeMode = ref('review');
const selectedTrackerCourse = ref(null);
const selectedAssignment = ref(null);
const copiedCodeId = ref(null);
const searchQuery = ref('');

const copyAttendanceCode = async (course) => {
    if (!course.attendance_code) return;
    await navigator.clipboard.writeText(course.attendance_code);
    copiedCodeId.value = course.id;
    setTimeout(() => copiedCodeId.value = null, 2000);
};

const selectAssignment = (assignment) => {
    selectedAssignment.value = assignment;
};

const showCreateModal = ref(false);
const createForm = useForm({
    title: '',
    description: '',
    passing_grade: 75,
});

const submitCreateCourse = () => {
    createForm.post(route('expert.courses.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const isGeneratingAI = ref(false);
const aiTopic = ref('');

const generateWithAI = async () => {
    if (!aiTopic.value) return;
    isGeneratingAI.value = true;
    try {
        const response = await axios.post(route('expert.ai.generate-program'), { topic: aiTopic.value });
        const data = response.data;
        createForm.title = data.title;
        createForm.description = data.description;
        createForm.passing_grade = data.passing_grade;
    } catch (error) {
        console.error('AI Generation failed', error);
    } finally {
        isGeneratingAI.value = false;
    }
};

const expertStats = computed(() => {
    const total_students = props.courses?.reduce((acc, c) => acc + (c?.enrollments_count || 0), 0) || 0;
    const completed_students = props.courses?.reduce((acc, c) => {
        return acc + (c?.enrollments?.filter(e => e?.status === 'completed')?.length || 0);
    }, 0) || 0;
    const success_velocity = total_students > 0 ? Math.round((completed_students / total_students) * 100) : 0;

    return {
        total_students,
        certifications_issued: completed_students,
        success_velocity
    };
});

const pendingCount = computed(() => props.assignments?.length || 0);

const filteredAssignments = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.assignments || [];
    return (props.assignments || []).filter(a =>
        a.enrollment?.user?.name?.toLowerCase().includes(q) ||
        a.enrollment?.course?.title?.toLowerCase().includes(q)
    );
});

// Avatar initials helper
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
};

const avatarColors = [
    'bg-violet-100 text-violet-700',
    'bg-sky-100 text-sky-700',
    'bg-rose-100 text-rose-700',
    'bg-amber-100 text-amber-700',
    'bg-emerald-100 text-emerald-700',
    'bg-indigo-100 text-indigo-700',
];
const getAvatarColor = (id) => avatarColors[(id || 0) % avatarColors.length];
</script>

<template>
    <Head title="Expert Control Center" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 py-2 mt-4">
                <!-- Compact Title block -->
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center shadow-lg flex-shrink-0">
                        <LayoutGrid class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Expert Panel</h2>
                        <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest leading-none mt-1">LMS Administration</p>
                    </div>
                </div>

                <!-- Tab Switcher — cleaner style -->
                <nav class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/50 gap-1">
                    <button @click="activeMode = 'review'"
                            :class="activeMode === 'review' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="px-4 py-2 rounded-lg font-bold text-[11px] transition-all flex items-center gap-2 whitespace-nowrap">
                        <ClipboardList class="w-3 h-3" /> Review Queue
                        <span v-if="pendingCount > 0"
                              class="bg-rose-500 text-white text-[9px] px-1.5 py-0.5 rounded-full min-w-[16px] text-center">
                            {{ pendingCount }}
                        </span>
                    </button>
                    <button @click="activeMode = 'courses'"
                            :class="activeMode === 'courses' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="px-4 py-2 rounded-lg font-bold text-[11px] transition-all flex items-center gap-2 whitespace-nowrap">
                        <BookOpen class="w-3 h-3" /> Programs
                    </button>
                    <button @click="activeMode = 'tracker'"
                            :class="activeMode === 'tracker' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="px-4 py-2 rounded-lg font-bold text-[11px] transition-all flex items-center gap-2 whitespace-nowrap">
                        <BarChart3 class="w-3 h-3" /> Insights
                    </button>
                </nav>
            </div>
        </template>

        <!-- ══════════════════════════════════════════
             TAB 1 — REVIEW QUEUE (Master-Detail)
        ══════════════════════════════════════════ -->
        <div v-if="activeMode === 'review'"
             class="flex bg-slate-50 border-t border-slate-200"
             style="height: calc(100vh - 140px); margin: -16px -24px -24px -24px;">

            <!-- LEFT COLUMN — Master List (30%) -->
            <div class="w-[30%] min-w-[320px] flex-shrink-0 flex flex-col bg-white border-r border-slate-200 z-10">
                <!-- Search -->
                <div class="p-4 border-b border-slate-200">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input v-model="searchQuery" type="text" placeholder="Search by name or mission..."
                               class="w-full pl-9 pr-3 py-2 bg-slate-50 rounded-lg text-sm font-medium text-slate-700 border-transparent focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none" />
                    </div>
                </div>

                <!-- Assignment List -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Empty state -->
                    <div v-if="filteredAssignments.length === 0" class="flex flex-col items-center justify-center h-full py-12 px-6 text-center">
                        <p class="font-semibold text-slate-400 text-sm">Inbox Empty</p>
                    </div>

                    <!-- Assignment cards -->
                    <div v-for="assignment in filteredAssignments" :key="assignment.id"
                         @click="selectAssignment(assignment)"
                         :class="[
                             'cursor-pointer transition-colors border-b border-slate-100 p-4',
                             selectedAssignment?.id === assignment.id
                                 ? 'bg-indigo-50 border-l-4 border-l-indigo-600'
                                 : 'bg-white border-l-4 border-l-transparent hover:bg-slate-50'
                         ]">
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0', getAvatarColor(assignment.id)]">
                                {{ getInitials(assignment.enrollment?.user?.name) }}
                            </div>
                            <!-- Meta -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-1 mb-1">
                                    <p class="font-bold text-slate-900 text-sm leading-tight truncate">
                                        {{ assignment?.enrollment?.user?.name || 'Unknown' }}
                                    </p>
                                    <span class="text-xs text-slate-400 font-medium whitespace-nowrap flex-shrink-0">
                                        {{ assignment?.created_at ? new Date(assignment.created_at).toLocaleDateString() : 'New' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium truncate">
                                    {{ assignment?.enrollment?.course?.title || 'Unknown Mission' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN — Detail Canvas (70%) -->
            <div class="w-[70%] flex flex-col bg-slate-50 relative overflow-hidden">

                <!-- EMPTY STATE -->
                <div v-if="!selectedAssignment" class="flex-1 flex flex-col items-center justify-center p-10">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm border border-slate-200">
                        <Search class="w-6 h-6 text-slate-300" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-400">Nothing selected</h3>
                    <p class="text-slate-400 font-medium text-sm text-center">Select a submission from the inbox to begin evaluation.</p>
                </div>

                <!-- ACTIVE SUBMISSION DETAIL -->
                <div v-else class="flex-1 flex flex-col h-full">
                    <!-- Top Header -->
                    <div class="bg-white border-b border-slate-200 px-8 py-5 flex items-center justify-between shrink-0 shadow-sm z-10">
                        <div class="flex items-center gap-4">
                            <div :class="['w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg', getAvatarColor(selectedAssignment?.id)]">
                                {{ getInitials(selectedAssignment?.enrollment?.user?.name) }}
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-xl leading-tight">{{ selectedAssignment?.enrollment?.user?.name || 'Student Name' }}</h3>
                                <p class="text-sm text-slate-500 font-medium mt-1">{{ selectedAssignment?.enrollment?.course?.title || 'Mission Name' }}</p>
                            </div>
                        </div>
                        <a :href="selectedAssignment.submission_type === 'file' ? '/storage/' + selectedAssignment.file_path : selectedAssignment.link_url"
                           target="_blank"
                           class="flex items-center gap-2 text-sm font-semibold text-indigo-600 bg-indigo-50 px-4 py-2 rounded-lg hover:bg-indigo-100 transition-colors border border-indigo-100">
                            <ExternalLink class="w-4 h-4" /> View Original Mission
                        </a>
                    </div>

                    <!-- Center Content (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-8">
                        <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-xl shadow-sm p-8">
                            <h4 class="text-sm font-bold text-slate-900 mb-4 border-b border-slate-100 pb-3">Submission Evidence</h4>
                            <div class="bg-slate-50 rounded-lg p-5 border border-slate-100">
                                <div v-if="selectedAssignment.submission_type === 'file'" class="flex items-center gap-4">
                                    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
                                        <FileText class="text-blue-600 w-6 h-6" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ selectedAssignment.file_path?.split('/').pop() }}</p>
                                        <p class="text-xs text-slate-500 mt-1">Uploaded Document</p>
                                    </div>
                                </div>
                                <div v-else class="flex items-center gap-4">
                                    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
                                        <ExternalLink class="text-violet-600 w-6 h-6" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 truncate">{{ selectedAssignment.link_url }}</p>
                                        <p class="text-xs text-slate-500 mt-1">External Link</p>
                                    </div>
                                </div>
                            </div>

                            <h4 class="text-sm font-bold text-slate-900 mt-8 mb-4 border-b border-slate-100 pb-3">Narrative</h4>
                            <div class="text-slate-700 text-sm leading-relaxed whitespace-pre-wrap">
                                {{ selectedAssignment.description || 'No narrative provided.' }}
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Panel: Grading Rubric -->
                    <div class="bg-white border-t border-slate-200 p-6 shrink-0 shadow-[0_-4px_10px_-1px_rgba(0,0,0,0.05)] z-20">
                        <div class="max-w-4xl mx-auto">
                            <ExpertReviewForm :assignment="selectedAssignment" :template="props.defaultTemplate" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
             TAB 2 — PROGRAMS (Course Management)
        ══════════════════════════════════════════ -->
        <div v-if="activeMode === 'courses'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-6xl">
            <!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        Welcome back, {{ user?.name || 'Expert' }}
                    </h2>
                    <p class="text-slate-500 font-medium mt-1">Here is the bird's-eye view of your academy performance.</p>
                </div>
                <button @click="showCreateModal = true" 
                        class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm flex items-center gap-2 shadow-sm hover:bg-indigo-700 transition-colors flex-shrink-0 w-max">
                    <Plus class="w-4 h-4" /> Design New Program
                </button>
            </div>

            <!-- Top Metrics (3 Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Students -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <Users class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Students</p>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ expertStats.total_students }}</h3>
                    </div>
                </div>

                <!-- Certifications Issued -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <Award class="w-6 h-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Certifications Issued</p>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ expertStats.certifications_issued }}</h3>
                    </div>
                </div>

                <!-- Success Velocity -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <TrendingUp class="w-6 h-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Avg. Success Velocity</p>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ expertStats.success_velocity }}%</h3>
                    </div>
                </div>
            </div>

            <!-- Program Grid -->
            <div class="pt-2">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Your Learning Products</h3>

                <div v-if="courses?.length === 0" class="bg-white p-16 rounded-xl border border-slate-200 border-dashed text-center">
                    <BookOpen class="text-slate-300 w-12 h-12 mx-auto mb-4" />
                    <p class="text-slate-900 font-bold text-lg">No programs found</p>
                    <p class="text-slate-500 text-sm mt-1 mb-6">Start building your legacy by designing your first learning program.</p>
                    <button @click="showCreateModal = true" class="text-indigo-600 font-bold text-sm hover:text-indigo-700 hover:underline">
                        + Design New Program
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="course in courses" :key="course.id"
                         class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col group hover:shadow-md hover:border-slate-300 transition-all">
                        <!-- Card Cover -->
                        <div class="h-40 relative bg-gradient-to-br from-indigo-900 to-purple-900 overflow-hidden">
                            <img v-if="course.thumbnail_path" :src="'/storage/' + course.thumbnail_path"
                                 class="w-full h-full object-cover opacity-50 group-hover:opacity-70 transition-opacity" />
                            <div class="absolute top-3 left-3">
                                <span class="bg-white/90 backdrop-blur-sm text-slate-800 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md shadow-sm">
                                    {{ course?.status || 'Draft' }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex flex-col flex-1">
                            <h4 class="font-bold text-slate-900 text-lg mb-1 line-clamp-1 group-hover:text-indigo-600 transition-colors">
                                {{ course?.title }}
                            </h4>
                            
                            <div class="flex items-center gap-5 mt-3 mb-6 text-sm text-slate-500">
                                <div class="flex items-center gap-1.5">
                                    <Users class="w-4 h-4 text-slate-400" />
                                    <span class="font-medium">{{ course?.enrollments_count ?? 0 }} Students</span>
                                </div>
                                <div class="flex items-center gap-1.5 border-l border-slate-200 pl-5">
                                    <span class="font-bold text-slate-700">${{ (course?.price ?? 0) }}</span>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <Link :href="route('expert.courses.builder', course.id)"
                                      class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-transparent border border-slate-200 text-slate-700 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors">
                                    Manage Curriculum
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
             TAB 3 — INSIGHTS (Participant Tracker)
        ══════════════════════════════════════════ -->
        <div v-if="activeMode === 'tracker'" class="space-y-6 animate-in fade-in slide-in-from-bottom-3 duration-500">
            <div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Training Insights</h3>
                <p class="text-slate-400 font-medium text-sm mt-0.5">Track behavioral change and performance deltas across programs.</p>
            </div>

            <div class="flex gap-6" style="min-height: 500px;">
                <!-- Program selector -->
                <div class="w-64 flex-shrink-0 space-y-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 mb-3">Select Program</p>
                    <button v-for="course in courses" :key="course.id"
                            @click="selectedTrackerCourse = course"
                            :class="selectedTrackerCourse?.id === course.id
                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-100'
                                : 'bg-white text-slate-700 border-slate-100 hover:border-indigo-200 hover:shadow-sm'"
                            class="w-full text-left px-5 py-4 rounded-2xl border transition-all duration-200 font-bold text-sm flex items-center justify-between group">
                        <span class="truncate">{{ course?.title }}</span>
                        <ChevronRight :class="selectedTrackerCourse?.id === course.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-50'"
                                      class="w-4 h-4 flex-shrink-0 transition-opacity" />
                    </button>
                    <div v-if="!courses?.length" class="text-center py-8">
                        <p class="text-slate-300 text-sm font-bold">No programs found.</p>
                    </div>
                </div>

                <!-- Data panel -->
                <div class="flex-1">
                    <!-- Selected program table -->
                    <div v-if="selectedTrackerCourse" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden animate-in fade-in duration-300">
                        <!-- Table header -->
                        <div class="px-8 py-5 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                            <div>
                                <h4 class="font-black text-slate-900">{{ selectedTrackerCourse?.title }}</h4>
                                <p class="text-xs text-slate-400 font-medium mt-0.5">
                                    {{ selectedTrackerCourse?.enrollments?.length ?? 0 }} participants enrolled
                                </p>
                            </div>
                            <a v-if="selectedTrackerCourse?.id" :href="route('expert.courses.report', selectedTrackerCourse.id)" target="_blank"
                               class="flex items-center gap-2 bg-emerald-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl hover:bg-emerald-700 shadow-md shadow-emerald-100 transition-all">
                                <Download class="w-3.5 h-3.5" /> Export CSV
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b border-slate-50">
                                        <th class="px-8 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Participant</th>
                                        <th class="px-6 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Attendance</th>
                                        <th class="px-6 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Pre</th>
                                        <th class="px-6 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Post</th>
                                        <th class="px-6 py-3.5 text-[10px] font-black text-emerald-600 uppercase tracking-widest text-center">Δ Delta</th>
                                        <th class="px-8 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-if="!selectedTrackerCourse?.enrollments?.length">
                                        <td colspan="6" class="px-8 py-16 text-center">
                                            <Users class="w-10 h-10 text-slate-200 mx-auto mb-3" />
                                            <p class="text-slate-300 font-bold text-sm">No enrollments yet for this program.</p>
                                        </td>
                                    </tr>
                                    <tr v-for="enrollment in selectedTrackerCourse?.enrollments" :key="enrollment.id"
                                        class="hover:bg-indigo-50/30 transition-colors group">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div :class="['w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs flex-shrink-0', getAvatarColor(enrollment.id)]">
                                                    {{ getInitials(enrollment.user?.name) }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-900 text-sm">{{ enrollment.user?.name ?? '—' }}</p>
                                                    <p class="text-[11px] text-slate-400 font-medium">{{ enrollment.user?.email ?? '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span :class="enrollment.attended_at ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200'"
                                                  class="text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-full border">
                                                {{ enrollment.attended_at ? '✓ Attended' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="font-black text-slate-600 text-sm">{{ enrollment.pretest_score ?? '—' }}</span>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="font-black text-slate-600 text-sm">{{ enrollment.posttest_score ?? '—' }}</span>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span v-if="enrollment.score_delta != null"
                                                  class="inline-flex items-center gap-1 text-emerald-600 font-black text-sm bg-emerald-50 px-3 py-1 rounded-full">
                                                +{{ enrollment.score_delta }}%
                                            </span>
                                            <span v-else class="text-slate-300 font-bold">—</span>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-2">
                                                <div :class="[
                                                    'w-2 h-2 rounded-full flex-shrink-0',
                                                    enrollment.status === 'completed' ? 'bg-emerald-500' :
                                                    enrollment.status === 'l3_submitted' ? 'bg-amber-400' :
                                                    'bg-slate-200'
                                                ]"></div>
                                                <span class="text-[11px] font-bold text-slate-600 capitalize">
                                                    {{ (enrollment.status ?? '').replace(/_/g, ' ') }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Empty state (no course selected) -->
                    <div v-else class="h-full flex flex-col items-center justify-center bg-slate-50/50 rounded-2xl border-2 border-dashed border-slate-100 p-16 text-center">
                        <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 border border-slate-100 shadow-sm">
                            <BarChart3 class="w-10 h-10 text-slate-200" />
                        </div>
                        <h4 class="font-black text-slate-300 text-lg">Select a program</h4>
                        <p class="text-slate-300 text-sm mt-1">Choose a program from the left to view participant analytics.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
             MODAL — Create New Program
        ══════════════════════════════════════════ -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="2xl">
            <div class="relative overflow-hidden bg-white">
                <!-- Decorative bg -->
                <div class="absolute top-0 right-0 w-80 h-80 bg-gradient-to-br from-indigo-100 to-violet-50 rounded-full blur-3xl opacity-50 -mr-40 -mt-40 pointer-events-none"></div>

                <div class="px-10 pt-10 pb-6 relative z-10">
                    <div class="flex items-start justify-between mb-8">
                        <div>
                            <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                                <span class="w-6 h-px bg-indigo-600"></span> New Program
                            </div>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight">Design a Training Program</h3>
                            <p class="text-slate-400 font-medium mt-1">Architect your next behavioral transformation experience.</p>
                        </div>
                        <button @click="showCreateModal = false"
                                class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all flex-shrink-0 mt-1">
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- AI Prompt bar -->
                    <div class="mb-8 bg-gradient-to-r from-indigo-600 to-violet-600 rounded-2xl p-1 shadow-xl shadow-indigo-200">
                        <div class="bg-indigo-900/30 backdrop-blur rounded-xl p-4 flex flex-col sm:flex-row items-center gap-4">
                            <div class="p-3 bg-white/10 rounded-xl border border-white/20">
                                <Sparkles class="w-6 h-6 text-white" :class="isGeneratingAI ? 'animate-spin' : 'animate-pulse'" />
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <p class="text-white font-black text-sm">AI Architect</p>
                                <p class="text-indigo-200 text-xs font-medium opacity-80">Describe a topic and let AI scaffold your program.</p>
                            </div>
                            <div class="w-full sm:w-auto flex items-center gap-2 bg-white/10 rounded-xl px-3 py-2 border border-white/10">
                                <input v-model="aiTopic" type="text" @keyup.enter="generateWithAI"
                                       placeholder="e.g. Leadership, Data Analysis…"
                                       class="bg-transparent border-none text-white text-sm placeholder-white/40 font-medium focus:ring-0 w-full sm:w-40 outline-none" />
                                <button @click="generateWithAI" :disabled="isGeneratingAI"
                                        class="bg-white text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-black hover:bg-indigo-50 transition-all disabled:opacity-50 whitespace-nowrap">
                                    <Loader2 v-if="isGeneratingAI" class="w-3.5 h-3.5 animate-spin" />
                                    <span v-else>Generate</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submitCreateCourse" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Program Title</label>
                            <input v-model="createForm.title" type="text" required
                                   class="w-full px-5 py-4 rounded-xl border-2 border-slate-100 bg-slate-50 focus:border-indigo-400 focus:bg-white transition-all font-bold text-slate-900 outline-none text-lg"
                                   placeholder="e.g. Strategic Leadership 2026" />
                            <InputError :message="createForm.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Vision & Description</label>
                            <textarea v-model="createForm.description" required rows="3"
                                      class="w-full px-5 py-4 rounded-xl border-2 border-slate-100 bg-slate-50 focus:border-indigo-400 focus:bg-white transition-all font-medium text-slate-700 resize-none outline-none"
                                      placeholder="What behavioral transformation will participants achieve?"></textarea>
                            <InputError :message="createForm.errors.description" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Passing Grade (%)</label>
                                <input v-model="createForm.passing_grade" type="number" min="0" max="100" required
                                       class="w-full px-5 py-4 rounded-xl border-2 border-slate-100 bg-slate-50 focus:border-indigo-400 focus:bg-white transition-all font-black text-xl text-slate-900 outline-none" />
                                <InputError :message="createForm.errors.passing_grade" class="mt-2" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit" :disabled="createForm.processing"
                                        class="w-full py-4 bg-indigo-600 text-white rounded-xl font-black text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all hover:-translate-y-0.5 active:scale-95 disabled:opacity-50">
                                    {{ createForm.processing ? 'Creating…' : 'Create Program' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
</style>
