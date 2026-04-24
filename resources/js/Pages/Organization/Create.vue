<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Rocket, ShieldCheck, Zap, Globe, Sparkles } from 'lucide-vue-next';

const form = useForm({
    name: '',
    slug: '',
});

const submit = () => {
    form.post(route('organization.store'));
};

const updateSlug = () => {
    form.slug = form.name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
};
</script>

<template>
    <Head title="Create Your Academy" />

    <div class="min-h-screen bg-[#f8fafc] flex flex-col items-center justify-center p-6 relative overflow-hidden">
        <!-- Decoration Background -->
        <div class="fixed inset-0 -z-10">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/10 rounded-full blur-[120px] opacity-60"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-500/10 rounded-full blur-[120px] opacity-60"></div>
        </div>

        <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-2 bg-white/40 backdrop-blur-3xl rounded-[3.5rem] border border-white shadow-2xl overflow-hidden relative">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500"></div>

            <!-- Left: Info -->
            <div class="p-12 bg-slate-900 text-white flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="bg-indigo-600 w-12 h-12 rounded-2xl flex items-center justify-center mb-10 shadow-lg shadow-indigo-500/50">
                        <Rocket class="w-7 h-7" />
                    </div>
                    <h1 class="text-4xl font-black leading-tight mb-6 mt-2">
                        Build Your <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">Knowledge Empire.</span>
                    </h1>
                    <p class="text-slate-400 font-medium text-lg leading-relaxed mb-12">
                        ClassHub SaaS transforms your expertise into a high-scale academy. Custom branding, multi-expert accounts, and behavior-growth analytics.
                    </p>

                    <div class="space-y-6">
                        <div v-for="item in [
                            { icon: ShieldCheck, title: 'Isolated Workspace', desc: 'Secure data for your institution' },
                            { icon: Zap, title: 'White-Labeling', desc: 'Your logo, colors, and domain' },
                            { icon: Globe, title: 'SaaS Ready', desc: 'Scale to thousands of participants' }
                        ]" :key="item.title" class="flex gap-4">
                            <div class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center text-indigo-400 flex-shrink-0">
                                <component :is="item.icon" class="w-5 h-5" />
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-200">{{ item.title }}</h4>
                                <p class="text-[11px] text-slate-500 leading-normal">{{ item.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-20 flex items-center gap-3 text-slate-500 text-xs font-bold uppercase tracking-widest">
                    <Sparkles class="w-4 h-4 text-indigo-500" />
                    Powered by Architect Engine
                </div>
            </div>

            <!-- Right: Form -->
            <div class="p-12 lg:p-20 flex flex-col justify-center">
                <div class="mb-12">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Setup Academy</h2>
                    <p class="text-slate-400 font-bold text-sm mt-2 uppercase tracking-widest">Workspace Configuration</p>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academy Name</label>
                        <input v-model="form.name" @input="updateSlug" type="text" placeholder="e.g. Acme Training Center"
                               class="w-full bg-slate-50 border-2 border-slate-100/50 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300" />
                        <p v-if="form.errors.name" class="text-red-500 text-xs font-bold ml-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Workspace Link (Slug)</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-6 text-slate-300 font-black text-sm">hub.com/</span>
                            <input v-model="form.slug" type="text" placeholder="acme-center"
                                   class="w-full bg-slate-50 border-2 border-slate-100/50 rounded-2xl pl-[4.5rem] pr-6 py-4 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300" />
                        </div>
                        <p class="text-[10px] text-slate-400 font-bold ml-1 italic">This will be your workspace unique identifier.</p>
                        <p v-if="form.errors.slug" class="text-red-500 text-xs font-bold ml-1">{{ form.errors.slug }}</p>
                    </div>

                    <button type="submit" :disabled="form.processing"
                            class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-lg shadow-2xl hover:bg-slate-800 transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-3">
                        Launch Academy <Rocket class="w-5 h-5" />
                    </button>
                    
                    <p class="text-center text-xs text-slate-400 font-bold leading-relaxed px-4">
                        By launching, you become the **Academy Admin** with full control over experts and curricula.
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
input::placeholder {
    font-weight: 500;
}
</style>
