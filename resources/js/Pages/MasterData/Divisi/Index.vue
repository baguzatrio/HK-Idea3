<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

interface Divisi {
    id: number;
    kode: string;
    nama: string;
    lantai: number | null;
    logo: string | null;

    no_urut: number;
}

const divisis = ref<Divisi[]>([]);
const isLoading = ref(true);

const fetchDivisis = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/divisis');
        divisis.value = response.data.divisis;
    } catch (error) {
        console.error('Error fetching data:', error);
        Swal.fire('Error', 'Gagal mengambil data divisi', 'error');
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchDivisis();
});

// ── Search & Pagination ──────────────────────────────────────
const search = ref('');
const perPage = ref(10);
const currentPage = ref(1);

const filtered = computed(() => {
    const q = search.value.toLowerCase();
    return divisis.value.filter(d =>
        d.kode.toLowerCase().includes(q) ||
        d.nama.toLowerCase().includes(q)
    );
});

const totalPages = computed(() => Math.ceil(filtered.value.length / perPage.value));

const paginated = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filtered.value.slice(start, start + perPage.value);
});

const resetPage = () => { currentPage.value = 1; };

watch(currentPage, () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ── Modal Tambah ─────────────────────────────────────────────
const showAddModal = ref(false);
const processingAdd = ref(false);

const addForm = ref({
    kode: '',
    nama: '',
    lantai: '',
    logo: null as File | null,

    no_urut: '',
    errors: {} as Record<string, string>,
});

const onLogoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    addForm.value.logo = file;
};

const submitAdd = async () => {
    processingAdd.value = true;
    addForm.value.errors = {};
    try {
        const formData = new FormData();
        formData.append('kode', addForm.value.kode);
        formData.append('nama', addForm.value.nama);
        if (addForm.value.lantai) formData.append('lantai', addForm.value.lantai);

        if (addForm.value.no_urut) formData.append('no_urut', addForm.value.no_urut);
        if (addForm.value.logo) formData.append('logo', addForm.value.logo);

        await axios.post('/api/divisis', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        showAddModal.value = false;

        addForm.value.kode = '';
        addForm.value.nama = '';
        addForm.value.lantai = '';

        addForm.value.no_urut = '';
        addForm.value.logo = null;

        Swal.fire('Berhasil!', 'Data divisi berhasil ditambahkan.', 'success');
        fetchDivisis();
    } catch (error: any) {
        if (error.response?.data?.errors) {
            for (const key in error.response.data.errors) {
                addForm.value.errors[key] = error.response.data.errors[key][0];
            }
        }
    } finally {
        processingAdd.value = false;
    }
};

// ── Modal Edit ───────────────────────────────────────────────
const showEditModal = ref(false);
const processingEdit = ref(false);
const editingDivisi = ref<Divisi | null>(null);

const editForm = ref({
    kode: '',
    nama: '',
    lantai: '',
    logo: null as File | null,

    no_urut: '',
    errors: {} as Record<string, string>,
});

const openEdit = (divisi: Divisi) => {
    editingDivisi.value = divisi;
    editForm.value.kode = divisi.kode;
    editForm.value.nama = divisi.nama;
    editForm.value.lantai = divisi.lantai?.toString() ?? '';

    editForm.value.no_urut = divisi.no_urut.toString();
    editForm.value.logo = null;
    editForm.value.errors = {};
    showEditModal.value = true;
};

const onEditLogoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    editForm.value.logo = file;
};

