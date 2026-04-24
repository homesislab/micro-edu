<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Lock, Eye, EyeOff } from 'lucide-vue-next';

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section class="space-y-8">
        <div>
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Update Password</h3>
            <p class="text-slate-400 font-medium mt-1">Use a long, random password to keep your account secure.</p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-5">
            <!-- Current Password -->
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Current Password</label>
                <div class="relative group">
                    <Lock class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                    <input
                        ref="currentPasswordInput"
                        id="current_password"
                        v-model="form.current_password"
                        :type="showCurrent ? 'text' : 'password'"
                        autocomplete="current-password"
                        placeholder="Your current password"
                        class="w-full pl-14 pr-14 py-4 bg-slate-50 border-2 border-transparent rounded-2xl font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50 outline-none transition-all"
                    />
                    <button type="button" @click="showCurrent = !showCurrent"
                            class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-600 transition-colors">
                        <EyeOff v-if="showCurrent" class="w-5 h-5" />
                        <Eye v-else class="w-5 h-5" />
                    </button>
                </div>
                <InputError :message="form.errors.current_password" />
            </div>

            <!-- New Password -->
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New Password</label>
                <div class="relative group">
                    <Lock class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                    <input
                        ref="passwordInput"
                        id="password"
                        v-model="form.password"
                        :type="showNew ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Min. 8 characters"
                        class="w-full pl-14 pr-14 py-4 bg-slate-50 border-2 border-transparent rounded-2xl font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50 outline-none transition-all"
                    />
                    <button type="button" @click="showNew = !showNew"
                            class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-600 transition-colors">
                        <EyeOff v-if="showNew" class="w-5 h-5" />
                        <Eye v-else class="w-5 h-5" />
                    </button>
                </div>
                <InputError :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Confirm New Password</label>
                <div class="relative group">
                    <Lock class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Repeat new password"
                        class="w-full pl-14 pr-14 py-4 bg-slate-50 border-2 border-transparent rounded-2xl font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-50 outline-none transition-all"
                    />
                    <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-600 transition-colors">
                        <EyeOff v-if="showConfirm" class="w-5 h-5" />
                        <Eye v-else class="w-5 h-5" />
                    </button>
                </div>
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                        :disabled="form.processing"
                        class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl font-black shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 disabled:opacity-50">
                    {{ form.processing ? 'Updating...' : 'Update Password' }}
                </button>
                <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-1" leave-active-class="transition ease-in duration-200" leave-to-class="opacity-0">
                    <span v-if="form.recentlySuccessful" class="text-sm font-bold text-emerald-600">✓ Password updated</span>
                </Transition>
            </div>
        </form>
    </section>
</template>
