<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ChevronLeft, 
    ChevronDown, 
    CheckCircle2, 
    Lock, 
    Target, 
    UploadCloud,
    Rocket,
    Check
} from 'lucide-vue-next';

// Dummy Gamification Course Data
const course = ref({
    title: 'Advanced SEO Mastery',
    progress: 60,
    modules: [
        {
            id: 1,
            title: 'Module 1: Fundamental',
            expanded: false,
            items: [
                { id: 101, title: 'Understanding Crawl Budget', type: 'visual', status: 'completed' },
            ]
        },
        {
            id: 2,
            title: 'Module 2: Advanced Execution',
            expanded: true,
            items: [
                { id: 201, title: 'Understanding Crawl Budget', type: 'literal', status: 'completed' },
                { id: 202, title: 'FINAL MISSION: Create SEO Audit Report', type: 'exercise', status: 'active', hasQuizHealth: true },
                { id: 203, title: 'Understanding Crawl Budget', type: 'knowledge', status: 'locked' },
                { id: 204, title: 'Actionable Recommendations', type: 'visual', status: 'locked' }
            ]
        }
    ]
});

const activeItemId = ref(202);
const expandedModules = ref([2]);

const toggleModule = (id) => {
    if (expandedModules.value.includes(id)) {
        expandedModules.value = expandedModules.value.filter(mId => mId !== id);
    } else {
        expandedModules.value.push(id);
    }
};

const selectItem = (item) => {
    if (item.status !== 'locked') {
        activeItemId.value = item.id;
    }
};

const getStatusClasses = (item) => {
    if (item.id === activeItemId.value) {
        return 'bg-indigo-50 border-indigo-200 text-indigo-900 border font-semibold shadow-sm';
    }
    if (item.status === 'locked') {
        return 'text-slate-400 opacity-60 cursor-not-allowed';
    }
    if (item.status === 'completed') {
        return 'text-slate-700 hover:bg-slate-50';
    }
    return 'text-slate-600 hover:bg-slate-50';
};
</script>

