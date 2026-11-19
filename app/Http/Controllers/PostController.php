<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! in_array(auth()->user()->privLevel, ['admin', 'super_admin'])) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $posts = Post::orderByDesc('created_at')->get();
        return view('kelola.berita.index', compact('posts'));
    }

    public function create()
    {
        return view('kelola.berita.form', ['post' => new Post()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['author_name'] = auth()->user()->name ?: auth()->user()->username;
        $data['cover_image'] = $this->saveCover($request);
        $data['published_at'] = $data['is_published'] ? now() : null;
        Post::create($data);

        return redirect()->route('kelola.berita.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(Post $post)
    {
        return view('kelola.berita.form', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validated($request);

        if ($data['title'] !== $post->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }
        if ($request->hasFile('cover')) {
            $this->deleteCover($post->cover_image);
            $data['cover_image'] = $this->saveCover($request);
        }
        if ($data['is_published'] && ! $post->published_at) {
            $data['published_at'] = now();
        }
        if (! $data['is_published']) {
            $data['published_at'] = null;
        }

        $post->update($data);

        return redirect()->route('kelola.berita.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(Post $post)
    {
        $this->deleteCover($post->cover_image);
        $post->delete();

        return redirect()->route('kelola.berita.index')->with('success', 'Berita berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'body'  => 'required|string|max:20000',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'title.required' => 'Judul wajib diisi.',
            'title.max'      => 'Judul maksimal 150 karakter.',
            'body.required'  => 'Isi berita wajib diisi.',
            'body.max'       => 'Isi berita terlalu panjang.',
            'cover.image'    => 'Sampul harus berupa gambar.',
            'cover.mimes'    => 'Sampul harus berformat JPG, PNG, atau WEBP.',
            'cover.max'      => 'Ukuran sampul maksimal 2 MB.',
        ]);

        return [
            'title'        => trim($request->input('title')),
            'body'         => $request->input('body'),
            'is_published' => $request->boolean('is_published'),
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'berita';
        $slug = $base;
        $i = 2;
        while (Post::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function saveCover(Request $request): ?string
    {
        if (! $request->hasFile('cover')) {
            return null;
        }
        $file = $request->file('cover');
        $dir = public_path('uploads/berita');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = date('Ymd_His') . '_' . Str::random(8) . '.' . $file->guessExtension();
        $file->move($dir, $name);
        return 'uploads/berita/' . $name;
    }

    private function deleteCover(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/berita/')) {
            $full = public_path($path);
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
}