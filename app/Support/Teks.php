<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Teks
{
    private const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    private const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    /**
     * Teks biasa menjadi HTML aman: karakter di-escape, alamat http/https
     * menjadi tautan yang bisa diklik, dan baris baru dipertahankan.
     */
    public static function tautan(?string $teks): string
    {
        $aman = e((string) $teks);

        $aman = preg_replace_callback(
            '~https?://(?:(?!&quot;|&#039;|&lt;|&gt;)[^\s<])+~i',
            function (array $m): string {
                $url = $m[0];
                $ekor = '';
                while ($url !== '' && preg_match('/[.,;:!?)\]]$/', $url)) {
                    $ekor = substr($url, -1) . $ekor;
                    $url = substr($url, 0, -1);
                }
                if ($url === '') {
                    return $m[0];
                }
                return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer nofollow">' . $url . '</a>' . $ekor;
            },
            $aman
        ) ?? $aman;

        return nl2br($aman, false);
    }

    public static function hari(CarbonInterface $d): string
    {
        return self::HARI[$d->dayOfWeek];
    }

    public static function bulan(CarbonInterface $d, bool $pendek = false): string
    {
        $nama = self::BULAN[$d->month - 1];
        return $pendek ? mb_substr($nama, 0, 3) : $nama;
    }

    public static function tanggal(CarbonInterface $d): string
    {
        return self::hari($d) . ', ' . $d->day . ' ' . self::bulan($d) . ' ' . $d->year;
    }
}