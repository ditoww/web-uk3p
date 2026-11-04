<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\DevTicket;
use App\Models\DevRole;

class SPVTicketController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data tiket nyata dari database.
        // Status yang ingin ditampilkan.
        $wantedStatuses = ['Open', 'In Progress', 'Resolved'];

        // Gunakan query builder agar ringan & fleksibel.
        // Kolom isDeleted mungkin ada; jika tidak ada kita abaikan (fallback otomatis).
        $query = \DB::table('devTickets')
            ->leftJoin('devRoles', 'devTickets.roleID', '=', 'devRoles.id')
            ->leftJoin('users as support', 'devTickets.supportID', '=', 'support.id')
            ->select(
                'devTickets.id',
                'devTickets.title',
                'devTickets.status',
                'devTickets.created_at',
                'devTickets.priority',
                'devTickets.roleID',
                'devRoles.roleName',
                'support.name as support_name'
            )
            ->whereIn('devTickets.status', $wantedStatuses)
            ->orderBy('devTickets.priority', 'asc')
            ->orderBy('devTickets.created_at', 'desc');

        // Tambahkan filter isDeleted jika kolom tersedia.
        try {
            if (\Schema::hasColumn('devTickets', 'isDeleted')) {
                $query->where('devTickets.isDeleted', 0);
            }
        } catch (\Throwable $e) {
            // Jika schema helper gagal (misal cache), lanjut tanpa filter ini.
        }

        $rawTickets = $query->get();

        // Inisialisasi priority yang null (sekali saja) mengikuti urutan created_at desc saat ini
        $needsInit = $rawTickets->whereNull('priority');
        if ($needsInit->count() > 0) {
            // Ambil semua id sesuai created_at desc (sudah diurutkan sebelumnya jika priority null semua)
            $allIds = \DB::table('devTickets')
                ->whereIn('status', $wantedStatuses)
                ->orderByDesc('created_at')
                ->pluck('id');
            $pos = 1;
            foreach ($allIds as $id) {
                \DB::table('devTickets')->where('id', $id)->update(['priority' => $pos]);
                $pos++;
            }
            // reload setelah init
            $rawTickets = $query->get();
        }

        // Hitung statistik berdasarkan data yang sudah diambil.
        $stats = [
            'total' => $rawTickets->count(),
            'open' => $rawTickets->where('status', 'Open')->count(), // status di DB mungkin lowercase 'Open'
            'in_progress' => $rawTickets->where('status', 'In Progress')->count(),
            'resolved' => $rawTickets->where('status', 'Resolved')->count(),
        ];

        $roles = DevRole::where('isDeleted', 0)->get();

        // Mapping ke struktur sebelumnya yang dipakai view (code, role, status, title, user, submitted_at)
        $tickets = $rawTickets->map(function ($t) {
            $timezone = session('user_timezone', config('app.timezone'));
            $cTime = Carbon::parse($t->created_at)->timezone($timezone);
            return [
                'code' => 'TK' . '-' . Carbon::parse($t->created_at)->format('Y') . '-' .str_pad($t->id, 3, '0', STR_PAD_LEFT),
                'roleID' => $t->roleID ?? '-',
                'role' => $t->roleName ?? '-',
                // Normalisasi penulisan status untuk tampilan.
                'status' => $t->status,
                'title' => $t->title,
                'id' => $t->id,
                'user' => $t->support_name ?? '-',
                'submitted_at' => $cTime,
                'priority' => $t->priority,
            ];
        });

        return view('SPV.spv', [
            'tickets' => $tickets, // tampilkan semua: Open, In Progress, Resolved
            'stats' => $stats,
            'roles' => $roles
        ]);
    }

    public function saveData(Request $request) {
        $ticketIds = (string) $request->input('ticketIds', '');
        // Sanitasi & normalisasi daftar id
        $ids = array_values(array_filter(array_map(function ($v) {
            $v = trim($v);
            return ctype_digit($v) ? (int)$v : null;
        }, explode(',', $ticketIds)), function ($v) { return !is_null($v); }));

        if (count($ids) === 0) {
            return back()->with('error', 'Tidak ada tiket untuk disimpan.');
        }

        // Ambil status & priority saat ini dari DB untuk id-id tersebut
        $rows = DevTicket::whereIn('id', $ids)
            ->get(['id', 'status', 'priority'])
            ->keyBy('id');

        // Pastikan seluruh tiket memiliki priority terinisialisasi
        // (Hal ini seharusnya sudah ditangani pada index(), tapi kita buat aman.)
        $hasNull = $rows->contains(function ($r) { return is_null($r->priority); });
        if ($hasNull) {
            // Inisialisasi sederhana mengikuti urutan yang dikirim UI (stabil)
            $pos = 1;
            foreach ($ids as $id) {
                DevTicket::where('id', $id)->update(['priority' => $pos]);
                if (isset($rows[$id])) { $rows[$id]->priority = $pos; }
                $pos++;
            }
        }

        // 1) Tentukan posisi yang DI-RESERVE untuk tiket non-Open (anchor)
        //    Mereka tidak boleh berubah posisi sehingga kita pertahankan angka priority-nya.
        $reserved = []; // priority => id (untuk status selain 'Open')
        foreach ($ids as $id) {
            $row = $rows->get($id);
            if (!$row) { continue; }
            $priority = (int) $row->priority;
            if (strtolower($row->status) !== 'open') {
                if ($priority > 0) {
                    $reserved[$priority] = $id;
                }
            }
        }

        // 2) Siapkan slot posisi target 1..N dan keluarkan slot yang sudah di-reserve
        $N = count($ids);
        $available = [];
        for ($p = 1; $p <= $N; $p++) {
            if (!array_key_exists($p, $reserved)) {
                $available[] = $p;
            }
        }

        // 3) Ambil urutan tiket Open berdasarkan urutan yang dikirim UI
        $openIdsInOrder = [];
        foreach ($ids as $id) {
            $row = $rows->get($id);
            if ($row && strtolower($row->status) === 'open') {
                $openIdsInOrder[] = $id;
            }
        }

        // 4) Tetapkan priority baru untuk tiket Open sesuai slot available (berurutan)
        $updates = [];
        foreach ($openIdsInOrder as $i => $id) {
            $newPriority = $available[$i] ?? null;
            if ($newPriority === null) { break; }
            $currentPriority = (int) ($rows->get($id)->priority);
            if ($currentPriority !== $newPriority) {
                $updates[$id] = $newPriority;
            }
        }

        // 5) Simpan perubahan hanya untuk tiket Open yang berubah
        \DB::transaction(function () use ($updates): void {
            foreach ($updates as $id => $priority) {
                DevTicket::where('id', $id)->update(['priority' => $priority]);
            }
        });

        return back()->with('success', 'Queue order saved (Open tickets re-ordered, non-Open anchored).');
    }
}
