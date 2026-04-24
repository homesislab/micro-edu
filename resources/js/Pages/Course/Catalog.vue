<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    Search, 
    Filter, 
    BookOpen, 
    Star, 
    Users, 
    ArrowRight,
    Award
} from 'lucide-vue-next';

const props = defineProps({
    courses: Array,
});
</script>

<template>
    <Head title="Course Catalog | ClassHub" />

    <div class="min-h-screen bg-slate-50 selection:bg-indigo-100">
        <!-- Navigation -->
        <nav class="bg-white/60 backdrop-blur-2xl border-b border-white/20 sticky top-0 z-50 shadow-sm shadow-slate-200/20">
            <div class="max-w-7xl mx-auto px-6 h-24 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3 group transition-transform hover:scale-105">
                    <div class="w-12 h-12 bg-gradient-to-tr from-indigo-600 to-indigo-800 rounded-[1.25rem] flex items-center justify-center shadow-xl shadow-indigo-100 ring-4 ring-indigo-50">
                        <Award class="text-white w-7 h-7" />
                    </div>
                    <span class="text-2xl font-black text-slate-900 tracking-tighter">ClassHub<span class="text-indigo-600">.</span></span>
                </Link>
                <div class="flex items-center gap-10">
                    <Link :href="route('login')" class="text-sm font-black text-slate-500 hover:text-indigo-600 transition-colors tracking-wide">Enter Hub</Link>
                    <Link :href="route('register')" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl font-black text-sm shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:-translate-y-0.5 transition-all">Start Journey</Link>
                </div>
            </div>
        </nav>

        <!-- Hero Header -->
        <header class="py-32 px-6 bg-white relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-[600px] h-[600px] bg-indigo-400/10 rounded-full blur-[120px] opacity-60"></div>
            <div class="absolute -bottom-20 -left-20 w-[400px] h-[400px] bg-purple-400/10 rounded-full blur-[100px] opacity-40"></div>
            
            <div class="max-w-7xl mx-auto relative z-10 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-full mb-8 border border-indigo-100">
                    <Star class="w-4 h-4 text-indigo-600 fill-indigo-600" />
                    <span class="text-xs font-black text-indigo-700 uppercase tracking-[0.2em]">Verified High Performance</span>
                </div>
                <h1 class="text-6xl md:text-7xl font-black text-slate-900 mb-8 tracking-tight leading-[1.1]">
                    Master Skills that <br/> 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-indigo-900">Transform Behavior.</span>
                </h1>
                <p class="text-xl text-slate-500 font-medium max-w-2xl mx-auto leading-relaxed opacity-80">
                    Join thousands of professionals in our implementation-first LMS. Certified Kirkpatrick level growth, guaranteed.
                </p>
            </div>
        </header>

        <!-- Filter & Search Bar -->
        <section class="max-w-7xl mx-auto px-6 -mt-12 relative z-20">
            <div class="bg-white/60 backdrop-blur-3xl p-5 rounded-[3.5rem] shadow-[0_25px_50px_-12px_rgba(0,0,0,0.08)] border border-white flex flex-col lg:flex-row gap-5">
                <div class="flex-1 relative group">
                    <Search class="absolute left-8 top-1/2 -translate-y-1/2 text-slate-400 w-6 h-6 group-focus-within:text-indigo-600 transition-colors" />
                    <input type="text" placeholder="What role do you want to master today?" 
                           class="w-full pl-18 pr-8 py-5 rounded-[2.5rem] border-transparent bg-slate-50/50 focus:bg-white focus:ring-[12px] focus:ring-indigo-50 transition-all font-semibold outline-none text-lg" />
                </div>
                <div class="flex items-center gap-4">
                    <button class="px-10 h-18 bg-white rounded-[2.5rem] font-black text-slate-600 border border-slate-100 flex items-center gap-3 hover:bg-slate-50 transition-all text-sm shadow-sm">
                        <Filter class="w-4 h-4" /> All Categories
                    </button>
                    <button class="px-12 h-18 bg-gradient-to-r from-indigo-600 to-indigo-800 text-white rounded-[2.5rem] font-black shadow-2xl shadow-indigo-200 hover:scale-[1.02] active:scale-95 transition-all text-base tracking-wide flex items-center gap-3">
                        Discover <ArrowRight class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </section>

        <!-- Catalog Grid -->
        <main class="max-w-7xl mx-auto px-6 py-32">
            <div class="flex items-end justify-between mb-20">
                <div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">Curated Mastery</h2>
                    <p class="text-slate-500 font-bold mt-2 uppercase tracking-[0.2em] text-xs">The Best implementation paths</p>
                </div>
                <div class="bg-indigo-50 text-indigo-700 px-6 py-3 rounded-2xl text-sm font-black flex items-center gap-3 border border-indigo-100 shadow-sm">
                    <BookOpen class="w-4 h-4" />
                    {{ courses.length }} Pathfound
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                <div v-for="course in courses" :key="course.id" 
                     class="group bg-white rounded-[4rem] border border-white shadow-xl shadow-slate-200/40 hover:shadow-2xl hover:shadow-indigo-200/30 transition-all duration-700 overflow-hidden flex flex-col relative">
                    <div class="h-72 bg-slate-900 relative overflow-hidden">
                        <img v-if="course.thumbnail_path" :src="'/storage/' + course.thumbnail_path" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 blur-0 group-hover:blur-[2px]" />
                        <div v-else class="w-full h-full bg-gradient-to-br from-indigo-700 to-indigo-950 flex items-center justify-center p-12">
                            <BookOpen class="text-white/20 w-32 h-32 group-hover:scale-110 transition-transform duration-1000" />
                        </div>
                        
                        <!-- Premium Badge Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
                        <div class="absolute top-8 left-8">
                            <span class="px-5 py-2 bg-indigo-600/90 backdrop-blur-xl rounded-2xl text-[10px] font-black text-white uppercase tracking-[0.2em] border border-white/20 shadow-xl">
                                Live Session
                            </span>
                        </div>
                    </div>

                    <div class="p-10 lg:p-12 flex-1 flex flex-col">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex -space-x-3">
                                <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-4 border-white bg-slate-100 flex items-center justify-center overflow-hidden shadow-sm">
                                    <div class="w-full h-full bg-gradient-to-br from-slate-200 to-slate-300"></div>
                                </div>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">+{{ 150 + course.id * 10 }} Alumni</span>
                        </div>

                        <h3 class="text-3xl font-black text-slate-900 mb-6 group-hover:text-indigo-600 transition-colors leading-[1.2]">
                            {{ course.title }}
                        </h3>
                        
                        <p class="text-slate-500 font-medium line-clamp-2 mb-10 leading-relaxed opacity-80">
                            {{ course.description }}
                        </p>

                        <div class="mt-auto space-y-10">
                            <div class="flex items-center justify-between pt-8 border-t border-slate-50">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 bg-indigo-50 rounded-[1.25rem] flex items-center justify-center border border-indigo-100 shadow-inner">
                                        <Star class="text-indigo-600 w-6 h-6 fill-indigo-600/20" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1">
                                            <p class="text-2xl font-black text-slate-900">4.9</p>
                                            <Star class="text-amber-400 w-4 h-4 fill-amber-400" />
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Growth Index</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-black text-slate-900">${{ course.price }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Enrollment</p>
                                </div>
                            </div>

                            <Link :href="route('events.show', course.id)" 
                                  class="w-full bg-slate-900 text-white py-6 rounded-[2rem] font-black flex items-center justify-center gap-4 group-hover:bg-indigo-600 transition-all shadow-2xl shadow-slate-200 group-hover:shadow-indigo-200 group-hover:-translate-y-1">
                                Join Program <ArrowRight class="w-6 h-6" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-24 px-6 border-t border-slate-100 bg-white mt-auto">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <Award class="text-white w-4 h-4" />
                    </div>
                    <span class="text-lg font-black text-slate-900 tracking-tighter">ClassHub</span>
                </div>
                <p class="text-slate-400 text-sm font-medium">© 2026 ClassHub Micro-Education Platform. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition-colors">Documentation</a>
                </div>
            </div>
        </footer>
    </div>
</template>
