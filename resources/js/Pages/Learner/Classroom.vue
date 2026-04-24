<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ChevronLeft, 
    ChevronDown, 
    CheckCircle2, 
    Lock, 
    Play, 
    FileText, 
    HelpCircle, 
    Target, 
    UploadCloud,
    MoreHorizontal,
    Check
} from 'lucide-vue-next';

const props = defineProps({
    course: {
        type: Object,
        default: () => ({
            title: 'Advanced SEO & Holistic Strategy',
            progress: 45,
            modules: [
                {
                    id: 1,
                    title: 'Foundations of Advanced SEO',
                    expanded: true,
                    items: [
                        { id: 101, title: 'Evolving Search Landscape', type: 'literal', status: 'completed' },
                        { id: 102, title: 'Technical SEO Deep Dive', type: 'visual', status: 'active' },
                        { id: 103, title: 'Algorithm Trends 2026', type: 'knowledge', status: 'locked' }
                    ]
                },
                {
                    id: 2,
                    title: 'Content & Semantic Intelligence',
                    expanded: false,
                    items: [
                        { id: 201, title: 'Semantic Keyword Research', type: 'visual', status: 'locked' },
                        { id: 202, title: 'AI Content Orchestration', type: 'literal', status: 'locked' },
                        { id: 203, title: 'Final Mission: SEO Audit', type: 'exercise', status: 'locked' }
                    ]
                }
            ]
        })
    }
});

const activeItemId = ref(102);
const expandedModules = ref([1]);

const activeItem = computed(() => {
    for (const mod of props.course.modules) {
        const item = mod.items.find(i => i.id === activeItemId.value);
        if (item) return item;
    }
    return null;
});

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

const getItemIcon = (type) => {
    switch (type) {
        case 'visual': return Play;
        case 'knowledge': return HelpCircle;
        case 'exercise': return Target;
        default: return FileText;
    }
};

const rubrics = [
    { label: 'Technical Accuracy', desc: 'Expert will verify your site architecture findings.' },
    { label: 'Strategic Completeness', desc: 'Comprehensive coverage of E-E-A-T factors.' },
    { label: 'Actionable Insights', desc: 'Quality of recommendations provided to the client.' }
];

</script>

