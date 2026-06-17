# Analisis Proyek: HK-IDEA

File ini dibuat untuk memberikan pemahaman komprehensif terkait struktur, teknologi, dan alur bisnis utama di dalam proyek **HK-IDEA** ini. Dokumentasi ini dapat digunakan sebagai referensi bagi *developer* atau *AI model* lain yang akan berkolaborasi atau melanjutkan pengembangan proyek ini.

---

## 1. Stack Teknologi Utama

Proyek ini dibangun menggunakan arsitektur **Single Page Application (SPA)** yang terpisah antara *frontend* dan *backend*, meskipun masih berada di dalam satu *codebase* repositori (monorepo style).

### Backend (API Provider)
- **Framework**: Laravel 13 (`laravel/framework: ^13.0`)
- **Bahasa**: PHP ^8.5
- **Authentication**: Laravel Sanctum (`laravel/sanctum: ^4.0`). Menggunakan pendekatan SPA Auth (berbasis *cookie/session* menggunakan `/sanctum/csrf-cookie`).
- **Authorization & RBAC**: Spatie Laravel Permission (`spatie/laravel-permission: ^7.3`).
  - *Kustomisasi*: Tabel default `permissions` di-rename menjadi `views` di database. Model permission bawaan Spatie di-override menggunakan kelas model kustom `App\Models\ViewDashboard`.
- **Routing**: Didefinisikan sepenuhnya di `routes/api.php` sebagai *endpoint* JSON. File `routes/web.php` hanya bertugas me-routing semua permintaan (*catch-all*) ke file `app.blade.php` untuk memuat *frontend* Vue.
- **Storage**: Menggunakan fitur bawaan *local public disk* Laravel (memerlukan `php artisan storage:link`) untuk menyimpan unggahan seperti file logo Divisi di `storage/app/public/divisi/logo/`.

### Frontend (SPA Client)
- **Framework**: Vue 3 (menggunakan Composition API dan `<script setup>`).
- **Build Tool**: Vite 8 (`vite: ^8.0.0`) dengan `laravel-vite-plugin`.
- **Routing**: Vue Router 4 (`vue-router`). Proyek ini murni menggunakan Vue Router (`resources/js/router.ts`) untuk perpindahan halaman (*client-side routing*). Router juga dikonfigurasi dengan *ScrollBehavior* agar otomatis kembali ke `top: 0` (atas layar) setiap kali terjadi perpindahan navigasi.
- **Styling**: Tailwind CSS 3/4 dengan utilitas standar dan komponen kustom.
- **State Management**: Modul state reaktif sederhana di Vue (seperti `resources/js/store/auth.ts`) untuk menyimpan data *user* yang sedang login.
- **Notifikasi**: SweetAlert2 (`sweetalert2`) digunakan untuk semua notifikasi sukses dan pop-up konfirmasi hapus data secara seragam.

---

## 2. Skema Database (Data Structure)

Sistem ini memiliki beberapa tabel utama dan tabel pivot (perantara) untuk mendukung operasional aplikasi dan manajemen hak akses (RBAC Spatie).

### Tabel Utama:
1. **`users`**
   Menyimpan data otentikasi dan profil pengguna.
   - `id` (PK), `name`, `email` (Unique), `password`, `remember_token`.
   - `divisi_id` (FK): Relasi ke tabel `divisi` (opsional/nullable). Jika divisi dihapus, *field* ini akan otomatis diset ke `null` (`nullOnDelete`).

2. **`divisi`**
   Menyimpan entitas divisi sebagai unit/kategori pengelompokan *report*.
   - `id` (PK), `kode` (String 50, Unique), `nama`, `lantai`, `logo` (Path gambar), `url`, `no_urut` (untuk urutan tampilan di *dashboard*).