const submitEdit = async () => {
    if (!editingDivisi.value) return;
    processingEdit.value = true;
    editForm.value.errors = {};

    try {
        const formData = new FormData();
        formData.append('kode', editForm.value.kode);
        formData.append('nama', editForm.value.nama);
        if (editForm.value.lantai) formData.append('lantai', editForm.value.lantai);

        if (editForm.value.no_urut) formData.append('no_urut', editForm.value.no_urut);
        if (editForm.value.logo) formData.append('logo', editForm.value.logo);
        formData.append('_method', 'PUT'); // untuk handle file upload di put Laravel

        await axios.post(`/api/divisis/${editingDivisi.value.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        showEditModal.value = false;
        Swal.fire('Berhasil!', 'Data divisi berhasil diperbarui.', 'success');
        fetchDivisis();
    } catch (error: any) {
        if (error.response?.data?.errors) {
            for (const key in error.response.data.errors) {
                editForm.value.errors[key] = error.response.data.errors[key][0];
            }
        }
    } finally {
        processingEdit.value = false;
    }
};

// ── Delete ───────────────────────────────────────────────────
const confirmDelete = (id: number) => {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data divisi yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/divisis/${id}`);
                Swal.fire('Terhapus!', 'Data divisi berhasil dihapus.', 'success');
                fetchDivisis();
            } catch (error) {
                Swal.fire('Error', 'Gagal menghapus data', 'error');
            }
        }
    });
};
</script>

