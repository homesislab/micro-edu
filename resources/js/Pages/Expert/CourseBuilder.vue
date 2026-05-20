<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Plus, 
    Video, 
    FileText, 
    Trash2, 
    Edit2, 
    ChevronRight, 
    BookOpen, 
    HelpCircle,
    ArrowLeft,
    Save,
    Settings,
    Clock,
    Link as LinkIcon,
    CheckCircle2,
    Circle,
    Shuffle,
    Copy,
    Check as CheckIcon,
    Sparkles,
    Loader2,
    Rocket,
    X
} from 'lucide-vue-next';
import ArchitectStudio from '@/Components/Expert/ArchitectStudio.vue';

const props = defineProps({
    course: Object,
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }

    router.get(route('expert.dashboard'));
};

const isGeneratingCurriculum = ref(false);
const generateCurriculumAI = () => {
    if (!confirm('AI will generate initial materials based on your course vision. Existing materials will not be deleted. Continue?')) return;
    
    isGeneratingCurriculum.value = true;
    router.post(route('expert.ai.generate-curriculum', props.course.id), {}, {
        onFinish: () => isGeneratingCurriculum.value = false,
        preserveScroll: true,
    });
};

</script>

<template>
    <Head :title="'Builder - ' + course.title" />

    <AuthenticatedLayout :fullWorkspace="true">
        <ArchitectStudio 
            :course="course" 
            :is-generating="isGeneratingCurriculum"
            @generate-ai="generateCurriculumAI"
        />
    </AuthenticatedLayout>
</template>