3. **`views`** (Tabel `permissions` bawaan Spatie yang dikustomisasi)
   Menyimpan entitas laporan/dashboard eksternal sebagai wujud dari sebuah izin akses (*permission*).
   - `id` (PK), `name` (Identifier unik untuk Spatie), `guard_name` (default `web`).
   - `divisi_id` (FK ke `divisi`, `nullOnDelete`).
   - Atribut kustom tambahan: `judul_report`, `deskripsi`, `link_dashboard` (URL Iframe).

4. **`roles`** (Tabel bawaan Spatie)
   Menyimpan data kelompok peran pengguna (seperti `super_admin`).
   - `id` (PK), `name` (Unique), `guard_name`.

### Tabel Pivot (Relasi):
Aplikasi ini memanfaatkan tabel perantara bawaan *Spatie Permission*:
- **`model_has_roles`**: Menghubungkan *user* dengan *role* yang dimilikinya.
- **`role_has_permissions`**: Menghubungkan *role* dengan kumpulan hak akses (*views/permissions*) yang diperbolehkan untuk peran tersebut.
- **`model_has_permissions`**: Menghubungkan *user* langsung dengan *views/permissions* tanpa perantara *role* (tersedia di *database*, tetapi aplikasi saat ini difokuskan pada manajemen berbasis *role*).

---

## 3. Struktur Direktori Kunci

- `routes/api.php`: Pusat semua *endpoint* backend. Berisi *routing* untuk `UserController`, `DivisiController`, `RoleController`, dan `ViewDashboardController`. Semua rute API dilindungi oleh *middleware* `auth:sanctum`. Terdapat juga grup rute dengan *middleware* `role:super_admin` khusus untuk manajemen Master Data.
- `resources/js/app.ts`: Titik awal aplikasi *frontend* di mana instance Vue dibuat dan dihubungkan dengan Vue Router.
- `resources/js/router.ts`: Mendefinisikan seluruh rute halaman SPA dengan *scroll behavior*.
- `resources/js/Pages/`: Komponen Vue yang bertindak sebagai "halaman" (Page).
  - `Auth/`: Halaman otentikasi (Login, Register, Reset Password, dll).
  - `MasterData/`: Halaman manajemen CRUD untuk entitas master. Di dalamnya terdapat subfolder `User/`, `Role/`, `Divisi/`, dan `View/`.
  - `Dashboard.vue`: Halaman portal utama.
- `resources/js/Layouts/`: Pembungkus halaman (*wrapper*).
  - `GuestLayout.vue`: Digunakan untuk halaman publik/otentikasi (dilengkapi dengan *background slideshow/image full-width* dan *overlay*).
  - `AuthenticatedLayout.vue`: Digunakan untuk halaman yang membutuhkan sesi login. Terdapat navigasi *navbar* dan *dropdown* profil. File layout ini dipastikan aman dari *blank-page-bug* dengan memisahkan div dekorasi (*pointer-events-none*) dengan div konten.
- `resources/js/Components/`: Kumpulan komponen UI reaktif yang digunakan berulang kali. (Contoh: `SearchableSelect.vue`, `AppFooter.vue`, `Dropdown.vue`).

---

## 4. Alur Fungsional Utama (Core Features)

### 4.1. Authentication & Authorization
Proses otentikasi menggunakan endpoint bawaan Breeze API (seperti `/login`, `/logout`). Di *frontend*, permintaan pertama kali akan memanggil `/sanctum/csrf-cookie` lalu mengirim data *login*. Data *user* yang *login* kemudian diakses melalui fungsi fetch `authState` di `store/auth.ts`.
Akses fitur dibagi berdasarkan **Spatie Roles & Permissions**. Role tertinggi adalah `super_admin`, yang secara otomatis mem-*bypass* semua batasan izin (didefinisikan di Laravel Service Provider atau pengecekan *role* manual).

