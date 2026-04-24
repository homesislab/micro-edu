<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, ShieldCheck, Zap, ArrowRight, Award, Globe, Users, TrendingUp, Check } from 'lucide-vue-next';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome to MicroEducate" />

    <div class="min-h-screen bg-[#f8fafc] text-slate-900 selection:bg-indigo-600 selection:text-white font-sans overflow-hidden">
        
        <!-- Decoration Background -->
        <div class="fixed inset-0 -z-10">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-100 rounded-full blur-[120px] opacity-60"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-50 rounded-full blur-[120px] opacity-60"></div>
        </div>

        <!-- Navbar -->
        <nav class="relative z-20 px-6 lg:px-12 py-8 flex items-center justify-between max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 w-10 h-10 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-100">
                    <BookOpen class="text-white w-6 h-6" />
                </div>
                <span class="font-black text-2xl tracking-tight">MicroEducate<span class="text-indigo-600">.</span></span>
            </div>

            <div v-if="canLogin" class="flex items-center gap-6">
                <Link v-if="$page.props.auth.user" href="/dashboard" 
                      class="text-slate-600 font-bold hover:text-indigo-600 transition-colors">Dashboard</Link>
                
                <template v-else>
                    <Link :href="route('login')" class="text-slate-600 font-bold hover:text-indigo-600 transition-colors">Sign In</Link>
                    <Link v-if="canRegister" :href="route('register')" 
                          class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                        Get Started
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative z-10 max-w-7xl mx-auto px-6 lg:px-12 pt-20 pb-40">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                <div class="space-y-10 animate-in fade-in slide-in-from-left-10 duration-1000">
                    <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 px-4 py-2 rounded-full border border-indigo-100 shadow-sm">
                        <Zap class="w-4 h-4" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Next-Gen LMS Platform</span>
                    </div>

                    <h1 class="text-6xl lg:text-8xl font-black text-slate-900 leading-[1.1] tracking-tighter">
                        Evaluate <br/>
                        <span class="bg-gradient-to-r from-indigo-600 to-emerald-500 bg-clip-text text-transparent">Behavior Growth.</span>
                    </h1>

                    <p class="text-xl text-slate-500 font-medium max-w-lg leading-relaxed">
                        A boutique LMS engine specialized in the **Kirkpatrick Evaluation Model**. 
                        Transform simple learning into measurable behavior changes with integrated L1-L3 assessments.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <Link v-if="!$page.props.auth.user" :href="route('register')" 
                              class="w-full sm:w-auto bg-slate-900 text-white px-10 py-5 rounded-2xl font-black text-lg shadow-2xl hover:bg-slate-800 transition-all flex items-center justify-center gap-3">
                            Start for Free <ArrowRight class="w-5 h-5" />
                        </Link>
                        <Link v-else href="/dashboard" 
                               class="w-full sm:w-auto bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black text-lg shadow-2xl hover:bg-indigo-700 transition-all">
                            Go to Hub
                        </Link>
                        <button class="w-full sm:w-auto group px-10 py-5 rounded-2xl font-bold text-slate-600 hover:bg-slate-100 transition-all flex items-center justify-center gap-2 underline underline-offset-4">
                            See how it works
                        </button>
                    </div>

                    <div class="flex items-center gap-10 pt-10 border-t border-slate-200">
                        <div class="flex flex-col">
                            <span class="text-2xl font-black text-slate-900">Level 3</span>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Assessment Capable</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black text-slate-900">100%</span>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Expert Verified</span>
                        </div>
                    </div>
                </div>

                <div class="relative animate-in fade-in zoom-in duration-1000 delay-300 hidden lg:block">
                    <!-- Dashboard Mockup -->
                    <div class="glass-card rounded-[3rem] p-4 shadow-2xl shadow-indigo-100/50 rotate-3 translate-x-10 scale-105 border-white/50 border-4">
                        <div class="bg-slate-50 rounded-[2.5rem] p-10 space-y-8">
                            <div class="flex items-center justify-between">
                                <div class="w-32 h-4 bg-slate-200 rounded-full"></div>
                                <div class="w-10 h-10 bg-indigo-100 rounded-xl"></div>
                            </div>
                            <div class="space-y-4">
                                <div class="w-full h-8 bg-slate-200 rounded-xl"></div>
                                <div class="w-2/3 h-8 bg-slate-200 rounded-xl"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="h-32 bg-indigo-50 rounded-3xl border-2 border-indigo-100 flex items-center justify-center">
                                    <TrendingUp class="w-10 h-10 text-indigo-400 opacity-20" />
                                </div>
                                <div class="h-32 bg-emerald-50 rounded-3xl border-2 border-emerald-100 flex items-center justify-center">
                                    <ShieldCheck class="w-10 h-10 text-emerald-400 opacity-20" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Elements -->
                    <div class="absolute -left-10 bottom-20 bg-white p-6 rounded-3xl shadow-xl flex items-center gap-4 animate-bounce duration-[3000ms]">
                        <div class="bg-emerald-500 w-10 h-10 rounded-xl flex items-center justify-center">
                            <Check class="text-white w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase">Growth Delta</p>
                            <p class="text-lg font-black text-slate-900">+42% Points</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Metrics/Features Section -->
        <section class="bg-white py-40 border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-20 space-y-4">
                    <h2 class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.3em]">Science Based Learning</h2>
                    <h3 class="text-4xl lg:text-5xl font-black text-slate-900 leading-tight">Beyond just simple quizzes.</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div v-for="feature in [
                        { title: 'Level 1: Reaction', desc: 'Mandatory feedback flow ensuring every material gets quality review.', icon: Users },
                        { title: 'Level 2: Learning', desc: 'Pre and post test engine with automatic score delta visualization.', icon: Zap },
                        { title: 'Level 3: Behavior', desc: 'Expert-reviewed assignments focusing on real workplace implementation.', icon: Award }
                    ]" :key="feature.title" class="space-y-6">
                        <div class="bg-slate-50 w-16 h-16 rounded-2xl flex items-center justify-center text-indigo-600">
                            <component :is="feature.icon" class="w-8 h-8" />
                        </div>
                        <h4 class="text-2xl font-black text-slate-900">{{ feature.title }}</h4>
                        <p class="text-slate-500 font-medium leading-relaxed">{{ feature.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-950 text-white py-20">
            <div class="max-w-7xl mx-auto px-6 lg:px-12 flex flex-col md:flex-row justify-between items-center gap-10">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 w-10 h-10 rounded-xl flex items-center justify-center">
                        <BookOpen class="text-white w-6 h-6" />
                    </div>
                    <span class="font-black text-2xl tracking-tight">MicroEducate.</span>
                </div>
                <p class="text-slate-500 text-sm font-bold">&copy; 2026 Crafted for Professional Behavior Evaluation.</p>
                <div class="flex gap-8 text-sm font-bold text-slate-400">
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">Support</a>
                </div>
            </div>
        </footer>

    </div>
</template>

<style scoped>
.glass-card {
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-in {
    animation: fadeIn 1s ease-out forwards;
}
</style>
