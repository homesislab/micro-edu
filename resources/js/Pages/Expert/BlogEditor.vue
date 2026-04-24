<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    Save, 
    ArrowLeft, 
    Eye, 
    Type,
    FileText,
    Image as ImageIcon,
    Settings,
    Send
} from 'lucide-vue-next';

const props = defineProps({
    blog: Object
});

const form = useForm({
    title: props.blog?.title ?? '',
    content: props.blog?.content ?? '',
    category: props.blog?.category ?? '',
    status: props.blog?.status ?? 'draft',
});

const submit = () => {
    if (props.blog) {
        form.patch(route('expert.blogs.update', props.blog.id));
    } else {
        form.post(route('expert.blogs.store'));
    }
};
</script>

<template>
    <Head :title="blog ? 'Edit Post' : 'Write Post'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('expert.blogs.index')" class="p-2 hover:bg-slate-100 rounded-xl transition-all">
                        <ArrowLeft class="w-5 h-5 text-slate-400" />
                    </Link>
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 tracking-tight">
                            {{ blog ? 'Edit Article' : 'Draft New Post' }}<span class="text-indigo-600">.</span>
                        </h2>
                        <p class="text-slate-500 font-medium">Craft educational content that inspires.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="submit" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black flex items-center gap-2 shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                        <Send class="w-5 h-5" /> {{ form.status === 'published' ? 'Update Post' : 'Publish Article' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Editor Main -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-slate-100 space-y-8">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Article Title</label>
                        <input v-model="form.title" type="text" placeholder="e.g. Master the Art of Micro-Learning" 
                               class="w-full text-4xl font-black border-none focus:ring-0 placeholder:text-slate-200 p-0" />
                    </div>

                    <div class="h-px bg-slate-100 w-full"></div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Content Body</label>
                        <textarea v-model="form.content" rows="20" placeholder="Start writing your expertise..."
                                  class="w-full text-lg font-medium border-none focus:ring-0 placeholder:text-slate-200 p-0 resize-none leading-relaxed text-slate-700"></textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar Controls -->
            <div class="space-y-8">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-6">
                    <h4 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <Settings class="w-5 h-5 text-indigo-500" /> Publishing Settings
                    </h4>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Post Category</label>
                            <input v-model="form.category" type="text" placeholder="e.g. Technology, Leadership" class="input-premium py-4" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Status</label>
                            <select v-model="form.status" class="input-premium py-4">
                                <option value="draft">Draft Mode</option>
                                <option value="published">Ready to Publish</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-3xl space-y-4">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-400">Word Count</span>
                            <span class="text-slate-900">{{ form.content.split(/\s+/).filter(x => x).length }} words</span>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-400">Read Time</span>
                            <span class="text-slate-900">{{ Math.ceil(form.content.split(/\s+/).length / 200) }} min</span>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-900 p-8 rounded-[2.5rem] text-white shadow-2xl shadow-indigo-200">
                    <h5 class="font-black mb-2 flex items-center gap-2">
                        <Eye class="w-5 h-5" /> Live Preview
                    </h5>
                    <p class="text-indigo-300 text-xs font-medium mb-6">See how your article looks to participants before publishing.</p>
                    <button class="w-full py-4 bg-white/10 hover:bg-white/20 border border-white/10 rounded-2xl font-black transition-all">
                        Preview Article
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