### 4.2. Portal Dashboard (`Dashboard.vue`)
Ini adalah halaman utama setelah pengguna berhasil masuk. Fungsionalitas utamanya meliputi:
1. **Slideshow Sambutan**: Di bagian teratas (di-*render* menempel pinggir *full-width*), terdapat *slideshow* otomatis yang berganti gambar (durasi tiap 15 detik), lengkap dengan ucapan "Selamat datang, [Nama User]".
2. **Grid Divisi (Hutama Karya)**: Menampilkan daftar divisi yang ada di perusahaan.
3. **Sistem Report berbasis Iframe**: Ketika sebuah "Divisi" di-klik, daftar **View/Dashboard Link** yang diperbolehkan untuk pengguna tersebut akan muncul. Memilih salah satu *report* akan memicu munculnya *iframe* (`link_dashboard`) yang menampilkan dashboard/laporan eksternal. Aplikasi akan otomatis melakukan *scroll* ke bagian *iframe* tersebut.

### 4.3. Master Data Management (Khusus Super Admin)
Pengelolaan data inti disediakan bagi pengguna dengan *role* `super_admin`. Entitas yang dikelola meliputi:
1. **Users**: Manajemen daftar pengguna. Memiliki fitur **Konfirmasi Password** untuk mencegah salah ketik pada modal tambah dan edit, serta menggunakan **SearchableSelect (Combobox)** saat menetapkan penugasan Divisi dan Roles.
2. **Divisi**: Manajemen unit organisasi. Meliputi *kode*, *nama*, *logo* (diunggah dan disimpan ke public disk Laravel), dan *no_urut*.
3. **Roles**: Pembuatan peran dan penugasan izin/akses View spesifik pada peran tersebut.
4. **View (Dashboard Links)**: Merepresentasikan halaman dashboard eksternal yang di-embed ke dalam iframe. Properti khusus pada entitas View: `name` (berisi nama unik untuk hak akses/permission), `divisi_id` (menggunakan `SearchableSelect`), `judul_report`, `deskripsi`, dan `link_dashboard`.

---

## 5. Pola Pengembangan (Development Patterns)

- **UI/UX & Aesthetics**: 
  - Menggunakan tampilan modern berbasis *card*, form di dalam modal. Tombol dan *input* difokuskan pada *usability* dengan desain bersih dari Tailwind.
  - Interaksi *hover dropdown* dibuat mulus tanpa *blink* (menggunakan *padding margin trick* di komponen Dropdown) agar kursor tidak kehilangan fokus saat beralih.
  - Komponen `AppFooter` menggunakan *watermark big text* "HUTAMA KARYA" dengan *flexbox layout* responsif dan *kerning (tracking)* normal agar terhindar dari *overlapping* huruf.
- **Pagination & Scroll Navigation**: Setiap tombol Paginasi halaman pada tabel Master Data dilengkapi logika *watch* state Vue yang otomatis memicu `window.scrollTo` ke area paling atas dengan transisi *smooth*.
- **API Responses**: Backend mengirimkan struktur JSON. Khusus untuk struktur *endpoint* dashboard (`/api/dashboard`), *response* sudah digabungkan secara *eager-loading* (Divisi beserta *Permissions*-nya) dan langsung di-filter secara manual menggunakan koleksi *helper* PHP berdasarkan peran `super_admin` atau spesifik `hasPermissionTo()`.
- **Form Component & Feedback**: 
  - Menggunakan komponen *custom* `SearchableSelect` untuk mengonversi `<select>` bawaan menjadi pencarian teks *(combobox/dropdown)* secara *client-side*.
  - Validasi *real-time* ganda: *Backend* menangkap *rule* validasi (termasuk *password confirmation*) lalu merespons dengan JSON error. *Frontend* (Vue) menaruh pesan error tersebut tepat di bawah setiap kotak input.
  - Semua pesan peringatan bawaan *browser* (*alert, prompt*) telah diganti secara menyeluruh menggunakan `SweetAlert2` (*Swal.fire*).
