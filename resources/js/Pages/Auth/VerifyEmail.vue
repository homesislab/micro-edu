<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, CheckCircle2, RefreshCw } from 'lucide-vue-next';

const props = defineProps({ status: { type: String } });

const form = useForm({});
const submit = () => { form.post(route('verification.send')); };
const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="space-y-8">
            <!-- Icon -->
            <div class="w-20 h-20 bg-indigo-50 rounded-[1.75rem] flex items-center justify-center border border-indigo-100">
                <Mail class="w-10 h-10 text-indigo-600" />
            </div>

            <div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Verify your email.</h2>
                <p class="text-slate-500 font-medium mt-3 leading-relaxed">
                    Thanks for signing up! We sent a verification link to your email address.
                    Please click it to activate your account.
                </p>
            </div>

            <!-- Success state -->
            <div v-if="verificationLinkSent" class="flex items-start gap-4 bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
                <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                <p class="text-sm font-bold text-emerald-700">A new verification link has been sent to your email address.</p>
            </div>

            <form @submit.prevent="submit">
                <button type="submit" :disabled="form.processing"
                        class="w-full bg-slate-900 text-white rounded-2xl font-black text-base shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:-translate-y-0.5 transition-all active:scale-[.98] disabled:opacity-50 flex items-center justify-center gap-3 py-[1.1rem]">
                    <RefreshCw class="w-5 h-5" :class="form.processing ? 'animate-spin' : ''" />
                    <span>{{ form.processing ? 'Sending...' : 'Resend Verification Email' }}</span>
                </button>
            </form>

            <div class="text-center">
                <Link :href="route('logout')" method="post" as="button"
                      class="text-sm text-slate-400 hover:text-rose-500 font-bold transition-colors">
                    Sign out and try a different account
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
