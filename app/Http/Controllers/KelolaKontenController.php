<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Support\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KelolaKontenController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->check() || ! in_array(auth()->user()->privLevel, ['admin', 'super_admin'])) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function halaman(string $page)
    {
        $cfg = $this->cfgPage($page);

        $sections = [];
        foreach ($cfg['sections'] as $s) {
            $sections[$s] = [
                'cfg' => config("konten.sections.$s"),
                'items' => Content::section($s)->orderBy('sort_order')->orderBy('id')->get(),
            ];
        }

        $values = [];
        foreach (array_keys($cfg['settings']) as $k) {
            $values[$k] = Setting::get($k, '');
        }

        return view('kelola.konten.halaman', compact('page', 'cfg', 'sections', 'values'));
    }

    public function simpanTeks(Request $request, string $page)
    {
        $cfg = $this->cfgPage($page);

        $rules = [];
        $names = [];
        foreach ($cfg['settings'] as $k => $f) {
            $rules[$k] = ['nullable', 'string', 'max:' . ($f['max'] ?? 2000)];
            $names[$k] = $f['label'];
        }
        $data = $request->validate($rules, ['max' => ':attribute terlalu panjang (maksimal :max karakter).'], $names);

        foreach (array_keys($cfg['settings']) as $k) {
            Setting::put($k, isset($data[$k]) ? trim((string) $data[$k]) : null);
        }

        return redirect()->route('kelola.konten.halaman', $page)->with('success', 'Teks halaman berhasil disimpan.');
    }

    public function tambah(string $section)
    {
        $cfg = $this->cfgSection($section);
        $item = new Content(['section' => $section, 'is_published' => true, 'sort_order' => 0]);
        return view('kelola.konten.form', compact('item', 'section', 'cfg'));
    }

    public function simpan(Request $request, string $section)
    {
        $cfg = $this->cfgSection($section);
        $data = $this->validasi($request, $cfg);
        $data['section'] = $section;
        $data['image'] = isset($cfg['fields']['image']) ? $this->simpanGambar($request) : null;
        Content::create($data);

        return redirect()->route('kelola.konten.halaman', $cfg['page'])
            ->with('success', $cfg['singular'] . ' berhasil ditambahkan.');
    }

    public function ubah(Content $content)
    {
        $section = $content->section;
        $cfg = $this->cfgSection($section);
        $item = $content;
        return view('kelola.konten.form', compact('item', 'section', 'cfg'));
    }

    public function perbarui(Request $request, Content $content)
    {
        $cfg = $this->cfgSection($content->section);
        $data = $this->validasi($request, $cfg);

        if (isset($cfg['fields']['image']) && $request->hasFile('image')) {
            $this->hapusGambar($content->image);
            $data['image'] = $this->simpanGambar($request);
        }

        $content->update($data);

        return redirect()->route('kelola.konten.halaman', $cfg['page'])
            ->with('success', $cfg['singular'] . ' berhasil diperbarui.');
    }

    public function hapus(Content $content)
    {
        $cfg = $this->cfgSection($content->section);
        $this->hapusGambar($content->image);
        $content->delete();

        return redirect()->route('kelola.konten.halaman', $cfg['page'])
            ->with('success', $cfg['singular'] . ' berhasil dihapus.');
    }

    // ---------- helper ----------

    private function cfgPage(string $page): array
    {
        $c = config("konten.pages.$page");
        abort_unless($c, 404);
        return $c;
    }

    private function cfgSection(string $section): array
    {
        $c = config("konten.sections.$section");
        abort_unless($c, 404);
        return $c;
    }

    private function validasi(Request $request, array $cfg): array
    {
        $rules = [];
        $names = [];
        $ikon = implode(',', array_keys(config('konten.icons')));

        foreach ($cfg['fields'] as $name => $f) {
            $req = ! empty($f['required']) ? 'required' : 'nullable';
            switch ($f['type']) {
                case 'textarea':
                    $rules[$name] = [$req, 'string', 'max:' . ($f['max'] ?? 2000)];
                    break;
                case 'icon':
                    $rules[$name] = ['nullable', 'in:' . $ikon];
                    break;
                case 'url':
                    $rules[$name] = ['nullable', 'string', 'max:255', 'starts_with:' . ($f['schemes'] ?? 'http://,https://,mailto:,tel:')];
                    break;
                case 'date':
                    $rules[$name] = ['nullable', 'date'];
                    break;
                case 'image':
                    $rules[$name] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . ($f['maxkb'] ?? 2048)];
                    break;
                case 'number':
                    $rules[$name] = ['nullable', 'integer', 'min:0', 'max:9999'];
                    break;
                default:
                    $rules[$name] = [$req, 'string', 'max:' . ($f['max'] ?? 150)];
            }
            $names[$name] = $f['label'];
        }

        $messages = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute melebihi batas maksimal (:max).',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
            'in' => ':attribute tidak valid.',
            'integer' => ':attribute harus berupa angka.',
            'starts_with' => ':attribute harus diawali https://, http://, mailto: atau tel:',
        ];

        $validated = $request->validate($rules, $messages, $names);

        $data = [];
        foreach ($cfg['fields'] as $name => $f) {
            if ($f['type'] === 'image') {
                continue;
            }
            if ($f['type'] === 'number') {
                $data[$name] = (int) ($validated[$name] ?? 0);
            } else {
                $data[$name] = isset($validated[$name]) ? trim((string) $validated[$name]) : null;
            }
        }
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }

    private function simpanGambar(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }
        $file = $request->file('image');
        $dir = public_path('uploads/konten');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = date('Ymd_His') . '_' . Str::random(8) . '.' . $file->guessExtension();
        $file->move($dir, $name);
        return 'uploads/konten/' . $name;
    }

    private function hapusGambar(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/konten/')) {
            $full = public_path($path);
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
}