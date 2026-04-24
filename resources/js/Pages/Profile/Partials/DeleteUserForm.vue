<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { Trash2, Lock, Eye, EyeOff, AlertTriangle, X } from 'lucide-vue-next';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);
const showPassword = ref(false);

const form = useForm({ password: '' });

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-8">
        <div>
            <h3 class="text-2xl font-black text-rose-600 tracking-tight">Danger Zone</h3>
            <p class="text-slate-400 font-medium mt-1">Permanently delete your account and all associated data.</p>
        </div>

        <!-- Warning card -->
        <div class="flex items-start gap-4 bg-rose-50 border border-rose-200 rounded-2xl p-5">
            <AlertTriangle class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" />
            <p class="text-sm font-medium text-rose-700 leading-relaxed">
                Once your account is deleted, <strong>all data will be permanently removed</strong> and cannot be recovered. Please download any data you wish to keep before proceeding.
            </p>
        </div>

        <button @click="confirmUserDeletion"
                class="flex items-center gap-3 px-6 py-3.5 bg-white border-2 border-rose-200 text-rose-600 rounded-2xl font-black text-sm hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all active:scale-95">
            <Trash2 class="w-4 h-4" />
            Delete My Account
        </button>

        <!-- Custom Confirmation Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0">
                <div v-if="confirmingUserDeletion"
                     class="fixed inset-0 z-[200] flex items-center justify-center p-4"
                     @click.self="closeModal">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

                    <!-- Modal panel -->
                    <div class="relative bg-white w-full max-w-lg rounded-[3rem] shadow-[0_0_80px_rgba(0,0,0,0.2)] border border-white overflow-hidden animate-in zoom-in-95 duration-300">
                        <!-- Top danger bar -->
                        <div class="h-1.5 bg-gradient-to-r from-rose-500 to-red-600"></div>

                        <div class="p-10">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center border border-rose-100">
                                        <Trash2 class="w-7 h-7 text-rose-600" />
                                    </div>
                                    <div>
                                        <h4 class="text-2xl font-black text-slate-900 tracking-tight">Delete Account?</h4>
                                        <p class="text-slate-400 font-medium text-sm mt-0.5">This action is irreversible.</p>
                                    </div>
                                </div>
                                <button @click="closeModal" class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center hover:bg-slate-100 transition-all text-slate-400">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <p class="text-slate-500 font-medium leading-relaxed mb-8">
                                All your learning progress, certificates, and enrollments will be permanently erased. Please enter your password to confirm this irreversible action.
                            </p>

                            <!-- Password input -->
                            <div class="space-y-2 mb-8">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Confirm with Password</label>
                                <div class="relative group">
                                    <Lock class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-rose-500 transition-colors w-5 h-5" />
                                    <input
                                        ref="passwordInput"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="Your current password"
                                        @keyup.enter="deleteUser"
                                        class="w-full pl-14 pr-14 py-4 bg-rose-50/50 border-2 border-rose-100 rounded-2xl font-medium text-slate-900 placeholder:text-slate-300 focus:border-rose-500 focus:ring-4 focus:ring-rose-50 outline-none transition-all"
                                    />
                                    <button type="button" @click="showPassword = !showPassword"
                                            class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-600 transition-colors">
                                        <EyeOff v-if="showPassword" class="w-5 h-5" />
                                        <Eye v-else class="w-5 h-5" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password" />
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button @click="closeModal"
                                        class="flex-1 py-3.5 bg-slate-50 text-slate-600 rounded-2xl font-black text-sm hover:bg-slate-100 transition-all active:scale-95">
                                    Cancel
                                </button>
                                <button @click="deleteUser"
                                        :disabled="form.processing"
                                        class="flex-1 py-3.5 bg-rose-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-rose-200 hover:bg-red-700 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2">
                                    <Trash2 class="w-4 h-4" />
                                    {{ form.processing ? 'Deleting...' : 'Yes, Delete Account' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>
