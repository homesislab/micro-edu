<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Plus, 
    FileText, 
    Edit2, 
    Trash2, 
    Eye, 
    ExternalLink,
    Search,
    Filter,
    Clock,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    blogs: Array
});

const deleteBlog = (id) => {
    if (confirm('Delete this blog post?')) {
        useForm({}).delete(route('expert.blogs.destroy', id));
    }
};
</script>

<template>
    <Head title="Expert Blogs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">
                        Educational Blogs<span class="text-indigo-600">.</span>
                    </h2>
                    <p class="text-slate-500 font-medium">Share insights and updates with your learners.</p>
                </div>
                <Link :href="route('expert.blogs.create')" 
                      class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black flex items-center gap-2 shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                    <Plus class="w-5 h-5" /> Write New Post
                </Link>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Search & Filters -->
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
                    <Search class="w-5 h-5 text-slate-400" />
                    <input type="text" placeholder="Search your articles..." class="bg-transparent border-none text-sm w-full focus:ring-0" />
                </div>
                <div class="flex items-center gap-2">
                    <button class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-slate-400 hover:text-slate-900 transition-all flex items-center gap-2 font-bold text-sm">
                        <Filter class="w-4 h-4" /> All Categories
                    </button>
                </div>
            </div>

            <!-- Blog Cards -->
            <div v-if="blogs.length === 0" class="bg-white p-20 rounded-[3rem] border-2 border-dashed border-slate-200 text-center">
                <div class="bg-slate-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-8">
                    <FileText class="text-slate-300 w-12 h-12" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2">No articles yet.</h3>
                <p class="text-slate-400 font-medium max-w-sm mx-auto mb-8">Start sharing your expertise through educational blogs to build authority and engage your audience.</p>
                <Link :href="route('expert.blogs.create')" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black inline-flex items-center gap-2 shadow-xl hover:bg-slate-800 transition-all">
                    <Plus class="w-5 h-5" /> Start Writing
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div v-for="blog in blogs" :key="blog.id" 
                     class="group bg-white rounded-[2.5rem] border border-slate-100 overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 flex flex-col">
                    <div class="p-8 flex-1">
                        <div class="flex items-center justify-between mb-6">
                            <span :class="blog.status === 'published' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'"
                                  class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border">
                                {{ blog.status }}
                            </span>
                            <div class="flex items-center gap-2 text-slate-400 text-xs font-bold">
                                <Clock class="w-3 h-3" />
                                {{ blog.published_at ? new Date(blog.published_at).toLocaleDateString() : 'Draft' }}
                            </div>
                        </div>

                        <h4 class="text-2xl font-black text-slate-900 mb-3 group-hover:text-indigo-600 transition-colors leading-tight">
                            {{ blog.title }}
                        </h4>
                        
                        <p class="text-slate-500 font-medium line-clamp-3 mb-6 leading-relaxed">
                            {{ blog.content.substring(0, 200) }}...
                        </p>

                        <div class="flex items-center gap-2">
                            <span v-if="blog.category" class="px-3 py-1 bg-slate-50 rounded-full text-[10px] font-bold text-slate-500 lowercase tracking-wider">
                                #{{ blog.category }}
                            </span>
                        </div>
                    </div>

                    <div class="px-8 py-6 bg-slate-50/50 flex items-center justify-between border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <Link :href="route('expert.blogs.edit', blog.id)" class="p-3 bg-white text-slate-400 rounded-xl hover:text-indigo-600 hover:shadow-md transition-all">
                                <Edit2 class="w-4 h-4" />
                            </Link>
                            <button @click="deleteBlog(blog.id)" class="p-3 bg-white text-slate-400 rounded-xl hover:text-red-600 hover:shadow-md transition-all">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                        <button class="flex items-center gap-2 text-xs font-black text-slate-400 hover:text-slate-900 transition-all">
                            View Live <ExternalLink class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