<template>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Master Data — Divisi
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-lg overflow-hidden">

                    <!-- ── Toolbar ── -->
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <span>Tampilkan</span>
                                <select v-model="perPage" @change="resetPage"
                                    class="border border-gray-300 rounded-md px-2 py-1 w-14 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option :value="10">10</option>
                                    <option :value="20">20</option>
                                    <option :value="50">50</option>
                                    <option :value="100">100</option>
                                </select>
                                <span>entri</span>
                            </div>

                            <input v-model="search" @input="resetPage" type="text" placeholder="Cari kode, nama..."
                                class="border border-gray-300 rounded-md px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <button @click="showAddModal = true"
                            class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Divisi
                        </button>
                    </div>

                    <!-- ── Tabel ── -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider w-10">
                                        #
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider w-20">
                                        Aksi</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">
                                        Kode
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">
                                        Lantai
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">
                                        Logo
                                    </th>

                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">
                                        No Urut
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-if="paginated.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                        Tidak ada data yang ditemukan.
                                    </td>
                                </tr>

                                <tr v-for="(divisi, index) in paginated" :key="divisi.id"
                                    class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-4 text-gray-400">
                                        {{ (currentPage - 1) * perPage + index + 1 }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button @click="openEdit(divisi)"
                                                class="inline-flex items-center justify-center w-7 h-7 bg-blue-400 hover:bg-blue-500 text-white rounded transition"
                                                title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="confirmDelete(divisi.id)"
                                                class="inline-flex items-center justify-center w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded transition"
                                                title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 font-mono text-gray-700">{{ divisi.kode }}</td>
                                    <td class="px-4 py-4 font-medium text-gray-800">{{ divisi.nama }}</td>
                                    <td class="px-4 py-4 text-gray-600">{{ divisi.lantai ?? '-' }}</td>
                                    <td class="px-4 py-4">
                                        <img v-if="divisi.logo" :src="`/storage/${divisi.logo}`" :alt="divisi.nama"
                                            class="h-8 w-auto object-contain" />
                                        <span v-else class="text-gray-400">-</span>
                                    </td>

                                    <td class="px-4 py-4 text-gray-600">{{ divisi.no_urut }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ── Footer tabel ── -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-t border-gray-200 text-sm text-gray-600">
                        <span>
                            Menampilkan
                            {{ filtered.length === 0 ? 0 : (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage *
                            perPage,
                            filtered.length) }}
                            dari {{ filtered.length }} entri
                        </span>

                        <div class="flex items-center gap-1">
                            <button @click="currentPage--" :disabled="currentPage === 1"
                                class="px-3 py-1 rounded border border-gray-300 disabled:opacity-40 hover:bg-gray-100 transition">&lsaquo;</button>

                            <button v-for="p in totalPages" :key="p" @click="currentPage = p" :class="[
                                'px-3 py-1 rounded border transition',
                                currentPage === p
                                    ? 'bg-blue-900 text-white border-blue-900'
                                    : 'border-gray-300 hover:bg-gray-100'
                            ]">{{ p }}</button>

                            <button @click="currentPage++" :disabled="currentPage === totalPages || totalPages === 0"
                                class="px-3 py-1 rounded border border-gray-300 disabled:opacity-40 hover:bg-gray-100 transition">&rsaquo;</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ── Modal Tambah ── -->
        <Teleport to="body">
            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                    @click.self="showAddModal = false">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Tambah Divisi</h3>
                            <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitAdd">
                            <div class="px-6 py-5 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span
                                                class="text-red-500">*</span></label>
                                        <input v-model="addForm.kode" type="text"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Cth: DIV001" />
                                        <p v-if="addForm.errors.kode" class="text-red-500 text-xs mt-1">{{
                                            addForm.errors.kode
                                            }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">No Urut</label>
                                        <input v-model="addForm.no_urut" type="number"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="0" />
                                        <p v-if="addForm.errors.no_urut" class="text-red-500 text-xs mt-1">{{
                                            addForm.errors.no_urut }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span
                                            class="text-red-500">*</span></label>
                                    <input v-model="addForm.nama" type="text"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Nama divisi" />
                                    <p v-if="addForm.errors.nama" class="text-red-500 text-xs mt-1">{{
                                        addForm.errors.nama }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lantai</label>
                                    <input v-model="addForm.lantai" type="number"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Nomor lantai" />
                                    <p v-if="addForm.errors.lantai" class="text-red-500 text-xs mt-1">{{
                                        addForm.errors.lantai
                                        }}</p>
                                </div>



                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                                    <input @change="onLogoChange" type="file" accept="image/*"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    <p v-if="addForm.errors.logo" class="text-red-500 text-xs mt-1">{{
                                        addForm.errors.logo }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200">
                                <button type="button" @click="showAddModal = false"
                                    class="px-4 py-2 text-sm rounded-md border border-gray-300 hover:bg-gray-100 transition">Batal</button>
                                <button type="submit" :disabled="processingAdd"
                                    class="px-4 py-2 text-sm rounded-md bg-blue-900 hover:bg-blue-800 text-white font-medium transition disabled:opacity-50">
                                    {{ processingAdd ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Modal Edit ── -->
        <Teleport to="body">
            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                    @click.self="showEditModal = false">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Edit Divisi</h3>
                            <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitEdit">
                            <div class="px-6 py-5 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span
                                                class="text-red-500">*</span></label>
                                        <input v-model="editForm.kode" type="text"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                        <p v-if="editForm.errors.kode" class="text-red-500 text-xs mt-1">{{
                                            editForm.errors.kode
                                            }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">No Urut</label>
                                        <input v-model="editForm.no_urut" type="number"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                        <p v-if="editForm.errors.no_urut" class="text-red-500 text-xs mt-1">{{
                                            editForm.errors.no_urut }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span
                                            class="text-red-500">*</span></label>
                                    <input v-model="editForm.nama" type="text"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    <p v-if="editForm.errors.nama" class="text-red-500 text-xs mt-1">{{
                                        editForm.errors.nama }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lantai</label>
                                    <input v-model="editForm.lantai" type="number"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    <p v-if="editForm.errors.lantai" class="text-red-500 text-xs mt-1">{{
                                        editForm.errors.lantai
                                        }}</p>
                                </div>



                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                                    <div v-if="editingDivisi?.logo" class="mb-2">
                                        <img :src="`/storage/${editingDivisi.logo}`" class="h-10 w-auto object-contain"
                                            alt="Logo saat ini" />
                                        <p class="text-xs text-gray-400 mt-1">Upload baru untuk mengganti logo</p>
                                    </div>
                                    <input @change="onEditLogoChange" type="file" accept="image/*"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    <p v-if="editForm.errors.logo" class="text-red-500 text-xs mt-1">{{
                                        editForm.errors.logo }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200">
                                <button type="button" @click="showEditModal = false"
                                    class="px-4 py-2 text-sm rounded-md border border-gray-300 hover:bg-gray-100 transition">Batal</button>
                                <button type="submit" :disabled="processingEdit"
                                    class="px-4 py-2 text-sm rounded-md bg-blue-900 hover:bg-blue-800 text-white font-medium transition disabled:opacity-50">
                                    {{ processingEdit ? 'Menyimpan...' : 'Update' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AuthenticatedLayout>
</template>