<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $t) {
            $t->string('key', 100)->primary();
            $t->text('value')->nullable();
            $t->timestamp('updated_at')->nullable();
        });

        Schema::create('contents', function (Blueprint $t) {
            $t->id();
            $t->string('section', 40)->index();
            $t->string('title', 150);
            $t->string('subtitle', 150)->nullable();
            $t->text('body')->nullable();
            $t->string('icon', 40)->nullable();
            $t->string('link', 255)->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_published')->default(true);
            $t->timestamps();
        });

        $now = now();

        DB::table('site_settings')->insert([
            ['key' => 'home_title', 'value' => 'Selamat Datang di UKKKP UNESA', 'updated_at' => $now],
            ['key' => 'home_intro', 'value' => 'Unit Kegiatan Kerohanian Kristen Protestan Universitas Negeri Surabaya: wadah pembinaan iman, pelayanan, dan kebersamaan bagi mahasiswa Kristen Protestan.', 'updated_at' => $now],
            ['key' => 'profil_tentang', 'value' => 'Unit Kegiatan Kerohanian Kristen Protestan (UKKKP) UNESA adalah Unit Kegiatan Mahasiswa di Universitas Negeri Surabaya yang menjadi wadah pembinaan iman, pelayanan, dan kebersamaan bagi mahasiswa Kristen Protestan.', 'updated_at' => $now],
        ]);

        $row = fn ($section, $title, $body, $icon, $order) => [
            'section' => $section, 'title' => $title, 'subtitle' => null, 'body' => $body,
            'icon' => $icon, 'link' => null, 'image' => null, 'sort_order' => $order,
            'is_published' => true, 'created_at' => $now, 'updated_at' => $now,
        ];

        DB::table('contents')->insert([
            $row('beranda_pilar', 'Pembinaan Iman', 'Pertumbuhan rohani lewat ibadah, pendalaman Alkitab, dan persekutuan.', 'ti-book', 1),
            $row('beranda_pilar', 'Pelayanan', 'Melayani sesama di kampus dan masyarakat.', 'ti-heart', 2),
            $row('beranda_pilar', 'Kebersamaan', 'Keluarga rohani lintas fakultas dan angkatan.', 'ti-users', 3),
            $row('kegiatan', 'Ibadah Rutin', null, 'ti-calendar-event', 1),
            $row('kegiatan', 'Persekutuan Kelompok Kecil', null, 'ti-users', 2),
            $row('kegiatan', 'Pelayanan dan Bakti Sosial', null, 'ti-heart', 3),
            $row('kegiatan', 'Perayaan Hari Besar Kristiani', null, 'ti-gift', 4),
            $row('kegiatan', 'Pelatihan dan Retret', null, 'ti-school', 5),
            $row('kegiatan', 'Penyambutan Mahasiswa Baru', null, 'ti-flag', 6),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
        Schema::dropIfExists('site_settings');
    }
};