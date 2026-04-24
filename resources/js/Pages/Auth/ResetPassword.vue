<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Mail, Lock, Eye, EyeOff, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const showPassword = ref(false);
const showConfirm = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div class="space-y-8">
            <div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Set new password.</h2>
                <p class="text-slate-500 font-medium mt-2">Choose a strong password for your account.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Email (readonly) -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                    <div class="relative">
                        <Mail class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 w-5 h-5" />
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
                            required
                            readonly
                            class="w-full pl-14 pr-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-medium text-slate-400 outline-none cursor-not-allowed"
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <!-- New Password -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New Password</label>
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
                            placeholder="Repeat new password"
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

                <button type="submit" :disabled="form.processing"
                        class="w-full bg-slate-900 text-white rounded-2xl font-black text-base shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:-translate-y-0.5 transition-all active:scale-[.98] disabled:opacity-50 flex items-center justify-center gap-3 py-[1.1rem]">
                    <span>{{ form.processing ? 'Resetting...' : 'Reset Password' }}</span>
                    <ArrowRight v-if="!form.processing" class="w-5 h-5" />
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
