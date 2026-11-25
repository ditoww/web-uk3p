<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Support\Setting;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home', [
            'judul'  => Setting::get('home_title', 'Selamat Datang di UKKKP UNESA'),
            'intro'  => Setting::get('home_intro', ''),
            'pilar'  => Content::section('beranda_pilar')->tampil()->get(),
            'jadwal' => Content::section('beranda_jadwal')->tampil()->get(),
        ]);
    }

    public function profil()
    {
        return view('public.profil', [
            'tentang'  => Setting::get('profil_tentang', ''),
            'sejarah'  => Setting::get('profil_sejarah', ''),
            'visi'     => Setting::get('profil_visi', ''),
            'misi'     => Setting::get('profil_misi', ''),
            'pengurus' => Content::section('profil_pengurus')->tampil()->get(),
        ]);
    }

    public function kegiatan()
    {
        return view('public.kegiatan', [
            'kegiatan' => Content::section('kegiatan')->tampil()->get(),
        ]);
    }

    public function kontak()
    {
        return view('public.kontak', [
            'alamat'    => Setting::get('kontak_alamat', ''),
            'kontak'    => Content::section('kontak_item')->tampil()->get(),
        ]);
    }
}