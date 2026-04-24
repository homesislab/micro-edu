<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Target, Lightbulb, FileText, CheckCircle2, ShieldAlert, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    assignment: Object,
    template: Object,
});

const defaultCriteria = [
    { label: 'Ketepatan',    icon: Target,     description: 'Sejauh mana implementasi sesuai dengan instruksi materi.' },
    { label: 'Kreativitas',  icon: Lightbulb,  description: 'Inovasi dan nilai tambah dalam penyelesaian tugas.' },
    { label: 'Dokumentasi',  icon: FileText,   description: 'Kelengkapan bukti dan kualitas narasi yang diunggah.' },
];

const rubricCriteria = computed(() => props.template?.criteria_json || defaultCriteria);

const form = useForm({
    scores: {},
    status: 'approved',
    expert_notes: '',
});

const resetScores = () => {
    const s = {};
    (rubricCriteria.value || []).forEach(c => { if (c?.label) s[c.label] = 0; });
    form.scores = s;
    form.expert_notes = '';
    form.status = 'approved';
};

onMounted(resetScores);
watch(() => props.assignment?.id, resetScores);

const totalScore  = computed(() => Object.values(form.scores).reduce((acc, v) => acc + (v || 0), 0));
const maxScore    = computed(() => rubricCriteria.value.length * 5);
const scorePct    = computed(() => maxScore.value > 0 ? Math.round((totalScore.value / maxScore.value) * 100) : 0);

const scoreLabel = computed(() => {
    if (scorePct.value === 0)   return { text: 'No Score Yet',  cls: 'text-slate-400' };
    if (scorePct.value >= 80)   return { text: 'Excellent',     cls: 'text-emerald-600' };
    if (scorePct.value >= 60)   return { text: 'Satisfactory',  cls: 'text-amber-600' };
    return { text: 'Needs Work', cls: 'text-rose-600' };
});

const submitReview = () => {
    if (!props.assignment?.id) return;
    form.post(route('expert.rubric.store', props.assignment.id));
};

const ratingLabels = ['Poor', 'Fair', 'Good', 'Great', 'Excellent'];
</script>

<template>
    <div class="flex flex-col xl:flex-row gap-6 w-full items-start">
        <!-- Rubric Criteria List (Kiri) -->
        <div class="flex-1 space-y-4 w-full">
            <h3 class="text-sm font-bold text-slate-900 mb-2">Grading Rubric</h3>
            <div v-for="(c, idx) in rubricCriteria" :key="c.label" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-slate-50 border border-slate-100 rounded-xl">
                <!-- Info Kriteria -->
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-900 text-sm">{{ c.label }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 truncate">{{ c.description }}</p>
                </div>
                <!-- Skala Skor (1-5) -->
                <div class="flex gap-1.5 flex-shrink-0">
                    <label v-for="score in 5" :key="score" class="cursor-pointer">
                        <input type="radio" :name="c.label" :value="score" v-model="form.scores[c.label]" class="sr-only" />
                        <div :class="[
                                'w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold transition-all border-2 select-none',
                                form.scores[c.label] === score
                                    ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm scale-110'
                                    : 'bg-white border-slate-200 text-slate-400 hover:border-indigo-300 hover:text-indigo-600',
                             ]">
                            {{ score }}
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Panel Total & Verdict (Kanan) -->
        <div class="w-full xl:w-72 flex-shrink-0 space-y-4">
            <!-- Teks Total Skor -->
            <div class="p-5 bg-white border border-slate-200 rounded-xl shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Score</p>
                    <p :class="['text-[10px] font-bold mt-0.5 uppercase', scoreLabel.cls]">{{ scoreLabel.text }}</p>
                </div>
                <div class="flex items-baseline gap-1">
                    <span :class="['text-4xl font-extrabold transition-all tabular-nums', scorePct >= 80 ? 'text-emerald-500' : scorePct >= 60 ? 'text-amber-500' : totalScore > 0 ? 'text-rose-500' : 'text-slate-300']">
                        {{ totalScore }}
                    </span>
                    <span class="text-slate-400 font-bold text-sm">/ {{ maxScore }}</span>
                </div>
            </div>

            <!-- Notes & Actions -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 space-y-4">
                <textarea v-model="form.expert_notes" rows="2"
                          class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 transition-all text-sm text-slate-700 outline-none resize-none placeholder-slate-400"
                          placeholder="Optional feedback..."></textarea>
                
                <div class="flex flex-col gap-2">
                    <button @click="() => { form.status = 'approved'; submitReview(); }"
                            :disabled="form.processing || totalScore === 0"
                            class="w-full py-2.5 bg-indigo-600 text-white rounded-lg font-bold text-sm shadow-sm hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <CheckCircle2 class="w-4 h-4" /> Approve (Release Cert)
                    </button>
                    
                    <button @click="() => { form.status = 'rejected'; submitReview(); }"
                            :disabled="form.processing || totalScore === 0"
                            class="w-full py-2.5 bg-white text-slate-600 border border-slate-300 rounded-lg font-bold text-sm hover:bg-slate-50 hover:text-slate-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <ShieldAlert class="w-4 h-4 text-slate-400" /> Request Revision
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
