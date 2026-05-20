<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isScrolled = ref(false);

onMounted(() => {
    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 20;
    });
});

const programs = [
    {
        title: 'Kelas Intensif Tahsin',
        level: 'Semua Level',
        duration: '3 Bulan · 24 Pertemuan',
        price: 'Rp 350.000',
        icon: '📖',
        color: 'from-rs-teal/20 to-rs-teal-dark/10',
        border: 'hover:border-rs-teal/40',
        badge: 'Terpopuler',
    },
    {
        title: 'Parenting Qurani',
        level: 'Orang Tua',
        duration: '1 Hari · 4 Sesi Webinar',
        price: 'Rp 99.000',
        icon: '🤲',
        color: 'from-rs-gold/20 to-rs-gold/5',
        border: 'hover:border-rs-gold/40',
        badge: null,
    },
    {
        title: 'Pesantren Online Anak',
        level: 'Anak 6–12 Tahun',
        duration: '1 Minggu · 5 Pertemuan',
        price: 'Rp 9.900',
        icon: '🌙',
        color: 'from-rs-teal-dim/20 to-transparent',
        border: 'hover:border-rs-teal-dim/40',
        badge: 'Spesial',
    },
];

const features = [
    {
        icon: '🌳',
        title: 'Sanad Chain',
        desc: 'Setiap guru memiliki rantai keilmuan (sanad) yang terverifikasi hingga Rasulullah ﷺ. Belajar dengan keyakinan.',
        color: 'bg-rs-teal/10 border-rs-teal/20 group-hover:bg-rs-teal/20',
    },
    {
        icon: '🎙️',
        title: 'Setoran Bacaan',
        desc: 'Upload rekaman bacaan langsung dari browser. Guru mereview dan memberikan feedback detail setiap ayat.',
        color: 'bg-rs-gold/10 border-rs-gold/20 group-hover:bg-rs-gold/20',
    },
    {
        icon: '📜',
        title: 'Ijazah Digital',
        desc: 'Sertifikat bergaya Syahadah Islam dengan QR verifikasi publik dan rantai sanad guru yang mengesahkan.',
        color: 'bg-rs-teal-dim/10 border-rs-teal-dim/20 group-hover:bg-rs-teal-dim/20',
    },
    {
        icon: '🏆',
        title: 'Halaqoh Leveling',
        desc: 'Kumpulkan Poin Tilawah dan naiki level dari Mubtadi hingga Huffadz. Belajar jadi lebih menyenangkan.',
        color: 'bg-rs-gold-light/10 border-rs-gold-light/20 group-hover:bg-rs-gold-light/20',
    },
    {
        icon: '🕌',
        title: 'Multi-Akademi',
        desc: 'Ustadz & Pondok Pesantren dari seluruh Indonesia bisa membuka akademi mereka di bawah platform Ruang Sanad.',
        color: 'bg-rs-teal/10 border-rs-teal/20 group-hover:bg-rs-teal/20',
    },
    {
        icon: '📊',
        title: 'Progress Kirkpatrick',
        desc: 'Ukur kemajuan belajar santri secara ilmiah: dari reaksi, pengetahuan, hingga perubahan perilaku nyata.',
        color: 'bg-rs-gold/10 border-rs-gold/20 group-hover:bg-rs-gold/20',
    },
];

const stats = [
    { value: '49.000+', label: 'Santri Aktif' },
    { value: '100+', label: 'Guru Bersanad' },
    { value: '3', label: 'Program Unggulan' },
    { value: '1', label: 'Sanad Tersambung' },
];
</script>

