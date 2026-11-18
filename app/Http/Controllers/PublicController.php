<?php

namespace App\Http\Controllers;

class PublicController extends Controller
{
    public function home()     { return view('public.home'); }
    public function profil()   { return view('public.profil'); }
    public function kegiatan() { return view('public.kegiatan'); }
    public function kontak()   { return view('public.kontak'); }
}