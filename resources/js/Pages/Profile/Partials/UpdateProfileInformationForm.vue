<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { User, Mail } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="space-y-8">
        <div>
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Profile Information</h3>
            <p class="text-slate-400 font-medium mt-1">Update your name and email address.</p>
        </div>

        <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-5">
            <!-- Name -->
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
                <div class="relative group">
                    <User class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                    <input
                        v-model="form.name"
                        id="name"
                        type="text"
                        autocomplete="name"
                        required
                        class="w-full pl-14 pr-5 py-4 bg-slate-50 border-2 border-transparent rounded-2xl font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50 outline-none transition-all"
                    />
                </div>
                <InputError :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                <div class="relative group">
                    <Mail class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                    <input
                        v-model="form.email"
                        id="email"
                        type="email"
                        autocomplete="username"
                        required
                        class="w-full pl-14 pr-5 py-4 bg-slate-50 border-2 border-transparent rounded-2xl font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50 outline-none transition-all"
                    />
                </div>
                <InputError :message="form.errors.email" />
            </div>

            <!-- Email verification notice -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                <p class="text-sm font-bold text-amber-700">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button"
                          class="underline font-black hover:text-amber-900 transition-colors">
                        Resend verification email
                    </Link>
                </p>
                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-emerald-600">
                    Verification link sent to your inbox.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                        :disabled="form.processing"
                        class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl font-black shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 disabled:opacity-50">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
                <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-1" leave-active-class="transition ease-in duration-200" leave-to-class="opacity-0">
                    <span v-if="form.recentlySuccessful" class="text-sm font-bold text-emerald-600">✓ Saved successfully</span>
                </Transition>
            </div>
        </form>
    </section>
</template>
