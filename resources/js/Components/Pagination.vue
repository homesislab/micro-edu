<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps({
    links: Array
});
</script>

<template>
    <div v-if="links.length > 3" class="flex items-center justify-center gap-2">
        <template v-for="(link, key) in links" :key="key">
            <div v-if="link.url === null" 
                 class="px-4 py-2 text-slate-300 text-xs font-black uppercase tracking-widest cursor-not-allowed"
                 v-html="link.label" />
            
            <Link v-else 
                  :href="link.url" 
                  class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all"
                  :class="link.active ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 border border-slate-100'"
                  v-html="link.label" />
        </template>
    </div>
</template>
