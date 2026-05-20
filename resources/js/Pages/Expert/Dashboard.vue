<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Users, Award, TrendingUp, Search, Plus, 
    BookOpen, User, ArrowRight, X, Loader2,
    ShieldCheck, Bell, Sparkles, Filter, 
    ChevronRight, MoreVertical, Rocket, Target, Zap
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    assignments: {
        type: Array,
        default: () => []
    },
    courses: {
        type: Array,
        default: () => []
    },
    expertStats: {
        type: Object,
        default: () => ({
            total_students: 0,
            certifications_issued: 0,
            success_velocity: 0
        })
    },
    defaultTemplate: Object,
});

const searchQuery = ref('');
const showCreateModal = ref(false);
const isGeneratingAI = ref(false);
const aiTopic = ref('');

const createForm = useForm({
    title: '',
    description: '',
    passing_grade: 75,
});

const filteredCourses = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.courses;
    return props.courses.filter(c => 
        c.title.toLowerCase().includes(q) || 
        c.description?.toLowerCase().includes(q)
    );
});

const pendingReviewsCount = computed(() => props.assignments?.length || 0);

const submitCreateCourse = () => {
    createForm.post(route('expert.courses.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

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

const quickTemplates = [
    { title: 'Bootcamp', prompt: '4-week intensive bootcamp for ', icon: Rocket, color: 'text-orange-600', bg: 'bg-orange-50' },
    { title: 'Onboarding', prompt: 'Employee onboarding program for ', icon: Users, color: 'text-blue-600', bg: 'bg-blue-50' },
    { title: 'Technical', prompt: 'Technical deep-dive workshop for ', icon: Target, color: 'text-purple-600', bg: 'bg-purple-50' }
];

const useQuickTemplate = (template) => {
    aiTopic.value = template.prompt;
    showCreateModal.value = true;
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
};
</script>

<template>
    <Head title="Expert Dashboard" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
            
            <!-- 1. HERO SECTION -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">
                        Welcome back, {{ user?.name || 'Expert' }}<span class="text-indigo-600">.</span>
                    </h1>
                    <p class="text-slate-500 font-medium text-lg">
                        Managing {{ props.courses?.length || 0 }} transformational programs.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Action buttons removed per user request -->
                </div>
            </div>

            <!-- 2. TOP METRICS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Total Active Learners -->
                <div class="bg-white p-8 rounded-[2rem] border border-slate-200/60 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 border border-blue-100/50">
                            <Users class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Total Active Learners</p>
                            <h3 class="text-4xl font-black text-slate-900 mt-1">{{ props.expertStats.total_students || 0 }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Average Completion Rate -->
                <div class="bg-white p-8 rounded-[2rem] border border-slate-200/60 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 border border-emerald-100/50">
                            <TrendingUp class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Avg. Completion Rate</p>
                            <h3 class="text-4xl font-black text-slate-900 mt-1">{{ props.expertStats.success_velocity || 0 }}%</h3>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Action Alert (Pending Reviews) -->
                <Link :href="route('expert.review-queue')" 
                      class="bg-white p-8 rounded-[2rem] border border-slate-200/60 shadow-sm hover:shadow-xl hover:shadow-purple-100 hover:border-purple-200 transition-all group overflow-hidden relative block">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 border border-purple-100/50 group-hover:bg-purple-600 group-hover:text-white transition-all">
                            <Award class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Action Required</p>
                            <h3 class="text-2xl font-black mt-1" :class="pendingReviewsCount > 0 ? 'text-purple-600' : 'text-slate-900'">
                                {{ pendingReviewsCount }} L3 Missions <span class="text-sm font-bold text-slate-400 block sm:inline">pending review</span>
                            </h3>
                        </div>
                    </div>
                    <!-- Subtle active indicator if > 0 -->
                    <div v-if="pendingReviewsCount > 0" class="absolute top-4 right-8 flex items-center gap-1">
                        <span class="w-2 h-2 bg-purple-500 rounded-full animate-ping"></span>
                        <span class="text-[9px] font-black text-purple-600 uppercase tracking-widest">Active</span>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-[10px] font-black text-purple-600 opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                        OPEN QUEUE <ArrowRight class="w-3 h-3" />
                    </div>
                </Link>
            </div>

            <!-- 3. CATALOG HEADER SECTION -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pt-6 mb-2">
                <div class="flex items-center gap-4">
                   <div class="w-1 h-10 bg-indigo-600 rounded-full"></div>
                   <h2 class="text-2xl font-black text-slate-900 tracking-tight">Your Programs</h2>
                </div>
                
                <div class="flex items-center gap-4 flex-1 max-w-2xl justify-end">
                    <!-- Search Bar -->
                    <div class="relative flex-1 group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                        <input v-model="searchQuery" type="text" placeholder="Search by title..." 
                               class="w-full pl-12 pr-4 py-3 bg-slate-100 border-transparent rounded-[1.25rem] font-bold text-sm text-slate-700 focus:bg-white transition-all outline-none" />
                    </div>
                    
                    <!-- CTA Button -->
                    <button @click="showCreateModal = true" 
                            class="bg-slate-900 text-white px-6 py-3 rounded-[1.25rem] font-black text-sm flex items-center gap-2 shadow-xl shadow-slate-200 hover:bg-indigo-600 hover:shadow-indigo-100 hover:-translate-y-0.5 transition-all active:scale-95 flex-shrink-0">
                        <Plus class="w-4 h-4" /> New Program
                    </button>
                </div>
            </div>

            <!-- 4. PROGRAM GRID -->
            <div v-if="filteredCourses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="course in filteredCourses" :key="course.id" 
                     class="group bg-white rounded-[2.5rem] border border-slate-200/50 shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 hover:border-indigo-100 transition-all duration-500 overflow-hidden flex flex-col h-full bg-gradient-to-b hover:from-indigo-50/20 hover:to-white">
                    
                    <!-- Card Cover -->
                    <div class="h-48 relative overflow-hidden bg-slate-900 flex items-center justify-center">
                        <img v-if="course.thumbnail_path" :src="'/storage/' + course.thumbnail_path"
                             class="w-full h-full object-cover opacity-60 group-hover:opacity-80 transition-opacity duration-700 group-hover:scale-110" />
                        <div v-else class="absolute inset-0 bg-gradient-to-br from-slate-800 to-slate-950 flex items-center justify-center">
                            <BookOpen class="w-16 h-16 text-slate-700/50 transition-colors" />
                        </div>
                        
                        <!-- Top Badges -->
                        <div class="absolute top-6 left-6 right-6 flex justify-between items-center">
                            <span :class="course.status === 'published' ? 'bg-emerald-500 text-white shadow-emerald-200' : 'bg-slate-200 text-slate-700 shadow-slate-100'"
                                  class="text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg backdrop-blur-md border border-white/20">
                                {{ course.status || 'Draft' }}
                            </span>
                        </div>
                        
                        <!-- Registered Floating Badge -->
                        <div class="absolute bottom-6 left-6 bg-white/10 backdrop-blur-xl border border-white/20 px-3 py-1.5 rounded-2xl flex items-center gap-1.5 shadow-lg group-hover:bg-white/20">
                            <User class="w-3.5 h-3.5 text-white" />
                            <span class="text-[11px] font-black text-white">{{ course.enrollments_count || 0 }}</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-8 flex flex-col flex-1 space-y-6">
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em]">{{ course.section?.name || 'Academic Core' }}</p>
                            <h3 class="text-xl font-black text-slate-900 line-clamp-1 group-hover:text-indigo-600 transition-colors">
                                {{ course.title }}
                            </h3>
                        </div>

                        <p class="text-sm text-slate-500 font-medium line-clamp-2 leading-relaxed">
                            {{ course.description || 'No description provided.' }}
                        </p>

                        <!-- Bottom Stats & Action -->
                        <div class="pt-6 border-t border-slate-50 mt-auto flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Yield</span>
                                    <span class="font-black text-slate-900 font-mono text-sm">${{ course.price || 0 }}</span>
                                </div>
                                <div class="w-px h-6 bg-slate-100"></div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Target</span>
                                    <span class="font-black text-slate-900 text-sm">{{ course.passing_grade }}%</span>
                                </div>
                            </div>
                            
                            <Link :href="route('expert.courses.builder', course.id)" 
                                  class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white group-hover:shadow-xl group-hover:shadow-indigo-100 transition-all duration-300">
                                <ArrowRight class="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-20 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm flex flex-col items-center justify-center space-y-12">
                <div class="space-y-6 flex flex-col items-center">
                    <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200">
                        <BookOpen class="w-12 h-12" />
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-2xl font-black text-slate-900">No programs started yet</h3>
                        <p class="text-slate-400 font-medium max-w-sm mx-auto">Click "New Program" or choose a blueprint below to start architecturing.</p>
                    </div>
                </div>

                <!-- Quick Blueprint Selection -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 w-full max-w-4xl px-10">
                    <button v-for="temp in quickTemplates" :key="temp.title"
                            @click="useQuickTemplate(temp)"
                            class="flex flex-col items-center p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-indigo-50 hover:border-indigo-100 transition-all group">
                        <div :class="[temp.bg, temp.color]" class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <component :is="temp.icon" class="w-8 h-8" />
                        </div>
                        <span class="text-sm font-black text-slate-900 mb-1">{{ temp.title }}</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Blueprint</span>
                    </button>
                </div>

                <button @click="showCreateModal = true" class="px-8 py-4 bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:bg-slate-900 hover:shadow-none transition-all">
                    Initiate Custom Program
                </button>
            </div>
        </div>

        <!-- Create New Program Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="2xl">
            <div class="relative overflow-hidden bg-white">
                <div class="absolute top-0 right-0 w-80 h-80 bg-gradient-to-br from-indigo-100 to-violet-50 rounded-full blur-3xl opacity-50 -mr-40 -mt-40 pointer-events-none"></div>

                <div class="px-10 pt-12 pb-10 relative z-10">
                    <div class="flex items-start justify-between mb-10">
                        <div>
                            <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                                <span class="w-6 h-px bg-indigo-600"></span> Initiation Phase
                            </div>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight">Design New Program</h3>
                            <p class="text-slate-500 font-medium mt-1">Define the core vision of your academy product.</p>
                        </div>
                        <button @click="showCreateModal = false"
                                class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- AI Assistant Section -->
                    <div class="mb-10 bg-slate-900 rounded-[2rem] p-8 shadow-2xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent"></div>
                        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
                            <div class="w-14 h-14 bg-indigo-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <Sparkles class="w-7 h-7 text-white" :class="isGeneratingAI ? 'animate-spin' : ''" />
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <h4 class="text-white font-black text-lg">AI Architect Assistant</h4>
                                <p class="text-indigo-200/70 text-sm font-medium">Scaffold your entire curriculum structure from a single prompt.</p>
                            </div>
                        </div>
                        <div class="mt-6 flex items-center gap-3 bg-white/10 rounded-2xl p-2 border border-white/10">
                            <input v-model="aiTopic" type="text" @keyup.enter="generateWithAI"
                                   placeholder="What do you want to teach?" 
                                   class="bg-transparent border-none text-white text-sm placeholder-white/30 font-medium focus:ring-0 w-full px-4 outline-none" />
                            <button @click="generateWithAI" :disabled="isGeneratingAI"
                                    class="bg-white text-slate-900 px-6 py-2.5 rounded-xl text-xs font-black hover:bg-indigo-400 hover:text-white transition-all disabled:opacity-50">
                                <span v-if="!isGeneratingAI">Scaffold</span>
                                <Loader2 v-else class="w-4 h-4 animate-spin" />
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submitCreateCourse" class="space-y-8">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Program Title</label>
                            <input v-model="createForm.title" type="text" required
                                   class="w-full px-6 py-4 rounded-2xl border-none bg-slate-50 focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-bold text-slate-900 outline-none shadow-inner"
                                   placeholder="e.g. Masterclass: System Architecture" />
                            <InputError :message="createForm.errors.title" />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Vision Statement</label>
                            <textarea v-model="createForm.description" required rows="4"
                                      class="w-full px-6 py-4 rounded-2xl border-none bg-slate-50 focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-medium text-slate-700 resize-none outline-none shadow-inner"
                                      placeholder="Describe the impact this program will have on participants..."></textarea>
                            <InputError :message="createForm.errors.description" />
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Passing Grade (%)</label>
                                <input v-model="createForm.passing_grade" type="number" min="0" max="100" required
                                       class="w-full px-6 py-4 rounded-2xl border-none bg-slate-50 focus:ring-8 focus:ring-indigo-50 focus:bg-white transition-all font-black text-xl text-slate-900 outline-none shadow-inner" />
                                <InputError :message="createForm.errors.passing_grade" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit" :disabled="createForm.processing"
                                        class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-sm shadow-2xl shadow-indigo-200 hover:bg-slate-900 hover:shadow-none transition-all active:scale-95 disabled:opacity-50">
                                    {{ createForm.processing ? 'Designing...' : 'Launch Designer' }}
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
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 1; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 2; }
</style>
