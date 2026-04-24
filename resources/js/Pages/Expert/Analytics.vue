<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { 
    BarChart3, 
    Download, 
    Users, 
    ChevronRight,
    TrendingUp,
    Award,
    Sparkles,
    ArrowLeft,
    Clock,
    CheckCircle2,
    Target,
    FileText,
    ExternalLink
} from 'lucide-vue-next';

const props = defineProps({
    courses: Array,
    selectedCourseId: [String, Number]
});

const selectedTrackerCourse = ref(null);
const selectedEnrollment = ref(null);
const showDetailSlideOver = ref(false);

const openDetail = (enrollment) => {
    selectedEnrollment.value = enrollment;
    showDetailSlideOver.value = true;
};

onMounted(() => {
    if (props.selectedCourseId) {
        selectedTrackerCourse.value = props.courses.find(c => c.id == props.selectedCourseId);
    } else if (props.courses?.length > 0) {
        selectedTrackerCourse.value = props.courses[0];
    }
});

const getAvatarColor = (id) => {
    const colors = [
        'bg-indigo-100 text-indigo-700 border-indigo-200',
        'bg-emerald-100 text-emerald-700 border-emerald-200',
        'bg-amber-100 text-amber-700 border-amber-200',
        'bg-rose-100 text-rose-700 border-rose-200',
        'bg-purple-100 text-purple-700 border-purple-200',
        'bg-cyan-100 text-cyan-700 border-cyan-200'
    ];
    return colors[id % colors.length];
};

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};
</script>

