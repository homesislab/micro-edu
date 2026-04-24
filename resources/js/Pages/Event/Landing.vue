<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Calendar, 
    Clock, 
    Video, 
    Award, 
    CheckCircle2, 
    Users, 
    ArrowRight,
    MapPin,
    ShieldCheck,
    MessageSquare
} from 'lucide-vue-next';

const props = defineProps({
    course: Object,
    isEnrolled: Boolean,
});

const form = useForm({});

const register = () => {
    form.post(route('events.register', props.course.id));
};

const formatDate = (dateString) => {
    if (!dateString) return 'TBA';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (dateString) => {
    if (!dateString) return 'TBA';
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    }) + ' WIB';
};
</script>

<template>
    <Head :title="course.title" />

    <div class="min-h-screen bg-slate-50 selection:bg-indigo-100">
        <!-- Premium Navigation -->
        <nav class="fixed top-0 w-full z-50 bg-white/70 backdrop-blur-xl border-b border-white/20">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
                        <Award class="text-white w-6 h-6" />
                    </div>
                    <span class="text-xl font-black text-slate-900 tracking-tighter">ClassHub<span class="text-indigo-600">.</span></span>
                </div>
                <div class="flex items-center gap-6">
                    <Link v-if="!$page.props.auth.user" :href="route('login')" class="text-sm font-black text-slate-600 hover:text-indigo-600 transition-colors">Login</Link>
                    <Link v-else :href="route('dashboard')" class="text-sm font-black text-indigo-600 px-6 py-2.5 bg-indigo-50 rounded-xl">My Dashboard</Link>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="pt-40 pb-20 px-6 relative overflow-hidden">
            <div class="absolute -top-40 -right-40 w-[600px] h-[600px] bg-indigo-400 rounded-full blur-[120px] opacity-10"></div>
            <div class="absolute top-20 -left-20 w-[400px] h-[400px] bg-emerald-400 rounded-full blur-[100px] opacity-10"></div>
            
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                <div class="space-y-10">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-2xl border border-indigo-100 animate-in fade-in slide-in-from-bottom-4 duration-700">
                        <span class="w-2 h-2 bg-indigo-600 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Upcoming Live Session</span>
                    </div>
                    
                    <h1 class="text-6xl md:text-7xl font-black text-slate-900 tracking-tight leading-[1.1] animate-in fade-in slide-in-from-bottom-8 duration-700">
                        {{ course.title }}<span class="text-indigo-600">.</span>
                    </h1>
                    
                    <p class="text-xl text-slate-500 font-medium leading-relaxed max-w-xl animate-in fade-in slide-in-from-bottom-10 duration-700">
                        {{ course.description }}
                    </p>

                    <div class="flex flex-wrap gap-6 items-center animate-in fade-in slide-in-from-bottom-12 duration-700">
                        <div v-if="!isEnrolled">
                            <button @click="register" class="bg-slate-900 text-white px-10 py-5 rounded-[2rem] font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all flex items-center gap-4 active:scale-95">
                                Register Now <ArrowRight class="w-6 h-6" />
                            </button>
                        </div>
                        <Link v-else :href="route('dashboard')" class="bg-emerald-500 text-white px-10 py-5 rounded-[2rem] font-black text-lg shadow-2xl hover:bg-emerald-600 transition-all flex items-center gap-4">
                            You are Enrolled <ShieldCheck class="w-6 h-6" />
                        </Link>
                        
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-3">
                                <div v-for="i in 3" :key="i" class="w-10 h-10 rounded-full border-2 border-white bg-slate-200"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-400">Join 240+ participants</span>
                        </div>
                    </div>
                </div>

                <!-- Featured Image / Flyer Preview -->
                <div class="relative animate-in zoom-in duration-1000">
                    <div class="aspect-[4/5] bg-slate-900 rounded-[3rem] shadow-2xl overflow-hidden relative group">
                        <img v-if="course.thumbnail_path" :src="'/storage/' + course.thumbnail_path" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-700" />
                        <div v-else class="w-full h-full bg-gradient-to-br from-indigo-600 to-indigo-900 flex items-center justify-center p-12">
                             <Award class="text-white/20 w-40 h-40" />
                        </div>
                        
                        <!-- Event Info Overlay -->
                        <div class="absolute bottom-10 left-10 right-10 bg-white/10 backdrop-blur-2xl border border-white/20 p-8 rounded-[2rem]">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-white/60 uppercase tracking-widest">Date</p>
                                    <p class="text-white font-bold leading-tight">{{ formatDate(course.event_time) }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-white/60 uppercase tracking-widest">Time</p>
                                    <p class="text-white font-bold leading-tight">{{ formatTime(course.event_time) }}</p>
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <p class="text-[10px] font-black text-white/60 uppercase tracking-widest">Expert</p>
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 bg-white/20 rounded-full"></div>
                                        <p class="text-white font-bold">Iyas Swamede (Senior Expert)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Decorations -->
                    <div class="absolute -bottom-10 -right-10 bg-white p-6 rounded-3xl shadow-xl flex items-center gap-4 border border-slate-50">
                        <div class="bg-indigo-600 w-12 h-12 rounded-2xl flex items-center justify-center">
                            <Video class="text-white w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Live On</p>
                            <p class="text-lg font-black text-slate-900">Zoom Cloud</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Course Highlights -->
        <section class="py-32 px-6 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center space-y-4 mb-20">
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight">Apa yang akan Anda pelajari?</h2>
                    <p class="text-slate-500 font-medium max-w-2xl mx-auto text-lg">Program intensif ini dirancang untuk memberikan dampak terukur pada kompetensi Anda melalui metode Kirkpatrick yang terintegrasi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div v-for="(item, idx) in [
                        { t: 'Live Global Session', d: 'Interaksi langsung dengan expert melalui platform Zoom berkapasitas tinggi.', i: Video },
                        { t: 'Verified Certification', d: 'Dapatkan sertifikat digital otomatis setelah lolos pengujian post-test.', i: ShieldCheck },
                        { t: 'Community Network', d: 'Bergabung dengan ratusan partisipan aktif lainnya dari berbagai institusi.', i: Users }
                    ]" :key="idx" class="p-10 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:shadow-xl transition-all duration-500 group">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mb-8 shadow-sm group-hover:scale-110 transition-transform">
                            <component :is="item.i" class="text-indigo-600 w-8 h-8" />
                        </div>
                        <h4 class="text-2xl font-black text-slate-900 mb-4">{{ item.t }}</h4>
                        <p class="text-slate-500 font-medium leading-relaxed">{{ item.d }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-20 px-6 border-t border-slate-100 bg-white">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <Award class="text-white w-4 h-4" />
                    </div>
                    <span class="text-lg font-black text-slate-900 tracking-tighter">ClassHub</span>
                </div>
                <p class="text-slate-400 text-sm font-medium">© 2026 ClassHub Micro-Education Platform. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition-colors"><MessageSquare class="w-5 h-5" /></a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-in {
    animation: fadeIn 0.8s ease-out forwards;
}
</style>
