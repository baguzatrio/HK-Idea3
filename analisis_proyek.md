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

### Frontend (SPA Client)
- **Framework**: Vue 3 (menggunakan Composition API dan `<script setup>`).
- **Build Tool**: Vite 8 (`vite: ^8.0.0`) dengan `laravel-vite-plugin`.
- **Routing**: Vue Router 4 (`vue-router`). Berbeda dengan setup Laravel Breeze bawaan yang biasa menggunakan Inertia.js, proyek ini murni menggunakan Vue Router (`resources/js/router.ts`) untuk perpindahan halaman (*client-side routing*). Inertia.js tidak digunakan meskipun ada dependensinya di `package.json`.
- **Styling**: Tailwind CSS 3/4 dengan utilitas standar dan komponen kustom.
- **State Management**: Modul state reaktif sederhana di Vue (seperti `resources/js/store/auth.ts`) untuk menyimpan data *user* yang sedang login.

---

## 2. Struktur Direktori Kunci

- `routes/api.php`: Pusat semua *endpoint* backend. Berisi *routing* untuk `UserController`, `DivisiController`, `RoleController`, dan `ViewDashboardController` (menggantikan `PermissionController` yang telah dihapus). Semua rute API dilindungi oleh *middleware* `auth:sanctum`. Terdapat juga grup rute dengan *middleware* `role:super_admin` khusus untuk manajemen Master Data.
- `resources/js/app.ts`: Titik awal aplikasi *frontend* di mana instance Vue dibuat dan dihubungkan dengan Vue Router.
- `resources/js/router.ts`: Mendefinisikan seluruh rute halaman SPA.
- `resources/js/Pages/`: Komponen Vue yang bertindak sebagai "halaman" (Page).
  - `Auth/`: Halaman otentikasi (Login, Register, Reset Password, dll).
  - `MasterData/`: Halaman manajemen CRUD untuk entitas master. Di dalamnya terdapat subfolder `User/`, `Role/`, `Divisi/`, dan `View/` (menggantikan `Permission/` yang telah dihapus).
  - `Dashboard.vue`: Halaman portal utama.
- `resources/js/Layouts/`: Pembungkus halaman (*wrapper*).
  - `GuestLayout.vue`: Digunakan untuk halaman publik/otentikasi (dilengkapi dengan *background slideshow/image full-width* dan *overlay*).
  - `AuthenticatedLayout.vue`: Digunakan untuk halaman yang membutuhkan sesi login. Terdapat navigasi *navbar* dan *dropdown* profil.

---

## 3. Alur Fungsional Utama (Core Features)

### 3.1. Authentication & Authorization
Proses otentikasi menggunakan endpoint bawaan Breeze API (seperti `/login`, `/logout`). Di *frontend*, permintaan pertama kali akan memanggil `/sanctum/csrf-cookie` lalu mengirim data *login*. Data *user* yang *login* kemudian diakses melalui fungsi fetch `authState` di `store/auth.ts`.
Akses fitur dibagi berdasarkan **Spatie Roles & Permissions**. Role tertinggi adalah `super_admin`, yang secara otomatis mem-*bypass* semua batasan izin (didefinisikan di Laravel Service Provider atau pengecekan *role* manual).

### 3.2. Portal Dashboard (`Dashboard.vue`)
Ini adalah halaman utama setelah pengguna berhasil masuk. Fungsionalitas utamanya meliputi:
1. **Slideshow Sambutan**: Di bagian teratas (di-*render* menempel pinggir *full-width*), terdapat *slideshow* otomatis yang berganti gambar (durasi tiap 15 detik), lengkap dengan ucapan "Selamat datang, [Nama User]".
2. **Grid Divisi (Hutama Karya)**: Menampilkan daftar divisi yang ada di perusahaan.
3. **Sistem Report berbasis Iframe**: Ketika sebuah "Divisi" di-klik, daftar **View/Dashboard Link** yang diperbolehkan untuk pengguna tersebut akan muncul. Memilih salah satu *report* akan memicu munculnya *iframe* (`link_dashboard`) yang menampilkan dashboard/laporan eksternal. Aplikasi akan otomatis melakukan *scroll* ke bagian *iframe* tersebut (tanpa ID target, menggunakan `window.scrollTo` relatif).

### 3.3. Master Data Management (Khusus Super Admin)
Pengelolaan data inti disediakan bagi pengguna dengan *role* `super_admin`. Entitas yang dikelola meliputi:
1. **Users**: Manajemen daftar pengguna, penugasan divisi pengguna, pengaturan kredensial, dan pemberian peran (Roles).
2. **Divisi**: Manajemen unit organisasi yang menjadi induk pelaporan (memiliki *kode*, *nama*, *logo*, *no_urut*).
3. **Roles**: Pembuatan peran dan penugasan izin/akses View spesifik pada peran tersebut.
4. **View (Dashboard Links)**: Entitas ini menggantikan konsep *Permission* standar. Di tingkat database, data ini disimpan di tabel `views` (via model `ViewDashboard`). View digunakan untuk merepresentasikan halaman dashboard eksternal yang di-embed ke dalam iframe. Properti khusus pada entitas View: `name` (berisi nama unik untuk hak akses/permission), `divisi_id` (relasi ke divisi), `judul_report`, `deskripsi`, dan `link_dashboard`.

---

## 4. Pola Pengembangan (Development Patterns)

- **UI/UX**: Menggunakan tampilan modern berbasis *card*, form di dalam modal (saat mengedit data master), dan interaksi *smooth transition* khas Vue. Tombol dan *input* difokuskan pada *usability* dengan desain bersih dari Tailwind.
- **API Responses**: Backend mengirimkan struktur JSON. Khusus untuk struktur *endpoint* dashboard (`/api/dashboard`), *response* sudah digabungkan secara *eager-loading* (Divisi beserta *Permissions*-nya) dan langsung di-filter secara manual menggunakan koleksi *helper* PHP berdasarkan peran `super_admin` atau spesifik `hasPermissionTo()`.
- **Form Component**: Penggunaan komponen *custom* standar untuk *input*, *error message*, *dropdown*, dan `SearchableSelect` agar entri data seragam dan validasinya konsisten secara *real-time*.
