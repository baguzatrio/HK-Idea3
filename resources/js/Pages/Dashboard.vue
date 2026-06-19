<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { authState } from '@/store/auth';

interface Permission {
    id: number;
    nama: string;
    deskripsi: string | null;
    judul_report: string;
    link_dashboard: string;
}

interface Divisi {
    id: number;
    kode: string;
    nama: string;
    logo: string | null;
    no_urut: number;
    permissions: Permission[];
}

// ── State ────────────────────────────────────────────────────
const divisis = ref<Divisi[]>([]);
const activeDivisi = ref<Divisi | null>(null);
const activePermission = ref<Permission | null>(null);
const isLoading = ref(true);

// Slideshow State
const slides = ref([
    '/images/slide1.jpg',
    '/images/slide2.jpg',
    '/images/slide3.jpg'
]);
const currentSlide = ref(0);
let slideInterval: number;

const fetchDashboardData = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/dashboard');
        divisis.value = response.data.divisis;
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchDashboardData();
    slideInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % slides.value.length;
    }, 15000); // 15 seconds
});

onUnmounted(() => {
    clearInterval(slideInterval);
});

const selectDivisi = (divisi: Divisi) => {
    if (activeDivisi.value?.id === divisi.id) {
        // Klik divisi yang sama = tutup
        activeDivisi.value = null;
        activePermission.value = null;
        return;
    }
    activeDivisi.value = divisi;
    activePermission.value = null;

    // Jika ada permission, langsung tampilkan permission yang paling awal
    if (divisi.permissions.length > 0) {
        activePermission.value = divisi.permissions[0];
    }

    setTimeout(() => {
        window.scrollTo({ top: 500, behavior: 'smooth' });
    },);
};

const selectPermission = (permission: Permission) => {
    if (activePermission.value?.id === permission.id) {
        activePermission.value = null;
        return;
    }
    activePermission.value = permission;
};
</script>

<template>
    <AuthenticatedLayout>
        <!-- ── Slideshow Full Width ── -->
        <div class="relative w-full h-[500px] overflow-hidden bg-gray-900">
            <!-- Images -->
            <TransitionGroup enter-active-class="transition-opacity duration-1000 ease-in-out"
                leave-active-class="transition-opacity duration-1000 ease-in-out" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <img v-for="(slide, index) in slides" :key="slide" :src="slide" v-show="currentSlide === index"
                    class="absolute inset-0 w-full h-full object-cover" alt="Slideshow image" />
            </TransitionGroup>

            <!-- Overlay Transparan -->
            <div class="absolute inset-0 bg-black/40 z-0"></div>

            <!-- Teks Selamat Datang (Rata Kiri) -->
            <div
                class="absolute inset-0 flex flex-col justify-center items-start px-8 sm:px-16 lg:px-32 z-10 text-left">
                <h1 class="text-3xl sm:text-5xl font-bold text-white mb-2 drop-shadow-md">
                    Selamat datang, {{ authState.user?.name }}
                </h1>
                <p class="text-lg sm:text-2xl text-gray-200 drop-shadow-sm">
                    di portal dashboard HK IDEA
                </p>
            </div>
        </div>

        <div class="py-8 space-y-6">

            <!-- ── Section Dashboard (muncul setelah pilih divisi) ── -->
            <Transition enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-3" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-3">
                <div v-if="activeDivisi" id="dashboard-section" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">

                        <!-- Header: Judul -->
                        <div class="px-6 pt-6 pb-2 flex items-start justify-between">
                            <div class="flex-1 text-center">
                                <!-- Judul = nama_report dari permission aktif, fallback nama divisi -->
                                <h2 class="text-2xl font-bold text-gray-800">
                                    Divisi {{ activeDivisi.nama }}
                                </h2>
                                <div class="mt-2 mx-auto w-60 h-0.5 bg-red-500 rounded"></div>
                            </div>
                        </div>

                        <!-- Tombol-tombol Permission/Report -->
                        <div v-if="activeDivisi.permissions.length > 0"
                            class="px-6 py-4 flex flex-wrap justify-center gap-2">
                            <button v-for="perm in activeDivisi.permissions" :key="perm.id"
                                @click="selectPermission(perm)" :class="[
                                    'px-4 py-1.5 text-sm rounded border transition',
                                    activePermission?.id === perm.id
                                        ? 'bg-blue-900 text-white border-blue-900'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400 hover:text-blue-900'
                                ]">
                                {{ perm.judul_report }}
                            </button>
                        </div>

                        <div v-else class="px-6 py-4 text-center text-sm text-gray-400 italic">
                            Tidak ada laporan yang tersedia di divisi {{ activeDivisi.nama }}.
                        </div>

                        <!-- iframe -->
                        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
                            enter-to-class="opacity-100">
                            <div v-if="activePermission && activePermission.link_dashboard"
                                class="border-t border-gray-100 mt-2">
                                <iframe :key="activePermission.id" :src="activePermission.link_dashboard"
                                    :title="activePermission.judul_report" class="w-full"
                                    style="height: 80vh; border: none;" allowfullscreen />
                                <p v-if="activePermission.deskripsi" class="text-left mt-3 text-gray-700 text-sm">
                                    {{ activePermission.deskripsi }}
                                </p>
                            </div>
                        </Transition>

                        <!-- Padding bawah -->
                        <div class="pb-4"></div>
                    </div>
                </div>
            </Transition>

            <!-- ── Grid Divisi ── -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl shadow-md px-8 py-8">

                    <!-- Judul -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800">Hutama Karya</h2>
                        <div class="mt-2 mx-auto w-60 h-0.5 bg-red-500 rounded"></div>
                    </div>

                    <!-- Grid: 6 per baris, rata tengah -->
                    <div v-if="divisis.length === 0" class="text-center py-10 text-gray-400 text-sm">
                        Belum ada divisi yang terdaftar.
                    </div>

                    <div v-else class="flex flex-wrap justify-center gap-4">
                        <button v-for="divisi in divisis" :key="divisi.id" @click="selectDivisi(divisi)" :class="[
                            'flex flex-col items-center gap-2 p-3 rounded-xl border-2 transition-all duration-200',
                            'w-[calc(100%/6-1rem)] min-w-[140px] max-w-[140px]',
                            activeDivisi?.id === divisi.id
                                ? 'border-blue-900 bg-blue-50 shadow-md scale-105'
                                : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 hover:scale-105'
                        ]">
                            <!-- Logo -->
                            <div
                                class="w-16 h-16 flex items-center justify-center rounded-lg overflow-hidden bg-white ">
                                <img v-if="divisi.logo" :src="`/storage/${divisi.logo}`" :alt="divisi.nama"
                                    class="max-w-full max-h-full object-contain p-1" />
                                <div v-else
                                    class="w-full h-full flex items-center justify-center bg-blue-100 text-blue-800 font-bold text-2xl">
                                    {{ divisi.nama.charAt(0).toUpperCase() }}
                                </div>
                            </div>

                            <!-- Kode Divisi -->
                            <span class="text-md font-medium text-gray-700 text-center leading-tight line-clamp-2">
                                {{ divisi.kode }}
                            </span>

                            <!-- Indikator aktif -->
                            <span v-if="activeDivisi?.id === divisi.id" class="" />
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>