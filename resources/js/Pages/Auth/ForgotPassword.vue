<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, ArrowRight, CheckCircle2 } from 'lucide-vue-next';

defineProps({ status: { type: String } });

const form = useForm({ email: '' });
const submit = () => { form.post(route('password.email')); };
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="space-y-8">
            <div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Forgot password?</h2>
                <p class="text-slate-500 font-medium mt-2">
                    No worries. Enter your email and we'll send you a reset link.
                </p>
            </div>

            <!-- Success state -->
            <div v-if="status" class="flex items-start gap-4 bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
                <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                <p class="text-sm font-bold text-emerald-700">{{ status }}</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                    <div class="relative group">
                        <Mail class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
                            autofocus
                            required
                            placeholder="you@company.com"
                            class="w-full pl-14 pr-5 py-4 bg-white border-2 border-slate-100 rounded-2xl font-medium text-slate-900 placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all shadow-sm"
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <button type="submit" :disabled="form.processing"
                        class="w-full bg-slate-900 text-white rounded-2xl font-black text-base shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:-translate-y-0.5 transition-all active:scale-[.98] disabled:opacity-50 flex items-center justify-center gap-3 py-[1.1rem]">
                    <span>{{ form.processing ? 'Sending...' : 'Send Reset Link' }}</span>
                    <ArrowRight v-if="!form.processing" class="w-5 h-5" />
                </button>

                <div class="text-center">
                    <Link :href="route('login')" class="text-sm text-slate-400 hover:text-indigo-600 font-bold transition-colors">
                        ← Back to Sign In
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
