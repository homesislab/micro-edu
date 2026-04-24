<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ShieldCheck, Lock, Eye, EyeOff, ArrowRight } from 'lucide-vue-next';

const showPassword = ref(false);

const form = useForm({ password: '' });
const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="space-y-8">
            <!-- Icon -->
            <div class="w-20 h-20 bg-slate-900 rounded-[1.75rem] flex items-center justify-center shadow-2xl shadow-slate-900/20">
                <ShieldCheck class="w-10 h-10 text-white" />
            </div>

            <div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Confirm identity.</h2>
                <p class="text-slate-500 font-medium mt-3 leading-relaxed">
                    This is a secure area. Please re-enter your password to continue.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                    <div class="relative group">
                        <Lock class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            autofocus
                            required
                            placeholder="Your password"
                            class="w-full pl-14 pr-14 py-4 bg-white border-2 border-slate-100 rounded-2xl font-medium text-slate-900 placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all shadow-sm"
                        />
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-600 transition-colors">
                            <EyeOff v-if="showPassword" class="w-5 h-5" />
                            <Eye v-else class="w-5 h-5" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <button type="submit" :disabled="form.processing"
                        class="w-full bg-slate-900 text-white rounded-2xl font-black text-base shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:-translate-y-0.5 transition-all active:scale-[.98] disabled:opacity-50 flex items-center justify-center gap-3 py-[1.1rem]">
                    <span>{{ form.processing ? 'Confirming...' : 'Confirm & Continue' }}</span>
                    <ArrowRight v-if="!form.processing" class="w-5 h-5" />
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
