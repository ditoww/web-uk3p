<?php

return [

    // Ikon yang boleh dipilih admin (nama kelas Tabler Icons)
    'icons' => [
        'ti-book' => 'Buku',
        'ti-heart' => 'Hati',
        'ti-users' => 'Kelompok',
        'ti-calendar-event' => 'Kalender',
        'ti-gift' => 'Hadiah',
        'ti-school' => 'Pendidikan',
        'ti-flag' => 'Bendera',
        'ti-star' => 'Bintang',
        'ti-music' => 'Musik',
        'ti-home' => 'Rumah',
        'ti-world' => 'Dunia',
        'ti-bell' => 'Lonceng',
        'ti-phone' => 'Telepon',
        'ti-mail' => 'Email',
        'ti-map-pin' => 'Lokasi',
        'ti-brand-instagram' => 'Instagram',
        'ti-brand-whatsapp' => 'WhatsApp',
        'ti-brand-youtube' => 'YouTube',
        'ti-brand-facebook' => 'Facebook',
    ],

    'pages' => [
        'beranda' => [
            'label' => 'Beranda',
            'route' => 'home',
            'settings' => [
                'home_title' => ['label' => 'Judul Utama', 'type' => 'text', 'max' => 150],
                'home_intro' => ['label' => 'Teks Pengantar', 'type' => 'textarea', 'max' => 1000],
            ],
            'sections' => ['beranda_pilar', 'beranda_jadwal'],
        ],
        'profil' => [
            'label' => 'Profil',
            'route' => 'profil',
            'settings' => [
                'profil_tentang' => ['label' => 'Tentang UKKKP UNESA', 'type' => 'textarea', 'max' => 3000],
                'profil_sejarah' => ['label' => 'Sejarah', 'type' => 'textarea', 'max' => 5000],
                'profil_visi' => ['label' => 'Visi', 'type' => 'textarea', 'max' => 2000],
                'profil_misi' => ['label' => 'Misi', 'type' => 'textarea', 'max' => 3000],
            ],
            'sections' => ['profil_pengurus'],
        ],
        'kegiatan' => [
            'label' => 'Kegiatan',
            'route' => 'kegiatan',
            'settings' => [],
            'sections' => ['kegiatan'],
        ],
        'kontak' => [
            'label' => 'Kontak',
            'route' => 'kontak',
            'settings' => [
                'kontak_alamat' => ['label' => 'Alamat Sekretariat', 'type' => 'textarea', 'max' => 1000],
                'kontak_bergabung' => ['label' => 'Cara Bergabung', 'type' => 'textarea', 'max' => 3000],
            ],
            'sections' => ['kontak_item'],
        ],
    ],

    'sections' => [
        'beranda_pilar' => [
            'page' => 'beranda', 'label' => 'Pilar Pelayanan', 'singular' => 'Pilar',
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'required' => true, 'max' => 100],
                'body' => ['label' => 'Deskripsi', 'type' => 'textarea', 'max' => 400],
                'icon' => ['label' => 'Ikon', 'type' => 'icon'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'help' => 'Angka kecil tampil lebih dulu.'],
            ],
        ],
        'beranda_jadwal' => [
            'page' => 'beranda', 'label' => 'Jadwal Ibadah dan Persekutuan', 'singular' => 'Jadwal',
            'fields' => [
                'title' => ['label' => 'Nama Ibadah/Kegiatan', 'type' => 'text', 'required' => true, 'max' => 100],
                'event_date' => ['label' => 'Tanggal', 'type' => 'date', 'help' => 'Kosongkan jika jadwal rutin tanpa tanggal tertentu.'],
                'event_time' => ['label' => 'Jam', 'type' => 'text', 'max' => 60, 'help' => 'Contoh: 16.30 WIB - selesai'],
                'location' => ['label' => 'Nama Lokasi', 'type' => 'text', 'max' => 150, 'help' => 'Contoh: UKMC Lantai 4'],
                'link' => ['label' => 'Link Google Maps (opsional)', 'type' => 'url', 'schemes' => 'http://,https://', 'help' => 'Di Google Maps tekan Bagikan, salin tautannya, lalu tempel di sini.'],
                'body' => ['label' => 'Keterangan / Detail', 'type' => 'textarea', 'max' => 5000, 'rows' => 10, 'help' => 'Boleh panjang, beberapa paragraf, dan emoji. Baris baru dipertahankan.'],
                'image' => ['label' => 'Foto / Poster (opsional)', 'type' => 'image', 'maxkb' => 5120, 'help' => 'JPG, PNG, atau WEBP, maksimal 5 MB.'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'help' => 'Angka kecil tampil lebih dulu.'],
            ],
        ],
        'kegiatan' => [
            'page' => 'kegiatan', 'label' => 'Daftar Kegiatan', 'singular' => 'Kegiatan',
            'fields' => [
                'title' => ['label' => 'Nama Kegiatan', 'type' => 'text', 'required' => true, 'max' => 120],
                'subtitle' => ['label' => 'Jadwal', 'type' => 'text', 'max' => 120],
                'body' => ['label' => 'Deskripsi', 'type' => 'textarea', 'max' => 2000],
                'icon' => ['label' => 'Ikon', 'type' => 'icon'],
                'image' => ['label' => 'Foto (opsional)', 'type' => 'image', 'help' => 'JPG, PNG, atau WEBP, maksimal 2 MB.'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'help' => 'Angka kecil tampil lebih dulu.'],
            ],
        ],
        'profil_pengurus' => [
            'page' => 'profil', 'label' => 'Struktur Pengurus', 'singular' => 'Pengurus',
            'fields' => [
                'title' => ['label' => 'Nama', 'type' => 'text', 'required' => true, 'max' => 100],
                'subtitle' => ['label' => 'Jabatan', 'type' => 'text', 'max' => 100],
                'body' => ['label' => 'Periode / Keterangan', 'type' => 'textarea', 'max' => 300],
                'image' => ['label' => 'Foto (opsional)', 'type' => 'image', 'help' => 'Tampilkan foto hanya dengan izin yang bersangkutan.'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'help' => 'Angka kecil tampil lebih dulu.'],
            ],
        ],
        'kontak_item' => [
            'page' => 'kontak', 'label' => 'Kontak dan Media Sosial', 'singular' => 'Kontak',
            'fields' => [
                'title' => ['label' => 'Nama (mis. Instagram)', 'type' => 'text', 'required' => true, 'max' => 60],
                'subtitle' => ['label' => 'Isi (mis. @ukkkp_unesa)', 'type' => 'text', 'max' => 120],
                'link' => ['label' => 'Tautan (opsional)', 'type' => 'url', 'help' => 'Harus diawali https://, http://, mailto: atau tel:'],
                'icon' => ['label' => 'Ikon', 'type' => 'icon'],
                'sort_order' => ['label' => 'Urutan', 'type' => 'number', 'help' => 'Angka kecil tampil lebih dulu.'],
            ],
        ],
    ],
];