<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { User, Mail, Lock, Eye, EyeOff, ArrowRight } from 'lucide-vue-next';

const showPassword = ref(false);
const showConfirm = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Create Account" />

        <div class="space-y-8">
            <!-- Header -->
            <div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Create account.</h2>
                <p class="text-slate-500 font-medium mt-2">
                    Already have one?
                    <Link :href="route('login')" class="text-indigo-600 font-bold hover:text-indigo-700 transition-colors">Sign in</Link>
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Full Name -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
                    <div class="relative group">
                        <User class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            autofocus
                            required
                            placeholder="Your full name"
                            class="w-full pl-14 pr-5 py-4 bg-white border-2 border-slate-100 rounded-2xl font-medium text-slate-900 placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all shadow-sm"
                        />
                    </div>
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Work Email</label>
                    <div class="relative group">
                        <Mail class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
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
                            autocomplete="new-password"
                            required
                            placeholder="Min. 8 characters"
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

                <!-- Confirm Password -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Confirm Password</label>
                    <div class="relative group">
                        <Lock class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            autocomplete="new-password"
                            required
                            placeholder="Repeat password"
                            class="w-full pl-14 pr-14 py-4 bg-white border-2 border-slate-100 rounded-2xl font-medium text-slate-900 placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 outline-none transition-all shadow-sm"
                        />
                        <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-600 transition-colors">
                            <EyeOff v-if="showConfirm" class="w-5 h-5" />
                            <Eye v-else class="w-5 h-5" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Terms fine print -->
                <p class="text-xs text-slate-400 font-medium px-1">
                    By creating an account you agree to our
                    <a href="#" class="text-indigo-600 font-bold hover:underline">Terms of Service</a>
                    and <a href="#" class="text-indigo-600 font-bold hover:underline">Privacy Policy</a>.
                </p>

                <!-- Submit -->
                <button type="submit"
                        :disabled="form.processing"
                        class="w-full bg-slate-900 text-white rounded-2xl font-black text-base shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:-translate-y-0.5 transition-all active:scale-[.98] disabled:opacity-50 flex items-center justify-center gap-3 py-[1.1rem] mt-2">
                    <span>{{ form.processing ? 'Creating account...' : 'Create Account' }}</span>
                    <ArrowRight v-if="!form.processing" class="w-5 h-5" />
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
