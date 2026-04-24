<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Mail, Lock, Eye, EyeOff, ArrowRight } from 'lucide-vue-next';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign In" />

        <div class="space-y-8">
            <!-- Header -->
            <div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Welcome back.</h2>
                <p class="text-slate-500 font-medium mt-2">
                    Don't have an account?
                    <Link :href="route('register')" class="text-indigo-600 font-bold hover:text-indigo-700 transition-colors">Start for free</Link>
                </p>
            </div>

            <!-- Status -->
            <div v-if="status" class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-bold px-5 py-4 rounded-2xl">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Email -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                    <div class="relative group">
                        <Mail class="absolute left-5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
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

                <!-- Password -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                    <div class="relative group">
                        <Lock class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
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

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" v-model="form.remember" class="sr-only peer" />
                            <div class="w-5 h-5 border-2 border-slate-200 rounded-lg bg-white peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all flex items-center justify-center">
                                <svg v-if="form.remember" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-sm text-slate-500 font-medium select-none">Remember me</span>
                    </label>
                    <Link v-if="canResetPassword" :href="route('password.request')"
                          class="text-sm text-indigo-600 font-bold hover:text-indigo-700 transition-colors">
                        Forgot password?
                    </Link>
                </div>

                <!-- Submit -->
                <button type="submit"
                        :disabled="form.processing"
                        class="w-full bg-slate-900 text-white py-4.5 rounded-2xl font-black text-base shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:-translate-y-0.5 transition-all active:scale-[.98] disabled:opacity-50 flex items-center justify-center gap-3 mt-2 py-[1.1rem]">
                    <span>{{ form.processing ? 'Signing in...' : 'Sign In' }}</span>
                    <ArrowRight v-if="!form.processing" class="w-5 h-5" />
                </button>
            </form>

            <!-- Social proof -->
            <div class="flex items-center justify-center gap-6 pt-2">
                <div v-for="i in 4" :key="i" class="w-7 h-7 rounded-full bg-slate-100 border-2 border-white shadow-sm -ml-2 first:ml-0"></div>
                <p class="text-xs text-slate-400 font-medium">+1,200 professionals enrolled</p>
            </div>
        </div>
    </GuestLayout>
</template>
