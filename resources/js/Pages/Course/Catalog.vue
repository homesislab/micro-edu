<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    Search, 
    Filter, 
    BookOpen, 
    Star, 
    Users, 
    ArrowRight,
    Award,
    Compass,
    TrendingUp,
    PlayCircle
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    courses: { type: Array, default: () => [] },
});

const activeCategory = ref('All');
const categories = ['All', 'Leadership', 'Technical', 'Productivity', 'Management'];

</script>

<template>
    <Head title="Course Catalog | MicroEducate" />

    <div class="min-h-screen bg-[#F8FAFC] selection:bg-indigo-500 selection:text-white font-sans overflow-x-hidden">
        
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-xl border-b border-indigo-50/50 fixed w-full top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                        <BookOpen class="text-white w-5 h-5" />
                    </div>
                    <span class="text-xl font-black text-slate-900 tracking-tighter">MicroEducate<span class="text-indigo-500">.</span></span>
                </Link>
                <div class="flex items-center gap-8">
                    <Link :href="route('login')" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Enter Hub</Link>
                    <Link :href="route('register')" class="bg-[#0B0F19] text-white px-6 py-2.5 rounded-full font-bold text-sm shadow-xl hover:shadow-indigo-500/20 hover:scale-105 transition-all">Sign Up</Link>
                </div>
            </div>
        </nav>

        <!-- Main Header & Search -->
        <header class="pt-40 pb-20 px-6 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/50 to-transparent -z-10"></div>
            
            <div class="max-w-5xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-100/50 rounded-full mb-6 border border-indigo-100">
                    <Compass class="w-4 h-4 text-indigo-600" />
                    <span class="text-[10px] font-black text-indigo-700 uppercase tracking-widest">Discovery Engine</span>
                </div>
                
                <h1 class="text-5xl md:text-7xl font-black text-[#0B0F19] tracking-tighter leading-[1.05] mb-6">
                    Find your next <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">behavioral breakthrough.</span>
                </h1>
                <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto mb-12">
                    Curated learning paths evaluated using the Kirkpatrick model. Not just courses, but guaranteed competence upgrades.
                </p>

                <!-- Oversized Search Bar -->
                <div class="max-w-3xl mx-auto relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-[2.5rem] blur-xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                    <div class="relative bg-white p-2 rounded-[2.5rem] shadow-2xl flex items-center border border-indigo-50/50">
                        <div class="pl-6 text-indigo-300 group-focus-within:text-indigo-500 transition-colors">
                            <Search class="w-6 h-6" />
                        </div>
                        <input type="text" placeholder="Search for paths, skills, or roles..." 
                               class="w-full bg-transparent border-none focus:ring-0 text-slate-800 font-bold text-lg px-4 py-4 placeholder-slate-300 outline-none" />
                        <button class="bg-[#0B0F19] text-white px-8 py-4 rounded-[2rem] font-bold shadow-lg hover:shadow-indigo-500/20 active:scale-95 transition-all flex items-center gap-2">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Category Pills -->
        <section class="max-w-7xl mx-auto px-6 mb-16 overflow-x-auto no-scrollbar pb-4">
            <div class="flex items-center justify-center gap-4 min-w-max">
                <button v-for="cat in categories" :key="cat"
                        @click="activeCategory = cat"
                        :class="[
                            'px-6 py-3 rounded-full font-bold text-sm transition-all border',
                            activeCategory === cat 
                                ? 'bg-indigo-50 border-indigo-200 text-indigo-700 shadow-sm' 
                                : 'bg-white border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600'
                        ]">
                    {{ cat }}
                </button>
                <div class="w-px h-6 bg-slate-200 mx-2"></div>
                <button class="flex items-center gap-2 px-6 py-3 rounded-full bg-white border border-slate-200 font-bold text-sm text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                    <Filter class="w-4 h-4" /> Filters
                </button>
            </div>
        </section>

        <!-- Catalog Grid -->
        <main class="max-w-7xl mx-auto px-6 pb-40">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Trending Programs</h2>
                <span class="text-sm font-bold text-slate-400">{{ courses?.length || 0 }} Results</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <div v-for="(course, index) in courses" :key="course.id" 
                     class="group bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 flex flex-col relative overflow-hidden">
                    
                    <!-- Thumbnail Area -->
                    <div class="h-64 relative overflow-hidden bg-slate-900 rounded-t-[2.5rem]">
                        <img v-if="course.thumbnail_path" :src="'/storage/' + course.thumbnail_path" 
                             class="w-full h-full object-cover group-hover:scale-110 group-hover:opacity-60 transition-all duration-700" />
                        <div v-else class="w-full h-full bg-gradient-to-br from-indigo-900 to-[#0B0F19] flex items-center justify-center">
                            <BookOpen class="text-white/10 w-24 h-24 group-hover:scale-110 transition-transform duration-700" />
                        </div>
                        
                        <!-- Badges -->
                        <div class="absolute top-6 left-6 right-6 flex justify-between items-start">
                            <span class="px-4 py-2 bg-white/10 backdrop-blur-md rounded-2xl text-[10px] font-black text-white uppercase tracking-widest border border-white/20">
                                Evaluated
                            </span>
                        </div>
                        
                        <!-- Floating Action Button -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
                            <button class="w-16 h-16 bg-white/20 backdrop-blur-xl border border-white/30 rounded-full flex items-center justify-center text-white scale-75 group-hover:scale-100 transition-all duration-300 hover:bg-white hover:text-indigo-600">
                                <PlayCircle class="w-8 h-8" />
                            </button>
                        </div>
                    </div>

                    <!-- Content Area -->
                    <div class="p-8 flex-1 flex flex-col relative bg-white">
                        <!-- Floating Users overlapping avatar -->
                        <div class="absolute -top-6 right-8 flex -space-x-3 p-1 bg-white rounded-full">
                            <div v-for="i in 3" :key="i" class="w-10 h-10 rounded-full border-2 border-white bg-indigo-100 flex items-center justify-center overflow-hidden shadow-sm">
                                <Users class="w-4 h-4 text-indigo-400" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mb-4 mt-2">
                            <Star class="w-4 h-4 text-amber-400 fill-amber-400" />
                            <span class="text-sm font-black text-slate-700">4.9 <span class="text-slate-400 font-medium">(1.2k)</span></span>
                        </div>

                        <h3 class="text-2xl font-black text-slate-900 mb-3 group-hover:text-indigo-600 transition-colors leading-tight">
                            {{ course.title }}
                        </h3>
                        
                        <p class="text-slate-500 font-medium line-clamp-2 mb-8 flex-1 leading-relaxed text-sm">
                            {{ course.description }}
                        </p>

                        <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tuition</p>
                                <p class="text-2xl font-black text-[#0B0F19]">${{ course.price || 0 }}</p>
                            </div>

                            <Link :href="route('events.show', course.id)" 
                                  class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white group-hover:-rotate-45 transition-all">
                                <ArrowRight class="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!courses || courses.length === 0" class="py-32 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm flex flex-col items-center justify-center space-y-6">
                <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-300">
                    <Search class="w-10 h-10" />
                </div>
                <h3 class="text-2xl font-black text-slate-900">No paths found</h3>
                <p class="text-slate-500">We couldn't find any learning paths matching your criteria.</p>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Hide scrollbar for category pills container */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
