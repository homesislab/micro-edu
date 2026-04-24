<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Plus,
    Users,
    Award,
    TrendingUp,
    BookOpen,
    ArrowRight,
    X,
    Loader2,
    Trash2,
    Rocket,
    Clock
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    courses: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            totalStudents: 0,
            activeCertifications: 0,
            averageSuccessVelocity: 0
        })
    }
});

const showCreateModal = ref(false);

const createForm = useForm({
    title: '',
    description: '',
    passing_grade: 75,
});

const submitCreateCourse = () => {
    createForm.post(route('expert.courses.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const deleteCourse = (id) => {
    if (confirm('Are you sure you want to delete this program? This action cannot be undone.')) {
        useForm({}).delete(route('expert.courses.delete', id));
    }
};

const toggleStatus = (course) => {
    const newStatus = course.status === 'published' ? 'draft' : 'published';
    useForm({
        status: newStatus
    }).patch(route('expert.courses.status.update', course.id), {
        preserveScroll: true
    });
};

const globalMetrics = computed(() => {
    return [
        {
            label: 'Total Students',
            subtitle: 'Network Reach',
            value: props.stats?.totalStudents || 0,
            icon: Users,
            color: 'text-blue-600',
            bg: 'bg-blue-50/50',
            accent: 'border-l-4 border-blue-500'
        },
        {
            label: 'Active Certifications',
            subtitle: 'Valid Credentials',
            value: props.stats?.activeCertifications || 0,
            icon: Award,
            color: 'text-emerald-600',
            bg: 'bg-emerald-50/50',
            accent: 'border-l-4 border-emerald-500'
        },
        {
            label: 'Average Success Velocity',
            subtitle: 'Success Rate',
            value: `${props.stats?.averageSuccessVelocity || 0}%`,
            icon: TrendingUp,
            color: 'text-indigo-600',
            bg: 'bg-indigo-50/50',
            accent: 'border-l-4 border-indigo-500'
        }
    ];
});
</script>

<template>
    <Head title="My Programs - Expert Dashboard" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="mb-12">
            <div class="relative">
                <div class="absolute -left-6 -top-6 w-16 h-16 bg-indigo-500/10 blur-3xl rounded-full animate-pulse"></div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight relative">
                    Expert Control Center<span class="text-indigo-600">.</span>
                </h1>
                <p class="text-slate-500 mt-2 font-semibold flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Manage your learning products and track overall impact.
                </p>
            </div>
        </div>

        <!-- Global Metrics Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div v-for="metric in globalMetrics" :key="metric.label"
                 :class="[metric.bg, metric.accent]"
                 class="rounded-3xl p-6 border border-slate-200/50 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">
                            {{ metric.subtitle }}
                        </p>
                        <p class="text-xl font-black text-slate-900">{{ metric.label }}</p>
                    </div>
                    <div :class="[metric.color, 'bg-white/60']" class="p-3 rounded-2xl border border-slate-200/50">
                        <component :is="metric.icon" class="w-5 h-5" />
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-900">{{ metric.value }}</p>
            </div>
        </div>

        <!-- Programs Section Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Your Learning Products</h2>
                <p class="text-slate-500 font-medium mt-1">Manage and track your courses</p>
            </div>
            <button @click="showCreateModal = true"
                    class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/30 hover:shadow-lg hover:shadow-indigo-600/40">
                <Plus class="w-4 h-4" />
                Design New Program
            </button>
        </div>

        <!-- Programs Grid -->
        <div v-if="props.courses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link v-for="course in props.courses" :key="course.id"
                  :href="route('expert.courses.builder', course.id)"
                  class="group bg-white rounded-3xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 border border-slate-200/50 hover:border-indigo-200/50">
                
                <!-- Top Section: Purple Background with Icon -->
                <div class="relative h-40 bg-gradient-to-br from-indigo-600 to-indigo-700 flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform duration-300">
                    <!-- Decorative gradient overlay -->
                    <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml,%3Csvg%20width=%2760%27%20height=%2760%27%20viewBox=%270%200%2060%2060%27%20xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cg%20fill=%27none%27%20fill-rule=%27evenodd%27%3E%3Cg%20fill=%27white%27%20fill-opacity=%270.1%27%3E%3Cpath%20d=%27M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%27/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
                    
                    <!-- Icon -->
                    <div class="relative w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30">
                        <BookOpen class="w-8 h-8 text-white" />
                    </div>

                    <div class="absolute top-4 left-4">
                        <span :class="course.status === 'published' ? 'bg-emerald-500 text-white' : 'bg-white/90 text-slate-800'"
                              class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-md shadow-sm border border-black/5">
                            {{ course.status || 'Draft' }}
                        </span>
                    </div>
                </div>

                <!-- Bottom Section: Course Info -->
                <div class="p-6">
                    <!-- Course Title -->
                    <h3 class="font-bold text-lg text-slate-900 mb-1 group-hover:text-indigo-600 transition-colors line-clamp-2">
                        {{ course?.title }}
                    </h3>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">
                        {{ course?.section?.name || 'General' }}
                    </p>

                    <!-- Stats Row -->
                    <div class="grid grid-cols-2 gap-3 mb-4 py-3 border-t border-b border-slate-100">
                        <!-- Student Count -->
                        <div class="flex items-center gap-2">
                            <Users class="w-4 h-4 text-indigo-600" />
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Students</p>
                                <p class="text-lg font-black text-slate-900">{{ course.enrollments_count || 0 }}</p>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 text-emerald-600 flex items-center justify-center font-black">$</div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Price</p>
                                <p class="text-lg font-black text-slate-900">${{ course.price || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Actions -->
                    <div class="flex items-center justify-end text-sm">
                        <div class="flex items-center gap-2 text-indigo-600 transition-transform group-hover:translate-x-1">
                             <span class="text-[10px] font-black uppercase tracking-widest">Build</span>
                             <ArrowRight class="w-4 h-4" />
                        </div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-gradient-to-br from-slate-50 to-slate-100/50 rounded-4xl border-2 border-dashed border-slate-300 p-16 text-center">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <BookOpen class="w-10 h-10 text-slate-300" />
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2">No Programs Yet</h3>
            <p class="text-slate-500 font-semibold mb-6 max-w-md mx-auto">
                Start creating your first learning product to reach students and build your impact.
            </p>
            <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/30">
                <Plus class="w-4 h-4" />
                Create Your First Program
            </button>
        </div>

        <!-- Create Program Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="2xl">
            <div class="p-6 bg-white rounded-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-black text-slate-900">Design New Program</h3>
                    <button @click="showCreateModal = false" class="p-1 hover:bg-slate-100 rounded-lg transition-colors">
                        <X class="w-6 h-6 text-slate-400" />
                    </button>
                </div>

                <form @submit.prevent="submitCreateCourse" class="space-y-5">
                    <!-- Program Title -->
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2">Program Title</label>
                        <input v-model="createForm.title" type="text" placeholder="e.g., Advanced Web Development"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all" />
                        <InputError :message="createForm.errors.title" class="mt-2" />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2">Description</label>
                        <textarea v-model="createForm.description" placeholder="Describe your program, learning outcomes, and target audience..."
                                  rows="4"
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all resize-none"></textarea>
                        <InputError :message="createForm.errors.description" class="mt-2" />
                    </div>

                    <!-- Passing Grade -->
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2">Passing Grade (%)</label>
                        <input v-model.number="createForm.passing_grade" type="number" min="0" max="100"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all" />
                        <InputError :message="createForm.errors.passing_grade" class="mt-2" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="showCreateModal = false"
                                class="flex-1 px-4 py-3 text-slate-700 bg-slate-100 rounded-xl font-bold hover:bg-slate-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="createForm.processing"
                                class="flex-1 px-4 py-3 text-white bg-indigo-600 rounded-xl font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <Loader2 v-if="createForm.processing" class="w-4 h-4 animate-spin" />
                            {{ createForm.processing ? 'Creating...' : 'Create Program' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Footer Spacing -->
        <div class="mt-16"></div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Smooth transitions */
:deep(.transition-all) {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>
