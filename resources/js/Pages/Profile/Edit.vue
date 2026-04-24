<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { User, Lock, Trash2, ShieldCheck } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const activeTab = ref('profile');
const page = usePage();
const user = computed(() => page.props.auth.user);

const tabs = [
    { key: 'profile', label: 'Profile Info', icon: User },
    { key: 'password', label: 'Password', icon: Lock },
    { key: 'danger', label: 'Danger Zone', icon: Trash2 },
];
</script>

<template>
    <Head title="Profile Settings" />
    <AuthenticatedLayout>
        <template #header>
            <div class="py-4">
                <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] mb-3">
                    <span class="w-8 h-px bg-indigo-600"></span> Account
                </div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight">Profile Settings<span class="text-indigo-600">.</span></h2>
                <p class="text-slate-500 font-medium mt-1">Manage your identity, security, and account preferences.</p>
            </div>
        </template>

        <div class="max-w-5xl space-y-10">
            <!-- User identity card -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm flex items-center gap-8">
                <div class="relative">
                    <div class="w-24 h-24 bg-indigo-600 rounded-[1.75rem] flex items-center justify-center shadow-2xl shadow-indigo-200 text-white text-3xl font-black select-none">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 border-2 border-white rounded-full"></div>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ user.name }}</p>
                    <p class="text-slate-400 font-medium">{{ user.email }}</p>
                    <span class="inline-flex items-center gap-1.5 mt-2 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border border-indigo-100">
                        <ShieldCheck class="w-3 h-3" /> {{ user.role }}
                    </span>
                </div>
            </div>

            <!-- Tab bar -->
            <div class="flex items-center p-1.5 bg-slate-200/50 backdrop-blur-xl rounded-[1.75rem] border border-white/50 shadow-inner w-fit">
                <button v-for="tab in tabs" :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="activeTab === tab.key ? 'bg-white text-indigo-700 shadow-md ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-900'"
                        class="px-6 py-2.5 rounded-2xl font-black text-xs transition-all flex items-center gap-2">
                    <component :is="tab.icon" class="w-4 h-4" />
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tab content -->
            <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm p-10 md:p-12">
                <div v-if="activeTab === 'profile'">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                </div>
                <div v-if="activeTab === 'password'">
                    <UpdatePasswordForm />
                </div>
                <div v-if="activeTab === 'danger'">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
