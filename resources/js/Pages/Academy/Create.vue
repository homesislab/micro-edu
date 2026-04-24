<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Rocket, ArrowLeft, Check, Sparkles, Globe, Shield } from 'lucide-vue-next';
import { computed } from 'vue';

const form = useForm({
    name: '',
    slug: '',
});

const submit = () => {
    form.post(route('academy.store'));
};

const slugPreview = computed(() => {
    return form.slug.toLowerCase().replace(/[^a-z0-9]/g, '-') || 'your-academy';
});
</script>

<template>
    <Head title="Launch Your Academy" />

    <div class="min-h-screen bg-slate-950 text-white flex flex-col md:flex-row overflow-hidden">
        <!-- Left Side: Inspiration -->
        <div class="hidden md:flex md:w-1/3 bg-indigo-600 p-12 flex-col justify-between relative overflow-hidden">
            <div class="z-10">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-xl rounded-2xl flex items-center justify-center mb-8">
                    <Rocket class="w-6 h-6 text-white" />
                </div>
                <h1 class="text-4xl font-black leading-tight mb-6">
                    Turn Your Knowledge Into a <span class="text-indigo-200">Living Community.</span>
                </h1>
                <p class="text-indigo-100 text-lg opacity-80 leading-relaxed">
                    MicroEducate gives you the tools to build, manage, and scale your own learning empire in minutes.
                </p>
            </div>

            <div class="space-y-6 z-10">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-white/10 rounded-lg"><Sparkles class="w-5 h-5" /></div>
                    <div>
                        <h4 class="font-bold">AI Powered Curriculum</h4>
                        <p class="text-sm text-indigo-200">Generate programs instantly with Gemini.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-white/10 rounded-lg"><Globe class="w-5 h-5" /></div>
                    <div>
                        <h4 class="font-bold">White Label Branding</h4>
                        <p class="text-sm text-indigo-200">Your logo, your colors, your rules.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-white/10 rounded-lg"><Shield class="w-5 h-5" /></div>
                    <div>
                        <h4 class="font-bold">Enterprise Security</h4>
                        <p class="text-sm text-indigo-200">Data isolation at the highest standard.</p>
                    </div>
                </div>
            </div>

            <!-- Abstract Background Shapes -->
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500 rounded-full blur-[100px] opacity-50"></div>
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-purple-500 rounded-full blur-[100px] opacity-30"></div>
        </div>

        <!-- Right Side: Form -->
        <div class="flex-1 flex flex-col justify-center px-6 md:px-24 py-12">
            <div class="max-w-xl w-full mx-auto">
                <Link :href="route('lobby')" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors mb-12 group">
                    <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                    Back to Lobby
                </Link>

                <div class="mb-10">
                    <h2 class="text-3xl font-black mb-2">Launch New Academy</h2>
                    <p class="text-slate-400">Let's set up the foundation of your new learning space.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div>
                        <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest mb-3">Academy Name</label>
                        <input 
                            v-model="form.name"
                            type="text" 
                            placeholder="e.g. Design Masterclass 2026"
                            class="w-full bg-slate-900 border-white/5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 rounded-2xl p-4 text-lg font-medium transition-all"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-400 uppercase tracking-widest mb-3">Academy URL Hub</label>
                        <div class="relative">
                            <input 
                                v-model="form.slug"
                                type="text" 
                                placeholder="my-awesome-academy"
                                class="w-full bg-slate-900 border-white/5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 rounded-2xl p-4 pl-4 text-lg font-medium transition-all"
                                required
                            />
                        </div>
                        <div class="mt-4 p-4 bg-indigo-500/5 border border-indigo-500/20 rounded-2xl">
                            <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Preview URL</p>
                            <p class="text-sm font-medium text-slate-300 truncate">
                                https://<span class="text-white font-bold">{{ slugPreview }}</span>.microeducate.com
                            </p>
                        </div>
                    </div>

                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-5 bg-white text-slate-950 font-black text-xl rounded-2xl hover:bg-indigo-50 transition-all active:scale-95 shadow-xl shadow-white/5 flex items-center justify-center gap-3"
                    >
                        <Rocket v-if="!form.processing" class="w-6 h-6" />
                        <span v-else class="animate-spin rounded-full h-6 w-6 border-4 border-slate-950 border-t-transparent"></span>
                        {{ form.processing ? 'Launching Space...' : 'Launch Academy' }}
                    </button>
                    
                    <p class="text-center text-xs text-slate-500 leading-relaxed px-8">
                        By launching an academy, you agree to MicroEducate's <span class="text-slate-400 underline cursor-pointer">Creator Terms</span> and authorize the creation of a dedicated workspace.
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>
