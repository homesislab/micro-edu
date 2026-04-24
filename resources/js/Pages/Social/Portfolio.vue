<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    Award, 
    CheckCircle, 
    ExternalLink, 
    MapPin, 
    Calendar, 
    ShieldCheck, 
    Briefcase,
    Globe,
    Zap,
    Image as ImageIcon,
    FileText
} from 'lucide-vue-next';

const props = defineProps({
    profile: Object,
    academies: Array,
    achievements: Array,
    proofs: Array,
});

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};
</script>

<template>
    <Head :title="`${profile.name} (@${profile.username}) | Portfolio`" />

    <div class="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-indigo-500/10">
        <!-- Profile Header -->
        <header class="bg-white border-b border-slate-200">
            <div class="max-w-6xl mx-auto px-6 py-12">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="relative">
                        <div v-if="profile.avatar_url" class="w-32 h-32 rounded-[48px] overflow-hidden border-4 border-white shadow-2xl shadow-indigo-100">
                            <img :src="profile.avatar_url" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="w-32 h-32 rounded-[48px] bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-4xl font-black shadow-2xl shadow-indigo-200">
                            {{ getInitials(profile.name) }}
                        </div>
                        <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-white p-2 rounded-2xl shadow-lg border-4 border-white">
                            <ShieldCheck class="w-5 h-5" />
                        </div>
                    </div>

                    <div class="text-center md:text-left flex-1">
                        <div class="flex flex-col md:flex-row md:items-center gap-3 mb-3">
                            <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ profile.name }}</h1>
                            <span class="text-indigo-600 font-bold px-3 py-1 bg-indigo-50 rounded-full text-sm self-center md:self-auto">@{{ profile.username }}</span>
                        </div>
                        <p class="text-lg text-slate-500 font-medium max-w-2xl mb-6 leading-relaxed">
                            {{ profile.bio || 'Continuous learner and professional contributor on the MicroEducate platform.' }}
                        </p>
                        <div class="flex flex-wrap justify-center md:justify-start gap-4 text-sm font-bold text-slate-400">
                            <div class="flex items-center gap-1.5"><Calendar class="w-4 h-4" /> Joined {{ profile.joined_at }}</div>
                            <div class="flex items-center gap-1.5 text-emerald-600"><CheckCircle class="w-4 h-4" /> Identity Verified</div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button class="px-8 py-3 bg-slate-900 text-white font-bold rounded-2xl hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 active:scale-95">
                            Connect Profile
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Sidebar: Stats and Academies -->
                <div class="space-y-12">
                    <!-- Stats Card -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Performance Metrics</h3>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 rounded-xl text-indigo-600"><Zap class="w-5 h-5" /></div>
                                    <span class="font-bold text-slate-600">Certificates</span>
                                </div>
                                <span class="text-2xl font-black text-slate-900">{{ achievements.length }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-purple-50 rounded-xl text-purple-600"><Award class="w-5 h-5" /></div>
                                    <span class="font-bold text-slate-600">Communities</span>
                                </div>
                                <span class="text-2xl font-black text-slate-900">{{ academies.length }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Academies -->
                    <div>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6 px-1">Academy Memberships</h3>
                        <div class="space-y-4">
                            <div v-for="academy in academies" :key="academy.id" class="flex items-center gap-4 p-4 bg-white rounded-2xl border border-slate-100 group hover:border-indigo-200 transition-all">
                                <div class="w-12 h-12 bg-slate-50 flex items-center justify-center text-slate-400 font-bold rounded-xl overflow-hidden group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <img v-if="academy.logo" :src="academy.logo" class="w-full h-full object-cover" />
                                    <span v-else>{{ academy.name.charAt(0) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ academy.name }}</p>
                                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mt-0.5">Verified Member</p>
                                </div>
                                <Globe class="w-4 h-4 text-slate-200" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Feed: Proof of Work & Achievements -->
                <div class="lg:col-span-2 space-y-12">
                    <!-- Proof of Work (L3 Assignments) -->
                    <section>
                        <div class="flex items-center justify-between mb-8 px-1">
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Proof of Work</h2>
                            <span class="text-xs font-bold text-slate-400">Kirkpatrick Level 3 Evidence</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="proof in proofs" :key="proof.id" class="bg-white rounded-[32px] overflow-hidden border border-slate-100 shadow-sm group hover:shadow-xl hover:shadow-indigo-100 transition-all">
                                <div class="aspect-video bg-slate-100 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm z-10">
                                        <button class="bg-white text-slate-900 px-6 py-2 rounded-xl font-bold flex items-center gap-2">
                                            <ExternalLink class="w-4 h-4" />
                                            View Evidence
                                        </button>
                                    </div>
                                    <!-- Fallback for empty image -->
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <component :is="proof.l3Assignment.submission_type === 'image' ? ImageIcon : FileText" class="w-12 h-12" />
                                    </div>
                                    <div class="absolute top-4 left-4 z-20">
                                        <div class="bg-emerald-500 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                                            <CheckCircle class="w-3.5 h-3.5" />
                                            Verified by Expert
                                        </div>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <p class="text-xs font-black text-indigo-600 mb-2 uppercase tracking-widest">{{ proof.course.name }}</p>
                                    <h4 class="text-lg font-bold text-slate-900 mb-2 leading-tight">{{ proof.l3Assignment.description || 'Behavior Implementation Evidence' }}</h4>
                                    <div class="flex items-center gap-4 mt-8 pt-6 border-t border-slate-50">
                                        <div class="flex -space-x-3">
                                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-200"></div>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400">Reviewed by 5 Experts</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="proofs.length === 0" class="text-center py-24 bg-white rounded-[40px] border border-slate-100">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <ShieldCheck class="w-8 h-8 text-slate-200" />
                            </div>
                            <p class="text-slate-400 font-medium">No verified evidence uploaded yet.</p>
                        </div>
                    </section>

                    <!-- Achievements -->
                    <section>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-8 px-1">Curriculum Mastery</h2>
                        <div class="space-y-4">
                            <div v-for="ach in achievements" :key="ach.id" class="flex items-center justify-between p-6 bg-white rounded-3xl border border-slate-100 hover:bg-slate-50 transition-all group">
                                <div class="flex items-center gap-6">
                                    <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 rounded-2xl group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        <Zap class="w-7 h-7" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900">{{ ach.course.name }}</h4>
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest flex items-center gap-1.5 mt-1">
                                            Cert #{{ ach.id + 1000 }} <span class="mx-1">•</span> {{ ach.updated_at.split('T')[0] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="px-4 py-1.5 bg-emerald-50 border border-emerald-100 text-emerald-600 text-xs font-black rounded-lg uppercase">
                                        {{ ach.points || 150 }} XP
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>

        <!-- Global Footer -->
        <footer class="border-t border-slate-200 py-20 bg-white">
            <div class="max-w-6xl mx-auto px-6 text-center">
                <div class="bg-indigo-600 inline-flex p-3 rounded-2xl mb-8">
                    <Globe class="w-6 h-6 text-white" />
                </div>
                <h4 class="text-xl font-black text-slate-900 mb-2">MicroEducate Identity</h4>
                <p class="text-slate-400 text-sm font-medium mb-12">Verified decentralized reputation layer for professional development.</p>
                <div class="flex justify-center gap-8 text-xs font-black text-slate-400 uppercase tracking-widest">
                    <a href="#" class="hover:text-indigo-600 transition-colors">Privacy Engine</a>
                    <a href="#" class="hover:text-indigo-600 transition-colors">Verification Protocol</a>
                    <a href="#" class="hover:text-indigo-600 transition-colors">Platform Terms</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
