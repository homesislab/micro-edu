<script setup>
import { Link } from '@inertiajs/vue3';
import { BookOpen, ShieldCheck, Zap, Award } from 'lucide-vue-next';
</script>

<template>
    <div class="min-h-screen flex">
        <!-- Left: Brand Panel -->
        <div class="hidden lg:flex w-[45%] bg-slate-950 flex-col justify-between p-16 relative overflow-hidden">
            <!-- Background decoration -->
            <div class="absolute inset-0">
                <div class="absolute top-[-20%] left-[-20%] w-[80%] h-[80%] bg-indigo-600/10 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-[-20%] right-[-20%] w-[60%] h-[60%] bg-indigo-500/5 rounded-full blur-[100px]"></div>
                <!-- Grid pattern -->
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 40px 40px;"></div>
            </div>

            <!-- Top logo -->
            <div class="relative z-10">
                <Link href="/" class="flex items-center gap-3 group w-fit">
                    <div class="bg-indigo-600 w-11 h-11 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                        <BookOpen class="text-white w-6 h-6" />
                    </div>
                    <span class="font-black text-2xl tracking-tight text-white">MicroEducate<span class="text-indigo-400">.</span></span>
                </Link>
            </div>

            <!-- Center content -->
            <div class="relative z-10 space-y-10">
                <div>
                    <div class="inline-flex items-center gap-2 bg-indigo-500/10 text-indigo-400 px-4 py-2 rounded-full border border-indigo-500/20 text-[10px] font-black uppercase tracking-[0.3em] mb-6">
                        <Zap class="w-3.5 h-3.5" />
                        Kirkpatrick Evaluation Engine
                    </div>
                    <h1 class="text-5xl font-black text-white leading-tight tracking-tighter">
                        Measure Real<br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">Behavior Growth.</span>
                    </h1>
                    <p class="text-slate-400 font-medium text-lg mt-6 leading-relaxed max-w-sm">
                        The only LMS that quantifies learning impact from reaction all the way to behavior implementation.
                    </p>
                </div>

                <div class="space-y-5">
                    <div v-for="item in [
                        { icon: ShieldCheck, title: 'L1–L3 Evaluation', desc: 'Full Kirkpatrick cycle built-in' },
                        { icon: Zap, title: 'Growth Delta Tracking', desc: 'Pre/post score delta visualization' },
                        { icon: Award, title: 'Verifiable Certificates', desc: 'Expert-reviewed behavior proof' },
                    ]" :key="item.title" class="flex items-center gap-5">
                        <div class="w-11 h-11 bg-white/5 rounded-2xl flex items-center justify-center text-indigo-400 border border-white/5 flex-shrink-0">
                            <component :is="item.icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="font-black text-sm text-slate-200">{{ item.title }}</p>
                            <p class="text-xs text-slate-500 font-medium">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom testimonial -->
            <div class="relative z-10 bg-white/5 border border-white/10 rounded-3xl p-6">
                <p class="text-slate-300 text-sm font-medium leading-relaxed italic">
                    "MicroEducate transformed how we measure training ROI — we finally have proof that learning changes behavior."
                </p>
                <div class="flex items-center gap-3 mt-4">
                    <div class="w-8 h-8 bg-indigo-500/30 rounded-lg flex items-center justify-center text-xs font-black text-indigo-300">SA</div>
                    <div>
                        <p class="text-xs font-black text-slate-300">Sarah A.</p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest">L&D Director</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Form Panel -->
        <div class="flex-1 flex flex-col min-h-screen bg-[#f8fafc]">
            <!-- Mobile logo -->
            <div class="lg:hidden p-6">
                <Link href="/" class="flex items-center gap-3 w-fit">
                    <div class="bg-indigo-600 w-9 h-9 rounded-xl flex items-center justify-center">
                        <BookOpen class="text-white w-5 h-5" />
                    </div>
                    <span class="font-black text-xl tracking-tight text-slate-900">MicroEducate<span class="text-indigo-600">.</span></span>
                </Link>
            </div>

            <!-- Centered form slot -->
            <div class="flex-1 flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-md">
                    <slot />
                </div>
            </div>

            <p class="text-center text-xs text-slate-400 font-medium pb-8">© 2026 MicroEducate. Kirkpatrick Certified Platform.</p>
        </div>
    </div>
</template>
