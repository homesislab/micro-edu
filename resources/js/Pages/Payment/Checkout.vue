<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ShieldCheck, 
    CreditCard, 
    Zap, 
    Lock, 
    CheckCircle,
    ChevronRight,
    HelpCircle,
    ArrowLeft
} from 'lucide-vue-next';

const props = defineProps({
    course: Object,
});

const form = useForm({});

const submit = () => {
    form.post(route('payment.process', props.course.id));
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Checkout: ${course.title}`" />

        <div class="max-w-5xl mx-auto py-12 px-4">
            <Link :href="route('catalog')" class="inline-flex items-center gap-2 text-slate-400 font-bold hover:text-indigo-600 transition-all mb-8 bg-white px-4 py-2 rounded-xl border border-slate-100 shadow-sm">
                <ArrowLeft class="w-4 h-4" />
                Back to Catalog
            </Link>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Left: Order Summary -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-[40px] p-10 border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8">
                            <Zap class="w-12 h-12 text-indigo-50 opacity-20" />
                        </div>

                        <h2 class="text-3xl font-black text-slate-900 mb-2">Program Checkout</h2>
                        <p class="text-slate-500 font-medium mb-12">Review your professional development enrollment details.</p>

                        <div class="flex flex-col md:flex-row gap-8 items-start mb-12">
                            <div class="w-full md:w-48 aspect-video md:aspect-square bg-slate-100 rounded-3xl overflow-hidden shadow-inner flex items-center justify-center text-slate-300">
                                <Zap class="w-12 h-12" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest">Premium Program</span>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest">Expert Verified</span>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 mb-2">{{ course.title }}</h3>
                                <div class="flex items-center gap-4 text-sm text-slate-400 font-bold mb-6">
                                    <div class="flex items-center gap-1.5"><User class="w-4 h-4" /> {{ course.expert.name }}</div>
                                    <div class="flex items-center gap-1.5"><ShieldCheck class="w-4 h-4" /> {{ course.academy.name }}</div>
                                </div>
                                <p class="text-slate-500 line-clamp-2 text-sm leading-relaxed">{{ course.description }}</p>
                            </div>
                        </div>

                        <div class="space-y-6 pt-10 border-t border-slate-50">
                            <div class="flex items-center justify-between text-slate-400 font-bold uppercase tracking-widest text-xs">
                                <span>Subtotal</span>
                                <span>{{ formatPrice(course.price) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-400 font-bold uppercase tracking-widest text-xs">
                                <span>Platform Processing</span>
                                <span class="text-emerald-500">Free</span>
                            </div>
                            <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                                <span class="text-xl font-black text-slate-900">Total Investment</span>
                                <span class="text-3xl font-black text-indigo-600">{{ formatPrice(course.price) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center gap-6 p-6 bg-indigo-50 rounded-3xl border border-indigo-100">
                        <div class="p-3 bg-white rounded-2xl shadow-sm text-indigo-600">
                            <ShieldCheck class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm font-black text-indigo-900">Career-Safe Guarantee</p>
                            <p class="text-xs text-indigo-700/60 font-medium">Verified by MicroEducate certification protocol.</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Payment Interface -->
                <div class="space-y-8">
                    <div class="bg-slate-900 rounded-[40px] p-10 text-white shadow-2xl relative overflow-hidden">
                        <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl"></div>
                        
                        <div class="relative z-10">
                            <h3 class="text-xl font-black mb-8 flex items-center gap-2">
                                <CreditCard class="w-6 h-6" />
                                Payment Mode
                            </h3>

                            <div class="space-y-4 mb-12">
                                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex items-center justify-between group cursor-pointer hover:bg-white/10 transition-all border-white/40 ring-2 ring-indigo-500">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                                            <Zap class="w-5 h-5 text-indigo-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">Instant Transfer</p>
                                            <p class="text-[10px] text-white/40 font-black uppercase tracking-widest">QRIS / VA / E-Wallet</p>
                                        </div>
                                    </div>
                                    <CheckCircle class="w-5 h-5 text-indigo-400" />
                                </div>

                                <div class="bg-white/5 border border-white/5 rounded-2xl p-4 flex items-center justify-between opacity-50 cursor-not-allowed">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                                            <CreditCard class="w-5 h-5 text-white/40" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">Credit Card</p>
                                            <p class="text-[10px] text-white/40 font-black uppercase tracking-widest">Visa / Mastercard</p>
                                        </div>
                                    </div>
                                    <Lock class="w-4 h-4 text-white/20" />
                                </div>
                            </div>

                            <button 
                                @click="submit"
                                :disabled="form.processing"
                                class="w-full py-5 bg-indigo-500 text-white font-black rounded-2xl hover:bg-indigo-400 transition-all shadow-xl shadow-indigo-500/20 active:scale-95 flex items-center justify-center gap-2 group mb-6"
                            >
                                {{ form.processing ? 'Verifying...' : 'Unlock Full Access' }}
                                <ChevronRight class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                            </button>

                            <p class="text-[10px] text-center text-white/40 font-bold uppercase tracking-[0.2em] flex items-center justify-center gap-2">
                                <Lock class="w-3 h-3" />
                                Secure Checkout Gateway
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <h4 class="text-sm font-black text-slate-900 mb-4 flex items-center gap-2">
                            <HelpCircle class="w-4 h-4 text-indigo-600" />
                            Need clarification?
                        </h4>
                        <p class="text-xs text-slate-500 leading-relaxed font-medium">
                            Once payment is confirmed, you'll receive a WhatsApp notification and instant access to all core curriculum materials and expert community forums.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