<template>
    <Head title="Insights & Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between py-6">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                        <span class="w-8 h-px bg-indigo-600"></span> Expert Analytics
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">
                        Impact Tracker<span class="text-indigo-600">.</span>
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm hidden md:flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                            <TrendingUp class="text-emerald-600 w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Network Growth</p>
                            <p class="text-xs text-emerald-600 font-black">+12.4% vs last month</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-700">
            <div class="flex gap-8" style="min-height: 600px;">
                <!-- Sidebar: Program Selection -->
                <div class="w-80 flex-shrink-0 space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Source Program</p>
                        <span class="bg-slate-100 text-slate-400 text-[10px] font-black px-2 py-0.5 rounded-md">{{ courses?.length ?? 0 }} Total</span>
                    </div>
                    
                    <div class="space-y-2 max-h-[600px] overflow-y-auto pr-2 scrollbar-thin">
                        <button v-for="course in courses" :key="course.id"
                                @click="selectedTrackerCourse = course"
                                :class="selectedTrackerCourse?.id === course.id
                                    ? 'bg-slate-900 text-white border-slate-900 shadow-xl shadow-slate-200'
                                    : 'bg-white text-slate-700 border-slate-100 hover:border-indigo-200 hover:shadow-sm'"
                                class="w-full text-left px-6 py-5 rounded-3xl border transition-all duration-300 group relative overflow-hidden">
                            <div v-if="selectedTrackerCourse?.id === course.id" class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -mr-12 -mt-12"></div>
                            <div class="relative z-10">
                                <h5 class="font-black text-sm truncate leading-none mb-2">{{ course?.title }}</h5>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1 opacity-60">
                                        <Users class="w-3 h-3" />
                                        <span class="text-[10px] font-bold">{{ course?.enrollments_count ?? 0 }}</span>
                                    </div>
                                    <span class="w-1 h-1 bg-current opacity-20 rounded-full"></span>
                                    <p class="text-[10px] font-bold opacity-60">
                                        {{ course?.created_at ? new Date(course.created_at).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : '' }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </div>

                    <div v-if="!courses?.length" class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl p-10 text-center">
                        <p class="text-slate-400 font-bold text-sm">No programs found.</p>
                    </div>
                </div>

                <!-- Main: Detailed Analytics -->
                <div class="flex-1 min-w-0">
                    <div v-if="selectedTrackerCourse" class="space-y-6">
                        <!-- Header Card -->
                        <div class="bg-white rounded-[3rem] border border-slate-100 shadow-xl p-10 relative overflow-hidden group">
                           <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-50 rounded-full blur-[100px] -mr-48 -mt-48 opacity-50 group-hover:bg-indigo-100 transition-colors duration-700"></div>
                           
                           <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
                               <div>
                                   <div class="flex items-center gap-3 mb-4">
                                       <div class="bg-indigo-600 p-3 rounded-2xl shadow-lg shadow-indigo-100">
                                           <BarChart3 class="w-6 h-6 text-white" />
                                       </div>
                                       <span class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.3em]">Program Performance</span>
                                   </div>
                                   <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ selectedTrackerCourse?.title }}</h3>
                                   <p class="text-slate-400 font-bold text-sm">{{ selectedTrackerCourse?.enrollments?.length ?? 0 }} participants enrolled in this cycle.</p>
                               </div>
                               <div class="flex gap-3">
                                    <a :href="route('expert.courses.report', selectedTrackerCourse.id)"
                                       class="flex items-center gap-3 bg-slate-900 text-white px-8 py-5 rounded-[2rem] font-black text-sm shadow-2xl hover:bg-emerald-600 transition-all hover:-translate-y-1 active:scale-95">
                                        <Download class="w-5 h-5 text-emerald-400" /> Export Performance (CSV)
                                    </a>
                               </div>
                           </div>
                        </div>

                        <!-- Grid -->
                        <div class="bg-white rounded-[3.5rem] border border-slate-100 shadow-2xl overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50/50">
                                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Participant</th>
                                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Engagement</th>
                                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Diagnostic Score</th>
                                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Mastery Score</th>
                                            <th class="px-6 py-6 text-[10px] font-black text-emerald-600 uppercase tracking-widest text-center">Δ Alpha Delta</th>
                                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Journey Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-if="!selectedTrackerCourse?.enrollments?.length">
                                            <td colspan="6" class="px-10 py-24 text-center">
                                                <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                                                    <Users class="w-10 h-10 text-slate-200" />
                                                </div>
                                                <p class="text-slate-900 font-black text-xl mb-1">Zero Activity Nodes.</p>
                                                <p class="text-slate-400 font-bold text-sm">No enrollments detected for this specific program yet.</p>
                                            </td>
                                        </tr>
                                        <tr v-for="enrollment in selectedTrackerCourse?.enrollments" :key="enrollment.id"
                                            @click="openDetail(enrollment)"
                                            class="group hover:bg-slate-50/50 transition-colors cursor-pointer">
                                            <td class="px-10 py-8">
                                                <div class="flex items-center gap-4">
                                                    <div :class="['w-14 h-14 rounded-[1.25rem] flex items-center justify-center font-black text-sm border-2 shadow-sm', getAvatarColor(enrollment.id)]">
                                                        {{ getInitials(enrollment.user?.name) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-black text-slate-900 text-lg leading-none mb-1">{{ enrollment.user?.name ?? 'Anonymous User' }}</p>
                                                        <p class="text-xs text-slate-400 font-bold">{{ enrollment.user?.email ?? 'no-email@system.local' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-8 text-center">
                                                <div class="flex justify-center">
                                                    <div :class="enrollment.attended_at ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-300 border-slate-100'"
                                                         class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border">
                                                        {{ enrollment.attended_at ? '✓ Session Confirmed' : 'Sync Pending' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-8 text-center font-black text-slate-300 text-2xl group-hover:text-slate-900 transition-colors">
                                                {{ enrollment.pretest_score ?? '—' }}
                                            </td>
                                            <td class="px-6 py-8 text-center font-black text-slate-300 text-2xl group-hover:text-slate-900 transition-colors">
                                                {{ enrollment.posttest_score ?? '—' }}
                                            </td>
                                            <td class="px-6 py-8 text-center">
                                                <div class="flex justify-center">
                                                    <div v-if="enrollment.score_delta != null"
                                                          class="inline-flex items-center gap-2 text-emerald-700 font-black text-lg bg-emerald-50 px-5 py-2 rounded-2xl border border-emerald-100">
                                                        <TrendingUp class="w-5 h-5" />
                                                        +{{ enrollment.score_delta }}%
                                                    </div>
                                                    <span v-else class="text-slate-200 font-black text-2xl">—</span>
                                                </div>
                                            </td>
                                            <td class="px-10 py-8">
                                                <div class="flex items-center gap-3">
                                                    <div :class="[
                                                        'w-3 h-3 rounded-full shadow-sm',
                                                        enrollment.status === 'completed' ? 'bg-emerald-500 shadow-emerald-200' :
                                                        enrollment.status === 'l3_submitted' ? 'bg-amber-400 shadow-amber-200' :
                                                        'bg-slate-200'
                                                    ]"></div>
                                                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">
                                                        {{ (enrollment.status ?? 'idle').replace(/_/g, ' ') }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Idle State -->
                    <div v-else class="h-full flex flex-col items-center justify-center bg-slate-50/50 rounded-[4rem] border border-dashed border-slate-200 p-20 text-center animate-in fade-in zoom-in-95 duration-700">
                        <div class="w-32 h-32 bg-white rounded-[3rem] flex items-center justify-center mx-auto mb-10 border border-slate-100 shadow-2xl">
                            <Sparkles class="w-12 h-12 text-indigo-400" />
                        </div>
                        <h4 class="font-black text-slate-900 text-3xl tracking-tight mb-4">No Program Selected</h4>
                        <p class="text-slate-400 font-bold text-lg max-w-sm mx-auto">Choose a training program from the directory on the left to begin deep impact analysis.</p>
                    </div>
                </div>
            </div>

            <!-- Impact Deep-Dive Slide-over -->
            <div v-if="showDetailSlideOver" class="fixed inset-0 z-[100] overflow-hidden">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showDetailSlideOver = false"></div>
                <div class="absolute inset-y-0 right-0 max-w-2xl w-full bg-white shadow-[-40px_0_80px_rgba(0,0,0,0.1)] border-l border-slate-50 flex flex-col animate-in slide-in-from-right duration-500">
                    
                    <!-- Header -->
                    <div class="p-10 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div :class="['w-16 h-16 rounded-[1.5rem] flex items-center justify-center font-black text-xl border-2 shadow-lg', getAvatarColor(selectedEnrollment.id)]">
                                {{ getInitials(selectedEnrollment.user?.name) }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ selectedEnrollment.user?.name }}</h3>
                                <p class="text-slate-400 font-bold text-[10px] uppercase tracking-[0.2em] mt-1">{{ selectedTrackerCourse.title }}</p>
                            </div>
                        </div>
                        <button @click="showDetailSlideOver = false" class="w-12 h-12 hover:bg-slate-100 rounded-2xl flex items-center justify-center transition-all">
                            <ArrowLeft class="w-6 h-6 text-slate-400" />
                        </button>
                    </div>

                    <!-- Scroll Area -->
                    <div class="flex-1 overflow-y-auto p-10 space-y-12">
                        
                        <!-- 1. Delta Growth Audit (L2) -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <TrendingUp class="w-4 h-4" /> Pillar 02: Knowledge Growth
                                </h4>
                                <span class="bg-emerald-50 text-emerald-600 font-black text-[10px] px-3 py-1 rounded-full">Score Delta: +{{ selectedEnrollment.score_delta }}%</span>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-for="test in selectedEnrollment.l2_tests" :key="test.id" 
                                     class="p-6 bg-slate-50 rounded-3xl border border-slate-100 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
                                            <component :is="test.type === 'pre_test' ? Clock : CheckCircle2" 
                                                       :class="test.type === 'pre_test' ? 'text-amber-500' : 'text-emerald-500'" class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-900">{{ test.curriculum_item?.title || 'General Assessment' }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ test.type.replace('_', ' ') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-black text-slate-900">{{ test.score }}%</p>
                                    </div>
                                </div>
                                <div v-if="!selectedEnrollment.l2_tests?.length" class="py-10 text-center border-2 border-dashed border-slate-100 rounded-[2rem]">
                                    <p class="text-slate-400 font-bold text-xs">No assessment data found.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Evidence Timeline (L3) -->
                        <div class="space-y-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <Target class="w-4 h-4" /> Pillar 03: Behavior Evidence
                            </h4>
                            
                            <div class="space-y-6 relative border-l-2 border-slate-100 ml-5 pl-8 pb-1">
                                <div v-for="assign in selectedEnrollment.l3_assignments" :key="assign.id" class="relative group">
                                    <div class="absolute -left-[45px] top-0 w-8 h-8 rounded-full bg-white border-4 border-indigo-600 shadow-lg z-10"></div>
                                    <div class="bg-white border border-slate-100 p-8 rounded-[2rem] shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all">
                                        <div class="flex justify-between items-start mb-6">
                                            <div>
                                                <h5 class="font-black text-slate-900 text-lg leading-none mb-2">{{ assign.curriculum_item?.title || 'Submission' }}</h5>
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Submitted {{ new Date(assign.created_at).toLocaleDateString() }}</p>
                                            </div>
                                            <div :class="assign.status === 'approved' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'" 
                                                 class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                                                {{ assign.status }}
                                            </div>
                                        </div>
                                        <p class="text-slate-500 text-sm font-medium leading-relaxed mb-6 italic">"{{ assign.description }}"</p>
                                        
                                        <div class="flex gap-4">
                                            <a v-if="assign.file_path" :href="'/storage/' + assign.file_path" target="_blank"
                                               class="flex items-center gap-2 bg-slate-900 text-white px-4 py-2.5 rounded-xl font-black text-[10px] hover:bg-indigo-600 transition-all">
                                                <FileText class="w-3 h-3" /> View Evidence
                                            </a>
                                            <a v-if="assign.link_url" :href="assign.link_url" target="_blank"
                                               class="flex items-center gap-2 bg-indigo-50 text-indigo-600 px-4 py-2.5 rounded-xl font-black text-[10px] hover:bg-indigo-100 transition-all">
                                                <ExternalLink class="w-3 h-3" /> Visit Portfolio
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="!selectedEnrollment.l3_assignments?.length" class="py-10 text-center border-2 border-dashed border-slate-100 rounded-[2rem] -ml-8">
                                    <p class="text-slate-400 font-bold text-xs">No behavior evidence submitted yet.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 3. L1 Feedback -->
                        <div v-if="selectedEnrollment.l1_feedback" class="bg-indigo-900 rounded-[3rem] p-10 text-white relative overflow-hidden group">
                           <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-500 rounded-full blur-[60px] opacity-20 -mr-24 -mt-24 transition-transform group-hover:scale-150 duration-700"></div>
                           <h4 class="text-[10px] font-black uppercase tracking-widest text-indigo-300 mb-6 flex items-center gap-2">
                               <Sparkles class="w-4 h-4" /> Pillar 01: Reaction Feedback
                           </h4>
                           <div class="flex items-center gap-1 mb-6">
                               <Star v-for="i in 5" :key="i" :class="i <= selectedEnrollment.l1_feedback.rating ? 'text-amber-400 fill-amber-400' : 'text-indigo-800'" class="w-5 h-5" />
                           </div>
                           <p class="text-xl font-medium leading-relaxed italic text-indigo-50">"{{ selectedEnrollment.l1_feedback.review }}"</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-10 border-t border-slate-50 bg-slate-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                                    <Award class="text-indigo-600 w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Certification Status</p>
                                    <p class="text-sm font-black text-slate-900">{{ selectedEnrollment.certificate_unlocked ? 'AUTHENTICATED' : 'WAITING FOR L3 APPROVAL' }}</p>
                                </div>
                            </div>
                            <button v-if="selectedEnrollment.certificate_unlocked" class="px-6 py-3 bg-slate-900 text-white rounded-2xl font-black text-xs hover:bg-emerald-600 transition-all shadow-lg">Verify Certificate</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
</style>
