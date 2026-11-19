<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PublicController extends Controller
{
    public function home()
    {
        $posts = Post::where('is_published', true)->orderByDesc('published_at')->take(3)->get();
        return view('public.home', compact('posts'));
    }

    public function profil()   { return view('public.profil'); }
    public function kegiatan() { return view('public.kegiatan'); }
    public function kontak()   { return view('public.kontak'); }

    public function berita()
    {
        $posts = Post::where('is_published', true)->orderByDesc('published_at')->limit(50)->get();
        return view('public.berita', compact('posts'));
    }

    public function beritaShow(string $slug)
    {
        $post = Post::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return view('public.berita-show', compact('post'));
    }
}