<template>
    <Head title="Learner Classroom" />

    <div class="h-screen flex bg-white font-sans overflow-hidden">
        
        <!-- Sidebar Kiri: Curriculum Map -->
        <aside class="w-80 flex-shrink-0 border-r border-slate-200 flex flex-col h-full bg-white z-20 shadow-[2px_0_10px_rgba(0,0,0,0.02)]">
            
            <!-- Navbar Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white text-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center shadow-md">
                        <span class="text-white font-black text-sm">M</span>
                    </div>
                    <span class="font-black text-lg tracking-tight">MicroEducate</span>
                </div>
            </div>

            <!-- Header Sidebar (Progress) -->
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest leading-tight">Course</p>
                        <h1 class="text-base font-black text-slate-900 leading-tight pr-4">{{ course.title }}</h1>
                    </div>
                    
                    <!-- Progress Tracking (Donut Chart) -->
                    <div class="relative w-16 h-16 flex-shrink-0">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                            <!-- Background Circle -->
                            <path class="text-slate-100" stroke-width="4" stroke="currentColor" fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <!-- Progress Circle -->
                            <path class="text-indigo-600 drop-shadow-md transition-all duration-1000 ease-out" stroke-dasharray="100, 100" stroke-width="4" stroke="currentColor" fill="none"
                                :stroke-dashoffset="100 - course.progress"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xs font-black text-slate-900 leading-none">{{ course.progress }}%</span>
                            <span class="text-[6px] font-bold text-slate-500 uppercase">Complete</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Curriculum Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                <div v-for="mod in course.modules" :key="mod.id" class="border border-slate-200 rounded-2xl overflow-hidden bg-white">
                    <!-- Module Header -->
                    <button @click="toggleModule(mod.id)"
                            class="w-full flex items-center justify-between p-4 bg-white hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2">
                             <CheckCircle2 v-if="mod.id === 1" class="w-4 h-4 text-slate-300" />
                             <div v-else class="w-4 h-4">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                             </div>
                            <span class="text-xs font-black text-slate-800">{{ mod.title }}</span>
                        </div>
                        <ChevronDown :class="{ 'rotate-180': expandedModules.includes(mod.id) }" class="w-4 h-4 text-slate-400 transition-transform" />
                    </button>

                    <!-- Items List -->
                    <div v-show="expandedModules.includes(mod.id)" class="px-2 pb-2 space-y-1 bg-white border-t border-slate-50">
                        <button v-for="item in mod.items" :key="item.id"
                                @click="selectItem(item)"
                                :disabled="item.status === 'locked'"
                                :class="[
                                    'w-full flex items-center justify-between p-3 rounded-xl transition-all text-left mt-1',
                                    getStatusClasses(item)
                                ]">
                            
                            <div class="flex items-center gap-3">
                                <!-- Icon Status Indikator Kiri -->
                                <CheckCircle2 v-if="item.status === 'completed'" class="w-5 h-5 text-emerald-500 fill-emerald-100/50 flex-shrink-0" />
                                <Target v-else-if="item.id === activeItemId" class="w-5 h-5 text-indigo-600 flex-shrink-0" />
                                <Lock v-else-if="item.status === 'locked'" class="w-4 h-4 text-slate-300 ml-0.5 flex-shrink-0" />
                                <div v-else class="w-5 h-5 ml-0.5"></div>

                                <span class="text-xs pr-2" :class="item.id === activeItemId ? 'font-black text-indigo-900' : (item.status === 'locked' ? 'font-medium' : 'font-bold')">{{ item.title }}</span>
                            </div>

                            <!-- Indikator Kanan: Quiz Health dot (Gamification trick) -->
                            <div v-if="item.hasQuizHealth" class="flex flex-col items-center justify-center flex-shrink-0 ml-1">
                                <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full mb-0.5 shadow-[0_0_8px_rgba(16,185,129,0.8)] animate-pulse"></div>
                                <span class="text-[8px] font-black text-emerald-600 leading-none">Quiz<br/>Health</span>
                            </div>
                            <Lock v-else-if="item.status === 'locked'" class="w-4 h-4 text-slate-300 flex-shrink-0" />
                        </button>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Kanvas Kanan: Content Area (Theater View) -->
        <main class="flex-1 bg-slate-50/50 overflow-y-auto relative py-12 px-8">
            
            <div class="max-w-4xl mx-auto">
                <h2 class="text-lg font-black text-slate-800 tracking-tight mb-6 flex items-center gap-2">
                    Exercise
                </h2>

                <!-- Exercise Module Canvas -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-700">
                    
                    <div class="p-10 space-y-8">
                        <!-- Mission Header -->
                        <div class="flex items-start justify-between border-b border-slate-100 pb-6">
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Final Mission: Create SEO Audit Report</h3>
                            <div class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg border border-blue-100 text-xs font-bold whitespace-nowrap shadow-sm">
                                Kirkpartick L3: In Progress
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div>
                            <h4 class="text-sm font-black text-slate-800 mb-3">Instructions</h4>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Develop a comprehensive SEO audit report for your website, focusing on technical issues, on-page factors, and competitive landscape. Max 10 pages.
                            </p>
                        </div>

                        <!-- Evaluation Rubric -->
                        <div>
                            <h4 class="text-sm font-black text-slate-800 mb-4">Evaluation Rubric (Expert Graded)</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                                        <span class="text-slate-700 font-medium">Technical Audit Accuracy</span>
                                    </div>
                                    <span class="font-bold text-slate-900">20 pts</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                                        <span class="text-slate-700 font-medium">Competitor Analysis Depth</span>
                                    </div>
                                    <span class="font-bold text-slate-900">30 pts</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                                        <span class="text-slate-700 font-medium">Actionable Recommendations</span>
                                    </div>
                                    <span class="font-bold text-slate-900">40 pts</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                                        <span class="text-slate-700 font-medium">Report Professionalism</span>
                                    </div>
                                    <span class="font-bold text-slate-900">10 pts</span>
                                </div>
                                <div class="flex justify-between items-center pt-3 border-t border-slate-100 mt-2">
                                    <span class="font-black text-slate-900 text-sm">Sumi</span> <!-- Tidy up Sum/Total -->
                                    <span class="font-black text-slate-900 text-sm">100 pts</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submission Zone -->
                        <div>
                            <h4 class="text-sm font-black text-slate-800 mb-4">Submission Zone</h4>
                            <div class="border-2 border-dashed border-slate-300 rounded-2xl p-10 text-center hover:bg-slate-50 transition-colors cursor-pointer group">
                                <UploadCloud class="w-10 h-10 text-slate-400 mx-auto mb-3 group-hover:scale-110 group-hover:text-indigo-500 transition-all duration-300" />
                                <p class="text-sm font-bold text-slate-900 mb-1">Drag & drop your SEO Audit Report here</p>
                                <p class="text-xs text-indigo-600 font-semibold mb-3 underline underline-offset-2">click to upload</p>
                                <p class="text-[10px] text-slate-500 font-medium">Supported file types: PDF, DOCX, maximum 25 MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="bg-slate-50/80 px-10 py-5 border-t border-slate-100 flex items-center justify-between">
                        <button class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95">
                            Save Progress
                        </button>
                        
                        <button class="px-6 py-2.5 bg-[#4F39F6] hover:bg-[#3d2bd1] text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center gap-2">
                            <Rocket class="w-4 h-4" />
                            Submit Mission for Expert Review
                        </button>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