<template>
    <Head title="Ruang Sanad — Belajar Al-Quran Bersanad No. 1 Indonesia" />

    <div class="min-h-screen bg-rs-dark-base text-slate-200 font-sans overflow-x-hidden selection:bg-rs-teal selection:text-white">

        <!-- Google Fonts: Amiri (Arabic) + Playfair Display -->
        <component is="teleport" to="head">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Playfair+Display:wght@700;900&family=Figtree:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        </component>

        <!-- Animated Background -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute top-[5%] left-[15%] w-[600px] h-[600px] bg-rs-teal/10 rounded-full blur-[180px] animate-pulse duration-[6000ms]"></div>
            <div class="absolute bottom-[10%] right-[5%] w-[500px] h-[500px] bg-rs-gold/8 rounded-full blur-[160px] animate-pulse duration-[8000ms] delay-2000"></div>
            <div class="absolute top-[50%] left-[50%] -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] bg-rs-teal-dark/5 rounded-full blur-[200px]"></div>
            <!-- Subtle Islamic geometric grid -->
            <div class="absolute inset-0 opacity-[0.015]"
                 style="background-image: repeating-linear-gradient(0deg, #C9972A 0, #C9972A 1px, transparent 0, transparent 50%), repeating-linear-gradient(90deg, #C9972A 0, #C9972A 1px, transparent 0, transparent 50%); background-size: 60px 60px;">
            </div>
        </div>

        <!-- Navbar -->
        <nav :class="[
            'fixed top-0 left-0 w-full z-50 transition-all duration-500 border-b',
            isScrolled
                ? 'bg-rs-dark-base/90 backdrop-blur-xl border-rs-teal/10 py-3 shadow-lg shadow-rs-teal/5'
                : 'bg-transparent border-transparent py-6'
        ]">
            <div class="max-w-7xl mx-auto px-6 lg:px-12 flex items-center justify-between">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 bg-gradient-to-br from-rs-teal to-rs-teal-dark rounded-2xl flex items-center justify-center shadow-lg shadow-rs-teal/30 group-hover:scale-105 transition-transform">
                        <span class="font-arabic text-rs-gold text-lg font-bold leading-none">س</span>
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="font-black text-xl tracking-tight">
                            <span class="text-white">Ruang</span><span class="text-rs-gold">Sanad</span>
                        </span>
                        <span class="text-[10px] text-slate-400 tracking-widest uppercase font-semibold">Platform Al-Quran No. 1</span>
                    </div>
                </Link>

                <!-- Nav Links (Desktop) -->
                <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-400">
                    <a href="#program" class="hover:text-rs-gold transition-colors">Program</a>
                    <a href="#fitur" class="hover:text-rs-gold transition-colors">Fitur</a>
                    <a href="#akademi" class="hover:text-rs-gold transition-colors">Buka Akademi</a>
                </div>

                <!-- Auth Buttons -->
                <div v-if="canLogin" class="flex items-center gap-4">
                    <Link v-if="$page.props.auth.user" href="/dashboard"
                          class="bg-rs-teal/20 hover:bg-rs-teal/30 text-rs-teal-light px-5 py-2.5 rounded-full font-bold text-sm transition-all border border-rs-teal/30">
                        Buka Dashboard →
                    </Link>
                    <template v-else>
                        <Link :href="route('login')"
                              class="text-slate-300 font-bold text-sm hover:text-white transition-colors">
                            Masuk
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                              class="px-6 py-2.5 rounded-full font-bold text-sm text-white bg-gradient-to-r from-rs-teal to-rs-teal-dark shadow-lg shadow-rs-teal/20 hover:shadow-rs-teal/40 hover:scale-105 transition-all">
                            Daftar Gratis
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- ═══ HERO SECTION ═══ -->
        <main class="relative z-10 pt-44 pb-28">
            <div class="max-w-7xl mx-auto px-6 lg:px-12 text-center flex flex-col items-center">

                <!-- Arabic Badge -->
                <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full border border-rs-gold/20 bg-rs-gold/5 backdrop-blur-md mb-10">
                    <span class="font-arabic text-rs-gold text-lg">بِسْمِ اللّٰهِ</span>
                    <span class="w-px h-4 bg-rs-gold/30"></span>
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Platform Al-Quran Bersanad</span>
                </div>

                <!-- Headline -->
                <h1 class="text-5xl sm:text-7xl lg:text-[5.5rem] font-black text-white leading-[1.05] tracking-tighter mb-6 max-w-5xl">
                    Belajar Al-Quran
                    <span class="relative inline-block mx-2">
                        <span class="absolute -inset-2 bg-gradient-to-r from-rs-teal to-rs-gold blur-2xl opacity-20"></span>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-rs-teal-light to-rs-gold relative z-10">Bersanad</span>
                    </span>
                    <br class="hidden sm:block">bersama Guru Terpercaya.
                </h1>

                <!-- Arabic Subheading -->
                <p class="font-arabic text-2xl text-rs-gold/80 mb-4 tracking-wide">
                    خَيْرُكُمْ مَنْ تَعَلَّمَ الْقُرْآنَ وَعَلَّمَهُ
                </p>
                <p class="text-sm text-slate-500 italic mb-8">
                    "Sebaik-baik kalian adalah yang mempelajari Al-Quran dan mengajarkannya." (HR. Bukhari)
                </p>

                <p class="text-lg text-slate-400 font-medium max-w-2xl leading-relaxed mb-12">
                    Platform LMS Islam pertama dengan sistem <strong class="text-rs-gold">Sanad terverifikasi</strong>.
                    Setoran audio, Ijazah digital, dan komunitas halaqoh dari seluruh Indonesia.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                    <Link v-if="!$page.props.auth.user" :href="route('register')"
                          class="w-full sm:w-auto px-10 py-4 rounded-2xl font-black text-lg text-white bg-gradient-to-r from-rs-teal to-rs-teal-dark shadow-2xl shadow-rs-teal/30 hover:shadow-rs-teal/50 hover:scale-105 transition-all flex items-center justify-center gap-3">
                        🕌 Mulai Belajar Gratis
                    </Link>
                    <Link v-else href="/dashboard"
                          class="w-full sm:w-auto px-10 py-4 rounded-2xl font-black text-lg text-white bg-gradient-to-r from-rs-teal to-rs-teal-dark shadow-2xl shadow-rs-teal/30 hover:scale-105 transition-all">
                        Buka Dashboard
                    </Link>
                    <a href="#program"
                       class="w-full sm:w-auto px-10 py-4 rounded-2xl font-bold text-rs-gold border border-rs-gold/30 hover:bg-rs-gold/10 transition-all flex items-center justify-center gap-2">
                        📖 Lihat Program
                    </a>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 mt-24 w-full max-w-3xl">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <div class="text-3xl font-black text-white mb-1">{{ stat.value }}</div>
                        <div class="text-sm text-slate-500 font-medium">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- ═══ PROGRAM SECTION ═══ -->
        <section id="program" class="py-28 relative z-10 border-t border-white/5">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="text-center mb-16">
                    <p class="text-rs-gold font-black text-sm tracking-[0.3em] uppercase mb-3">Program Kami</p>
                    <h2 class="text-4xl md:text-5xl font-black text-white leading-tight">
                        Pilih perjalanan <span class="text-rs-teal-light">tilawah</span> Anda
                    </h2>
                    <p class="text-slate-400 mt-4 max-w-xl mx-auto">Setiap program diasuh oleh guru bersanad yang terverifikasi. Belajar dengan metode AQU yang terbukti efektif.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="prog in programs" :key="prog.title"
                         :class="['group relative p-8 rounded-3xl bg-gradient-to-b border border-white/5 transition-all duration-500 cursor-pointer', prog.color, prog.border]">
                        <!-- Badge -->
                        <div v-if="prog.badge"
                             class="absolute -top-3 right-6 bg-rs-gold text-white text-xs font-black px-4 py-1 rounded-full shadow-lg">
                            {{ prog.badge }}
                        </div>
                        <div class="text-4xl mb-5">{{ prog.icon }}</div>
                        <h3 class="text-xl font-black text-white mb-2">{{ prog.title }}</h3>
                        <p class="text-xs text-rs-teal-light font-bold uppercase tracking-widest mb-1">{{ prog.level }}</p>
                        <p class="text-sm text-slate-400 mb-6">{{ prog.duration }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black text-white">{{ prog.price }}</span>
                            <Link :href="route('register')"
                                  class="text-sm font-bold text-rs-gold hover:text-rs-gold-light transition-colors flex items-center gap-1">
                                Daftar →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ FEATURES SECTION ═══ -->
        <section id="fitur" class="py-28 relative z-10 border-t border-white/5 bg-rs-dark-card/40">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="text-center mb-16">
                    <p class="text-rs-teal-light font-black text-sm tracking-[0.3em] uppercase mb-3">Kenapa Ruang Sanad?</p>
                    <h2 class="text-4xl md:text-5xl font-black text-white leading-tight">
                        Lebih dari sekadar <span class="text-rs-gold">kursus online</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="f in features" :key="f.title"
                         :class="['group p-8 rounded-3xl border transition-all duration-500', f.color]">
                        <div class="text-4xl mb-5">{{ f.icon }}</div>
                        <h3 class="text-xl font-black text-white mb-3">{{ f.title }}</h3>
                        <p class="text-slate-400 leading-relaxed text-sm">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ OPEN ACADEMY CTA ═══ -->
        <section id="akademi" class="py-28 relative z-10 border-t border-white/5">
            <div class="max-w-5xl mx-auto px-6">
                <div class="relative rounded-[3rem] border border-rs-teal/20 bg-rs-dark-card overflow-hidden p-12 md:p-20 text-center">
                    <!-- Glow -->
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-rs-teal/15 rounded-full blur-[120px] pointer-events-none"></div>
                    <div class="absolute bottom-0 right-1/4 w-[300px] h-[200px] bg-rs-gold/10 rounded-full blur-[100px] pointer-events-none"></div>

                    <!-- Arabic top deco -->
                    <p class="font-arabic text-rs-gold/60 text-3xl mb-6 relative z-10">الله أكبر</p>

                    <h2 class="text-4xl md:text-5xl font-black text-white mb-6 relative z-10 tracking-tight">
                        Buka Akademi Islam<br>
                        <span class="text-rs-teal-light">di bawah naungan Ruang Sanad</span>
                    </h2>
                    <p class="text-lg text-slate-400 mb-10 max-w-2xl mx-auto relative z-10">
                        Ustadz, Ustadzah, atau Pondok Pesantren bisa meluncurkan akademi digital sendiri.
                        Infrastruktur LMS siap pakai, santri dari seluruh Indonesia.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10">
                        <Link :href="route('register')"
                              class="px-12 py-4 bg-gradient-to-r from-rs-teal to-rs-teal-dark text-white rounded-2xl font-black text-lg shadow-2xl shadow-rs-teal/30 hover:scale-105 transition-transform">
                            🕌 Buka Akademi Saya
                        </Link>
                        <Link :href="route('login')"
                              class="px-12 py-4 border border-rs-gold/30 text-rs-gold rounded-2xl font-bold text-lg hover:bg-rs-gold/10 transition-all">
                            Sudah punya akun? Masuk
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ FOOTER ═══ -->
        <footer class="py-12 border-t border-white/5 bg-rs-dark-base">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-rs-teal to-rs-teal-dark rounded-xl flex items-center justify-center">
                        <span class="font-arabic text-rs-gold text-base">س</span>
                    </div>
                    <span class="font-black text-xl tracking-tighter">
                        <span class="text-white">Ruang</span><span class="text-rs-gold">Sanad</span>
                    </span>
                </div>
                <p class="font-arabic text-rs-gold/50 text-lg">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
                <p class="text-slate-500 text-sm font-medium">© 2026 Ruang Sanad. Platform Al-Quran Bersanad.</p>
            </div>
        </footer>

    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Playfair+Display:wght@700;900&display=swap');
.font-arabic {
    font-family: 'Amiri', serif;
    direction: rtl;
}
</style>
