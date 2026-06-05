<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Divisi;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Divisi
        $divisiData = [
            ['kode' => 'ERTI', 'nama' => 'Engineering, Riset, dan Teknologi Informasi', 'lantai' => 10, 'no_urut' => 0],
            ['kode' => 'QHSSE', 'nama' => 'Quality, Health, Safety, and Environment', 'lantai' => 5, 'no_urut' => 0],
            ['kode' => 'INFRA I', 'nama' => 'Infrastruktur I', 'lantai' => null, 'no_urut' => 0],
            ['kode' => 'INFRA II', 'nama' => 'Infrastruktur II', 'lantai' => null, 'no_urut' => 0],
            ['kode' => 'PJT', 'nama' => 'Pembangunan Jalan Tol', 'lantai' => 10, 'no_urut' => 0],
            ['kode' => 'OPT', 'nama' => 'Operasi dan Pemeliharaan Jalan Tol', 'lantai' => null, 'no_urut' => 0],
            ['kode' => 'LEGAL', 'nama' => 'Legal', 'lantai' => 8, 'no_urut' => 0],
            ['kode' => 'SEKPER', 'nama' => 'Sekertaris Perkantoran', 'lantai' => 6, 'no_urut' => 0],
            ['kode' => 'SPI', 'nama' => 'Satuan Pengawasan Intern', 'lantai' => null, 'no_urut' => 0],
            ['kode' => 'SCM', 'nama' => 'Supply Chain Management', 'lantai' => 8, 'no_urut' => 0],
            ['kode' => 'HK', 'nama' => 'Hutama Karya', 'lantai' => null, 'no_urut' => 0],
            ['kode' => 'HC', 'nama' => 'Human Capital', 'lantai' => 8, 'no_urut' => 2],
            ['kode' => 'AKKU', 'nama' => 'Akuntansi & Keuangan', 'lantai' => 7, 'no_urut' => 3],
        ];

        $divisis = [];
        foreach ($divisiData as $data) {
            $divisis[$data['kode']] = Divisi::updateOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }

        // 2. Buat Permission (Juga sebagai Report Dashboard)
        $permissionData = [
            ['name' => 'view-report-hr', 'divisi_kode' => 'HC', 'judul_report' => 'Laporan Kinerja Karyawan'],
            ['name' => 'veiw-dashboard-spi', 'divisi_kode' => 'SPI', 'judul_report' => 'Dashboard SPI'],
            ['name' => 'view-dashboard-sekkper', 'divisi_kode' => 'SEKPER', 'judul_report' => 'Dashboard SEKPER'],
            ['name' => 'view-dashboard-qhsse', 'divisi_kode' => 'QHSSE', 'judul_report' => 'Dashboard QHSSE'],
            ['name' => 'view-dashboard-infra-i', 'divisi_kode' => 'INFRA I', 'judul_report' => 'Dashboard INFRA I'],
            ['name' => 'view-dashboard-scm', 'divisi_kode' => 'SCM', 'judul_report' => 'Dashboard SCM'],
            ['name' => 'view-dashboard-infra-ii', 'divisi_kode' => 'INFRA II', 'judul_report' => 'Dashboard INFRA II'],
            ['name' => 'view-dashboard-pjt', 'divisi_kode' => 'PJT', 'judul_report' => 'Dashboard PJT'],
            ['name' => 'view-dashboard-opt', 'divisi_kode' => 'OPT', 'judul_report' => 'Dashboard OPT'],
            ['name' => 'view-dashboard-legal', 'divisi_kode' => 'LEGAL', 'judul_report' => 'Dashboard Legal'],
            ['name' => 'view-report-keberhasilan', 'divisi_kode' => 'ERTI', 'judul_report' => 'Keberhasilan TI'],
        ];

        $permissions = [];
        foreach ($permissionData as $data) {
            $divisiId = isset($divisis[$data['divisi_kode']]) ? $divisis[$data['divisi_kode']]->id : null;
            $permissions[$data['name']] = Permission::updateOrCreate(
                ['name' => $data['name']],
                [
                    'guard_name' => 'web',
                    'divisi_id' => $divisiId,
                    'judul_report' => $data['judul_report'],
                    'deskripsi' => null,
                    'link_dashboard' => 'https://example.com',
                ]
            );
        }

        // 3. Buat Role & Assign Permissions
        $roleData = [
            'EVP_ERTI' => ['view-report-keberhasilan'],
            'EVP_HC' => ['view-report-hr'],
            'EVP_INFA1' => ['view-dashboard-infra-i'],
            'EVP_INFRA2' => ['view-dashboard-infra-ii'],
            'EVP_LEGAL' => ['view-dashboard-legal'],
            'EVP_OPT' => ['view-dashboard-opt'],
            'EVP_QHSSE' => ['view-dashboard-qhsse'],
            'EVP_SCM' => ['view-dashboard-scm'],
            'EVP_SEKPER' => ['view-dashboard-sekkper'],
            'EVP_SPI' => ['veiw-dashboard-spi'],
            'manager' => [],
            'super_admin' => ['view-report-hr', 'view-report-keberhasilan']
        ];

        foreach ($roleData as $roleName => $perms) {
            $role = Role::updateOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
            $permissionModels = [];
            foreach ($perms as $permName) {
                if (isset($permissions[$permName])) {
                    $permissionModels[] = $permissions[$permName];
                }
            }
            $role->syncPermissions($permissionModels);
        }

        // 4. Buat Akun Users
        $userData = [
            ['name' => 'Ahmad Zulfikar Ananta Mahardika', 'email' => 'dika@gmail.com', 'divisi_kode' => 'ERTI', 'role' => 'EVP_ERTI'],
            ['name' => 'Dian Faradis', 'email' => 'dian@gmail.com', 'divisi_kode' => 'INFRA I', 'role' => 'EVP_INFA1'],
            ['name' => 'Efandy Prawira', 'email' => 'effandy@gmail.com', 'divisi_kode' => 'SCM', 'role' => 'EVP_SCM'],
            ['name' => 'Ayudya Aldi Setiyawan', 'email' => 'aldi@gmail.com', 'divisi_kode' => 'HC', 'role' => 'EVP_HC'],
            ['name' => 'Muhammad Bagus Satrio Aji', 'email' => 'satrio@gmail.com', 'divisi_kode' => 'HK', 'role' => 'super_admin'],
        ];

        foreach ($userData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'divisi_id' => isset($divisis[$data['divisi_kode']]) ? $divisis[$data['divisi_kode']]->id : null,
                ]
            );

            $user->syncRoles([$data['role']]);
        }

        $this->command->info('Seeding Master Data Berhasil! Data sesuai dengan referensi terbaru.');
    }
}
