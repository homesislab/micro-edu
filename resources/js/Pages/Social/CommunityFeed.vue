<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    MessageSquare, 
    Share2, 
    MoreVertical, 
    Pin, 
    Send, 
    User,
    CornerDownRight,
    PlusCircle,
    Flame,
    Hash
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    course: Object,
    threads: Object, // Paginated
});

const threadForm = useForm({
    title: '',
    content: '',
});

const commentForm = useForm({
    content: '',
    parent_id: null,
});

const activeThreadId = ref(null);
const activeReplyId = ref(null);

const storeThread = () => {
    threadForm.post(route('community.threads.store', props.course.id), {
        onSuccess: () => {
            threadForm.reset();
            showNewThreadForm.value = false;
        },
    });
};

const storeComment = (threadId) => {
    commentForm.post(route('community.comments.store', threadId), {
        onSuccess: () => {
            commentForm.reset();
            activeThreadId.value = null;
            activeReplyId.value = null;
        },
    });
};

const showNewThreadForm = ref(false);

const getStatusColor = (status) => {
    switch(status) {
        case 'paid': return 'bg-emerald-100 text-emerald-700';
        case 'pending': return 'bg-amber-100 text-amber-700';
        default: return 'bg-slate-100 text-slate-700';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${course.title} Community`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Class Feed</h2>
                    <p class="text-slate-500 font-medium">Connect with fellow professionals in {{ course.title }}.</p>
                </div>
                
                <button 
                    @click="showNewThreadForm = !showNewThreadForm"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-200"
                >
                    <PlusCircle class="w-5 h-5" />
                    {{ showNewThreadForm ? 'Cancel Discussion' : 'Start Discussion' }}
                </button>
            </div>
        </template>

        <div class="max-w-4xl mx-auto py-8 px-4 space-y-8">
            <!-- New Thread Form -->
            <div v-if="showNewThreadForm" class="bg-white rounded-3xl p-8 border border-slate-100 shadow-xl shadow-slate-100 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-2">Discussion Theme</label>
                        <input 
                            v-model="threadForm.title"
                            type="text" 
                            placeholder="What's on your mind regarding this class?" 
                            class="w-full bg-slate-50 border-transparent focus:bg-white focus:border-indigo-100 focus:ring-4 focus:ring-indigo-50 rounded-2xl p-4 text-xl font-bold transition-all"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-2">Your Context / Question</label>
                        <textarea 
                            v-model="threadForm.content"
                            rows="4" 
                            placeholder="Share insights, ask for feedback, or provide evidence..." 
                            class="w-full bg-slate-50 border-transparent focus:bg-white focus:border-indigo-100 focus:ring-4 focus:ring-indigo-50 rounded-2xl p-4 transition-all"
                        ></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button 
                            @click="storeThread"
                            :disabled="threadForm.processing"
                            class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 disabled:opacity-50"
                        >
                            {{ threadForm.processing ? 'Posting...' : 'Post to Community' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Feed -->
            <div class="space-y-6">
                <div v-for="thread in threads.data" :key="thread.id" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden group hover:border-indigo-100 transition-all">
                    <!-- Thread Header -->
                    <div class="p-8 pb-4">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold border border-indigo-100">
                                    {{ thread.user.name.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900">{{ thread.user.name }}</h4>
                                    <p class="text-xs text-slate-400 font-medium tracking-tight">Posted {{ thread.created_at }}</p>
                                </div>
                            </div>
                            <div v-if="thread.is_pinned" class="flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                                <Pin class="w-3 h-3" />
                                Announcement
                            </div>
                        </div>

                        <div class="space-y-3 mb-8">
                            <h3 class="text-2xl font-black text-slate-900 leading-tight group-hover:text-indigo-600 transition-colors">{{ thread.title }}</h3>
                            <p class="text-slate-600 leading-relaxed">{{ thread.content }}</p>
                        </div>

                        <div class="flex items-center gap-6 pt-4 border-t border-slate-50">
                            <button @click="activeThreadId = thread.id" class="flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-indigo-600 transition-all">
                                <MessageSquare class="w-5 h-5" />
                                {{ thread.comments_count }} Insights
                            </button>
                            <button class="flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-indigo-600 transition-all">
                                <Share2 class="w-5 h-5" />
                                Share
                            </button>
                        </div>
                    </div>

                    <!-- Thread Comments Section -->
                    <div v-if="activeThreadId === thread.id" class="bg-slate-50/50 p-8 border-t border-slate-50 space-y-6">
                        <!-- Comment List -->
                        <div class="space-y-4">
                            <div v-for="comment in thread.comments" :key="comment.id" class="flex gap-4">
                                <div class="w-8 h-8 rounded-xl bg-slate-200 flex items-center justify-center text-slate-500 text-xs font-bold shrink-0">
                                    {{ comment.user.name.charAt(0) }}
                                </div>
                                <div class="flex-1">
                                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <p class="text-xs font-black text-slate-400 mb-1 uppercase tracking-widest">{{ comment.user.name }}</p>
                                        <p class="text-sm text-slate-700 leading-relaxed">{{ comment.content }}</p>
                                    </div>
                                    <button @click="activeReplyId = comment.id" class="mt-2 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 ml-2">Reply</button>
                                </div>
                            </div>
                        </div>

                        <!-- Add Comment -->
                        <div class="flex gap-4 mt-8 pt-6 border-t border-slate-100">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-lg shadow-slate-200">
                                ME
                            </div>
                            <div class="flex-1 relative">
                                <textarea 
                                    v-model="commentForm.content"
                                    rows="1" 
                                    placeholder="Add your insight..." 
                                    class="w-full bg-white border border-slate-100 focus:border-indigo-100 focus:ring-4 focus:ring-indigo-50 rounded-xl px-4 py-2.5 text-sm transition-all"
                                ></textarea>
                                <button 
                                    @click="storeComment(thread.id)"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                >
                                    <Send class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="threads.data.length === 0" class="text-center py-20 bg-white rounded-[40px] border border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Hash class="w-10 h-10 text-slate-200" />
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">The context is silent...</h3>
                    <p class="text-slate-400 font-medium">Be the first to spark a professional discussion in this community.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-fill-mode: forwards;
}
</style>