<template>
    <Head title="Learner Classroom" />

    <div class="h-screen flex bg-white overflow-hidden font-sans">
        
        <!-- Sidebar Kiri: Curriculum Map -->
        <aside class="w-80 flex-shrink-0 border-r border-slate-100 flex flex-col h-full bg-white z-20">
            <!-- Header Sidebar -->
            <div class="p-6 border-b border-slate-50">
                <Link :href="'/dashboard'" class="flex items-center gap-2 text-slate-400 hover:text-slate-900 transition-colors mb-6 group">
                    <ChevronLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                    <span class="text-[10px] font-black uppercase tracking-widest">Lobby Academy</span>
                </Link>
                
                <h1 class="text-lg font-black text-slate-900 leading-tight mb-4">{{ course.title }}</h1>
                
                <!-- Progress Bar -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-tighter">
                        <span class="text-slate-400">Course Progress</span>
                        <span class="text-indigo-600">{{ course.progress }}%</span>
                    </div>
                    <div class="h-1.5 w-full bg-slate-50 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-1000" :style="{ width: course.progress + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- Curriculum Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-4">
                <div v-for="(mod, mIdx) in course.modules" :key="mod.id" class="space-y-1">
                    <!-- Module Accordion Header -->
                    <button @click="toggleModule(mod.id)"
                            class="w-full flex items-center justify-between p-4 hover:bg-slate-50 rounded-2xl transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center font-black text-[10px]">
                                {{ mIdx + 1 }}
                            </div>
                            <span class="text-xs font-black text-slate-900 tracking-tight text-left">{{ mod.title }}</span>
                        </div>
                        <ChevronDown :class="{ 'rotate-180': expandedModules.includes(mod.id) }" class="w-4 h-4 text-slate-300 transition-transform" />
                    </button>

                    <!-- Items List -->
                    <div v-show="expandedModules.includes(mod.id)" class="px-2 space-y-1 animate-in slide-in-from-top-1 duration-300">
                        <button v-for="item in mod.items" :key="item.id"
                                @click="selectItem(item)"
                                :disabled="item.status === 'locked'"
                                :class="[
                                    'w-full flex items-center justify-between p-3 rounded-xl transition-all',
                                    item.id === activeItemId ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-500 hover:bg-slate-50',
                                    item.status === 'locked' ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                class="relative group">
                            
                            <div class="flex items-center gap-3">
                                <div :class="item.id === activeItemId ? 'bg-white shadow-sm' : 'bg-slate-50'" class="w-7 h-7 rounded-lg flex items-center justify-center">
                                    <component :is="getItemIcon(item.type)" :class="item.status === 'locked' ? 'text-slate-300' : (item.id === activeItemId ? 'text-indigo-600' : 'text-slate-400')" class="w-4 h-4" />
                                </div>
                                <span :class="item.id === activeItemId ? 'font-black' : 'font-bold'" class="text-[11px] text-left">{{ item.title }}</span>
                            </div>

                            <div class="flex items-center">
                                <CheckCircle2 v-if="item.status === 'completed'" class="w-4 h-4 text-emerald-500" />
                                <div v-else-if="item.id === activeItemId" class="w-1.5 h-1.5 bg-indigo-600 rounded-full animate-pulse mr-1"></div>
                                <Lock v-else-if="item.status === 'locked'" class="w-3 h-3 text-slate-300" />
                            </div>
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Bottom Actions -->
            <div class="p-6 border-t border-slate-50">
                <button class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100 rounded-2xl transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-slate-400 border border-slate-100">
                            <MoreHorizontal class="w-4 h-4" />
                        </div>
                        <span class="text-[10px] font-black uppercase text-slate-500">Resource Files</span>
                    </div>
                </button>
            </div>
        </aside>

        <!-- Kanvas Kanan: Content Area -->
        <main class="flex-1 bg-slate-50 overflow-y-auto relative p-12">
            
            <!-- Focus Header (Item Title) -->
            <div class="max-w-4xl mx-auto mb-10 flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ activeItem?.title }}</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-black uppercase tracking-widest text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Unit 0{{ activeItem?.id.toString().slice(-1) }}</span>
                        <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Curriculum Pillar: Holistic SEO</span>
                    </div>
                </div>
                <!-- Support/Context Button? -->
            </div>

            <!-- Content Container -->
            <div class="max-w-4xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">
                
                <!-- Mockup: Exercise/Mission Canvas -->
                <div v-if="activeItem?.type === 'exercise' || true">
                    <div class="space-y-8">
                        
                        <!-- Mission Card -->
                        <div class="bg-white rounded-[3rem] border border-slate-100 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.05)] overflow-hidden">
                            <div class="p-12">
                                <div class="bg-indigo-600 w-16 h-16 rounded-[1.5rem] flex items-center justify-center shadow-2xl shadow-indigo-200 mb-8">
                                    <Target class="w-8 h-8 text-white" />
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">Final Mission: Create SEO Audit Report</h3>
                                <p class="text-slate-500 font-medium leading-relaxed mb-10">
                                    Analyze the provided case study website and generate a comprehensive SEO audit report. You must address technical performance, semantic structure, and backlink health. Use the templates provided in the resource section.
                                </p>

                                <div class="h-px bg-slate-50 mb-10"></div>

                                <!-- Evaluation Rubric -->
                                <div class="space-y-6 mb-12">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Expert Grading Rubric</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="rubric in rubrics" :key="rubric.label" class="p-6 bg-slate-50 rounded-3xl border border-slate-100 group hover:border-indigo-200 hover:bg-indigo-50/30 transition-all duration-300">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="w-5 h-5 bg-white border border-slate-200 rounded-md flex items-center justify-center text-slate-300 group-hover:text-indigo-600 group-hover:border-indigo-100 transition-colors">
                                                    <Check class="w-3 h-3" />
                                                </div>
                                                <span class="text-[11px] font-black text-slate-900">{{ rubric.label }}</span>
                                            </div>
                                            <p class="text-[10px] text-slate-400 font-bold leading-relaxed ml-8">{{ rubric.desc }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submission Area -->
                                <div class="space-y-6">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Your Submission</h4>
                                    <div class="border-4 border-dashed border-slate-100 rounded-[2.5rem] p-16 text-center group hover:border-indigo-200 hover:bg-slate-50/50 transition-all cursor-pointer relative overflow-hidden">
                                        <div class="relative z-10">
                                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 group-hover:bg-indigo-50 transition-all duration-500">
                                                <UploadCloud class="w-10 h-10 text-slate-300 group-hover:text-indigo-600 transition-colors" />
                                            </div>
                                            <p class="text-sm font-black text-slate-900 mb-2">Drag and drop your mission file here</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Accepted: PDF, DOCX, ZIP (Max 50MB)</p>
                                        </div>
                                        <div class="absolute inset-0 bg-indigo-50 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="p-8 bg-slate-50 border-t border-slate-100 flex justify-end">
                                <button class="bg-indigo-600 hover:bg-slate-900 text-white px-10 py-5 rounded-[2rem] font-black text-sm shadow-2xl shadow-indigo-200 hover:shadow-slate-200 transition-all active:scale-95 flex items-center gap-3 group">
                                    Submit Mission for Review
                                    <CheckCircle2 class="w-5 h-5 opacity-50 group-hover:opacity-100 transition-opacity" />
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer Support -->
                <div class="mt-20 text-center pb-20">
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">MicroEducate Focus Mode &bull; Design by Antigravity</p>
                </div>

            </div>
        </main>
    </div>
</template>

<style scoped>
.animate-in {
    animation-delay: 0.2s;
}

/* Custom scrollbar for sidebar */
nav::-webkit-scrollbar {
    width: 4px;
}
nav::-webkit-scrollbar-track {
    background: transparent;
}
nav::-webkit-scrollbar-thumb {
    background: #f1f5f9;
    border-radius: 10px;
}
nav::-webkit-scrollbar-thumb:hover {
    background: #e2e8f0;
}
</style>
