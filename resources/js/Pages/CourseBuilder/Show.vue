<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { PlayCircle, FileText, ClipboardCheck, Users, BookOpen, Clock, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    courseRecord: {
        type: Object,
        default: () => ({ name: '', description: '', sections: [] })
    }
});

const getLessonIcon = (type) => {
    switch(type) {
        case 'video': return PlayCircle;
        case 'text': return FileText;
        case 'assignment': return ClipboardCheck;
        case 'quiz': return ClipboardCheck;
        case 'exercise': return PlayCircle;
        default: return FileText;
    }
};
</script>

<template>
    <Head :title="`Archived: ${courseRecord?.name || courseRecord?.title || 'Program'}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="py-6">
                <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                    <span class="w-8 h-px bg-indigo-600"></span> Public Perspective
                </div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">
                    {{ courseRecord?.name || courseRecord?.title || 'Program Design' }}<span class="text-indigo-600">.</span>
                </h2>
                <p class="text-slate-400 font-bold text-sm mt-2">
                    Viewing structural blueprint for educational sequence.
                </p>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-10 animate-in fade-in slide-in-from-bottom-5 duration-700 pb-20">
            <!-- Main Class Card -->
            <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-2xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full blur-3xl -mr-32 -mt-32 opacity-50"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                            <BookOpen class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ courseRecord?.name || courseRecord?.title }}</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-0.5">Program Overview</p>
                        </div>
                    </div>
                    
                    <p class="text-slate-500 font-medium leading-relaxed max-w-2xl text-lg">{{ courseRecord?.description }}</p>

                    <div v-if="courseRecord?.instructors?.length" class="mt-10 pt-10 border-t border-slate-50">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                            <Users class="w-4 h-4 text-indigo-400" />
                            Lead Architects
                        </h4>
                        <div class="flex flex-wrap gap-3">
                            <div v-for="instructor in courseRecord.instructors" :key="instructor.id" 
                                 class="flex items-center gap-3 bg-slate-50 border border-slate-100 px-5 py-2.5 rounded-2xl">
                                <div class="w-6 h-6 bg-indigo-100 rounded-lg flex items-center justify-center text-[10px] font-bold text-indigo-700">
                                    {{ instructor.name?.charAt(0) }}
                                </div>
                                <span class="text-sm font-black text-slate-700 tracking-tight">{{ instructor.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sections and Lessons -->
            <div class="space-y-8">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] px-4">Modul Sequence</h4>
                
                <div v-if="!courseRecord?.sections?.length" class="bg-white p-16 rounded-[3rem] border-2 border-dashed border-slate-50 text-center">
                    <p class="text-slate-300 font-bold uppercase tracking-widest text-sm">Sequence nodes not initialized.</p>
                </div>

                <div v-for="(section, sIdx) in courseRecord?.sections" :key="section.id" 
                     class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm overflow-hidden group hover:border-indigo-100 transition-all">
                    <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="text-xs font-black text-indigo-600 bg-white w-8 h-8 rounded-lg flex items-center justify-center shadow-sm">
                                {{ (sIdx + 1).toString().padStart(2, '0') }}
                            </div>
                            <h4 class="text-lg font-black text-slate-900 tracking-tight uppercase">
                                {{ section.title }}
                            </h4>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ section.lessons?.length || 0 }} Units</span>
                    </div>
                    
                    <ul v-if="section.lessons?.length" class="p-4 space-y-2">
                        <li v-for="lesson in section.lessons" :key="lesson.id" 
                            class="flex items-center gap-4 p-4 rounded-2xl hover:bg-slate-50 transition-colors group/lesson">
                            <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 group-hover/lesson:bg-white group-hover/lesson:text-indigo-600 transition-all border border-transparent group-hover/lesson:border-slate-100 group-hover/lesson:shadow-sm">
                                <component :is="getLessonIcon(lesson?.type)" class="w-5 h-5 flex-shrink-0" />
                            </div>
                            <div class="flex-1">
                                <span class="text-slate-700 font-bold block">{{ lesson?.title || 'Untitled Unit' }}</span>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5 block">{{ lesson?.type || 'material' }}</span>
                            </div>
                            <ChevronRight class="w-4 h-4 text-slate-200 group-hover/lesson:translate-x-1 transition-all" />
                        </li>
                    </ul>
                    <div v-else class="p-8 text-center bg-slate-50/30">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">No interactive units in this modul.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
