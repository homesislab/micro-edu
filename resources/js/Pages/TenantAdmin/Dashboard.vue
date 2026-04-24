<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Users, 
    BookOpen, 
    Award, 
    TrendingUp, 
    UserPlus, 
    ArrowUpRight,
    Search,
    Calendar,
    Clock,
    Zap
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    recent_activity: Array
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Academy Admin Dashboard" />

        <template #header>
            <div class="flex items-center justify-between py-6">
                <div>
                    <div class="flex items-center gap-2 text-indigo-500 font-black text-[10px] uppercase tracking-[0.3em] mb-2">
                        <span class="w-8 h-px bg-indigo-500"></span> Enterprise Academy Command
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">
                        Academy Overview<span class="text-indigo-500">.</span>
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('tenant.users.index')" 
                          class="bg-slate-900 text-white px-8 py-4 rounded-[2rem] font-black text-sm flex items-center gap-3 shadow-2xl hover:bg-indigo-600 transition-all hover:-translate-y-1 active:scale-95">
                        <UserPlus class="w-5 h-5 text-indigo-400" /> Manage Personnel
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-10">
            <!-- Strategic Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-[3rem] p-8 border border-slate-100 shadow-xl group hover:border-indigo-200 transition-all">
                    <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                        <Users class="w-7 h-7" />
                    </div>
                    <p class="text-slate-400 font-black text-[10px] uppercase tracking-widest mb-1">Total Workforce</p>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tighter">{{ stats.total_employees }}</h3>
                    <div class="mt-4 flex items-center gap-2 text-emerald-600 text-xs font-bold">
                        <TrendingUp class="w-3 h-3" /> +12% this month
                    </div>
                </div>

                <div class="bg-white rounded-[3rem] p-8 border border-slate-100 shadow-xl group hover:border-indigo-200 transition-all">
                    <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-6 group-hover:scale-110 transition-transform">
                        <BookOpen class="w-7 h-7" />
                    </div>
                    <p class="text-slate-400 font-black text-[10px] uppercase tracking-widest mb-1">Active Learning Nodes</p>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tighter">{{ stats.active_enrollments }}</h3>
                    <p class="mt-4 text-slate-400 text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                        <Clock class="w-3 h-3" /> Concurrent Sessions
                    </p>
                </div>

                <div class="bg-white rounded-[3rem] p-8 border border-slate-100 shadow-xl group hover:border-indigo-200 transition-all">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:scale-110 transition-transform">
                        <Award class="w-7 h-7" />
                    </div>
                    <p class="text-slate-400 font-black text-[10px] uppercase tracking-widest mb-1">Mastery Success Rate</p>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tighter">{{ stats.completion_rate }}%</h3>
                    <div class="mt-4 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" :style="`width: ${stats.completion_rate}%`"></div>
                    </div>
                </div>

                <div class="bg-indigo-600 rounded-[3rem] p-8 text-white relative overflow-hidden group shadow-2xl">
                    <Zap class="absolute -right-8 -bottom-8 w-40 h-40 text-white/10 rotate-12 group-hover:rotate-0 transition-transform duration-700" />
                    <div class="relative z-10">
                        <p class="text-indigo-100 font-black text-[10px] uppercase tracking-widest mb-1">Internal Mentors</p>
                        <h3 class="text-4xl font-black tracking-tighter">{{ stats.expert_count }}</h3>
                        <Link :href="route('expert.dashboard')" class="mt-4 text-xs font-black uppercase tracking-widest bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-white/30 transition-colors w-fit">
                            Launch Expert Panel <ArrowUpRight class="w-3 h-3" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Operational Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div class="lg:col-span-2 bg-white rounded-[4rem] border border-slate-100 shadow-2xl overflow-hidden">
                    <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-1">Academy Feed</h3>
                            <p class="text-indigo-300 font-bold text-[10px] uppercase tracking-widest">Real-time enrollment telemetry</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-for="activity in recent_activity" :key="activity.id" 
                             class="flex items-center justify-between p-6 rounded-3xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 group">
                            <div class="flex items-center gap-6">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center font-black text-indigo-600 text-lg group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    {{ activity.user?.name?.charAt(0) || 'U' }}
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-900 group-hover:text-indigo-600 transition-colors">{{ activity.user?.name }}</h4>
                                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-1">
                                        Enrolled in: <span class="text-slate-600">{{ activity.course?.title }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="bg-slate-100 text-slate-400 font-black text-[10px] uppercase tracking-widest px-4 py-1.5 rounded-lg group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-all">
                                    {{ formatDate(activity.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-8">
                    <div class="bg-slate-900 rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-slate-800 to-indigo-900 opacity-50"></div>
                        <div class="relative z-10">
                            <h3 class="text-2xl font-black tracking-tight mb-6 leading-tight">Optimize Your <br/>Learning Curve.</h3>
                            <p class="text-slate-400 text-xs font-bold leading-relaxed mb-8">Deploy batch enrollments or refresh your workforce competency metrics in one click.</p>
                            
                            <div class="space-y-4">
                                <Link :href="route('tenant.users.index')" class="flex items-center justify-between w-full bg-white/5 hover:bg-white/10 p-5 rounded-2xl transition-all border border-white/10 group/btn">
                                    <span class="font-black text-[10px] uppercase tracking-widest">Bulk Import Nodes</span>
                                    <ArrowUpRight class="w-5 h-5 text-indigo-400 group-hover/btn:translate-x-1 group-hover/btn:-translate-y-1 transition-transform" />
                                </Link>
                                <button class="flex items-center justify-between w-full bg-white/5 hover:bg-white/10 p-5 rounded-2xl transition-all border border-white/10 group/btn opacity-50 cursor-not-allowed">
                                    <span class="font-black text-[10px] uppercase tracking-widest">Generate HR Report</span>
                                    <Calendar class="w-5 h-5 text-slate-500" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
