<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TickDesk - Task Developer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Segoe UI", sans-serif;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 20px;
        }

        .navbar-subtitle {
            font-size: 14px;
            color: #6c757d;
        }

        .navbar-icon {
            font-size: 20px;
            color: #6c757d;
            margin-right: 20px;
        }

        .profile-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .stat-card {
            border-radius: 12px;
            padding: 20px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-title {
            margin: 0;
            font-size: 14px;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }

        .badge-soft {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 12px;
            font-weight: 500;
        }

        .badge-light-green {
            background-color: #d1f4e0;
            color: #198754;
        }

        .badge-light-red {
            background-color: #fddede;
            color: #dc3545;
        }

        .badge-light-blue {
            background-color: #d7e8ff;
            color: #0d6efd;
        }

        .badge-light-yellow {
            background-color: #fff3cd;
            color: #664d03;
        }

        .badge-light-purple {
            background-color: #e6d6fa;
            color: #6f42c1;
        }

        .badge-light-orange {
            background-color: #ffeccd;
            color: #663903;
        }

        .priority-card {
            background-color: #f9fafb;
            border: none;
            border-radius: 10px;
            padding: 16px 24px;
            position: relative;
            margin-bottom: 15px;
        }

        .priority-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 6px;
            height: 100%;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
            background-color: #dc3545;
        }

        .priority-card.border-warning::before {
            background-color: #ffc107;
        }

        .priority-card.border-success::before {
            background-color: #198754;
        }

        .drag-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: grab;
        }

        .ticket-card.dragging {
            opacity: 0.6;
            transform: scale(0.98);
            transition: all 0.2s ease;
        }

        .ticket-card.in-progress {
            position: relative;
            z-index: 10;
            pointer-events: none;
        }

        .ticket-card.in-progress.dragging {
            opacity: 1 !important;
            transform: none !important;
            pointer-events: none;
        }

        @media (max-width: 767px) {
            .w-sm-50 {
                width: 50% !important;
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .stat-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar with Profile Modal -->
    <nav class="navbar navbar-light bg-white shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="navbar-brand mb-0">TickDesk</span>
                <span class="navbar-subtitle">Pengurutan Prioritas Tiket Support</span>
            </div>
            <div class="d-flex align-items-center">
                @php
                    $user = session('user') ?? (Auth::check() ? Auth::user() : null);
                    $profileImg =
                        $user && !empty($user->profile_picture)
                            ? $user->profile_picture
                            : asset('assetsadmin/img/avatars/default.png');
                @endphp
                <img src="{{ $profileImg }}" alt="Profile" class="profile-img" id="profileImg"
                    style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#profileModal" />
            </div>
        </div>
    </nav>

    <!-- Modal Profile -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"
            style="max-width: 768px; padding-left: 32px; padding-right: 32px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">Profil Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $profileImg }}" alt="user-avatar" class="rounded-circle object-fit-cover"
                            style="width:80px;height:80px;">
                        <div>
                            <div class="fw-bold fs-5">{{ $user->name ?? '-' }}</div>
                            <div class="text-muted">{{ $user->email ?? '-' }}</div>
                            <div class="text-muted small">Username: {{ $user->username ?? '-' }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2"><strong>No HP:</strong> {{ $user->phone_number ?? '-' }}</div>
                        @php
                            $timezone = session('user_timezone', config('app.timezone'));
                            $cTime = \Carbon\Carbon::parse($user->created_at)->timezone($timezone)->format('d-m-Y H:i');
                        @endphp
                        <div class="mb-2"><strong>Dibuat:</strong> {{ isset($user->created_at) ? $cTime : '-' }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnLogoutProfile">Logout</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5 mb-5">
        <div class="row text-white mb-4 g-3">
            <div class="col-md-3 stat-md-6">
                <div class="stat-card bg-primary shadow-sm">
                    <i class="bi bi-ticket-perforated fs-4"></i>
                    <div>
                        <p class="stat-title">Total Tickets</p>
                        <p class="stat-number">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stat-md-6">
                <div class="stat-card bg-danger shadow-sm">
                    <i class="bi bi-exclamation-circle fs-4"></i>
                    <div>
                        <p class="stat-title">Open</p>
                        <p class="stat-number">{{ $stats['open'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stat-md-6">
                <div class="stat-card bg-warning text-dark shadow-sm">
                    <i class="bi bi-clock fs-4"></i>
                    <div>
                        <p class="stat-title">In Progress</p>
                        <p class="stat-number">{{ $stats['in_progress'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stat-md-6">
                <div class="stat-card bg-success shadow-sm">
                    <i class="bi bi-check-circle fs-4"></i>
                    <div>
                        <p class="stat-title">Resolved</p>
                        <p class="stat-number">{{ $stats['resolved'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row align-items-center justify-content-between mb-4">
            <div class="col-md-3 mb-2 w-sm-50">
                <select class="form-select" id="roleFilter">
                    <option value="All Roles">Semua Role</option>
                    @foreach ($roles as $indRole)
                        <option value="{{ $indRole->id }}">{{ $indRole->roleName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2 w-sm-50">
                <select class="form-select" id="statusFilter">
                    <option value="All Status">Semua Status</option>
                    <option value="Open">Open</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Resolved">Resolved</option>
                </select>
            </div>
            <div class="col-md-6 text-end">
                <form id="saveForm" action="{{ route('SPV.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ticketIds" id="ticketIdsInput">

                    <button type="button" onclick="saveData()" class="btn btn-primary" id="saveBtn">
                        <i class="bi bi-save me-1"></i> Simpan Urutan
                    </button>
                </form>
            </div>
        </div>
        <!-- Removed duplicated <form id="saveForm"> to prevent invalid nested forms that broke other forms (like logout) -->

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Urutan Prioritas Tiket Support</h5>
                    <small class="text-muted">Seretkan tiket yang Open untuk mengurutkan prioritas</small>
                </div>

                <div id="ticketContainer">
                    @foreach ($tickets as $ticket)
                        @php
                            $roleColors = ['orange', 'blue', 'green', 'purple'];

                            $roleColor = $roleColors[$ticket['roleID'] % 4];
                            $statusColor = match ($ticket['status']) {
                                'Open' => 'red',
                                'In Progress' => 'yellow',
                                'Resolved' => 'green',
                                default => 'light',
                            };
                            $borderClass = match ($ticket['status']) {
                                'Open' => 'border-danger',
                                'In Progress' => 'border-warning',
                                'Resolved' => 'border-success',
                                default => '',
                            };
                        @endphp

                        <div class="priority-card {{ $borderClass }} ticket-card {{ $ticket['status'] === 'In Progress' ? 'in-progress' : '' }}"
                            data-role="{{ $ticket['roleID'] }}" data-status="{{ $ticket['status'] }}"
                            ticket-id="{{ $ticket['id'] }}" @if ($ticket['status'] === 'Open') draggable="true" @endif>
                            <div class="drag-icon text-muted"><i class="bi bi-grip-vertical fs-5"></i></div>
                            <div>
                                <div class="mb-1">
                                    <strong>{{ $ticket['code'] }}</strong>
                                    <span
                                        class="badge badge-soft badge-light-{{ $roleColor }}">{{ $ticket['role'] }}</span>
                                    <span
                                        class="badge badge-soft badge-light-{{ $statusColor }}">{{ $ticket['status'] }}</span>
                                </div>
                                <div class="fw-semibold">{{ $ticket['title'] }}</div>
                                <div class="text-muted small">User: {{ $ticket['user'] }} • Dibuat:
                                    {{ \Carbon\Carbon::parse($ticket['submitted_at'])->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Debug Auth --}}
    @if (Auth::check())
        {{-- User is logged in --}}
    @else
        <div style="color:red">User tidak login (Auth::check() = false)</div>
    @endif

    <!-- Modal Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda ingin logout?
                </div>
                <div class="modal-footer">
                    <form id="logoutFormSpv" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" onclick="duplicationPrevention(event, 'logoutFormSpv')"
                            class="btn btn-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById("ticketContainer");
            const roleFilter = document.getElementById("roleFilter");
            const statusFilter = document.getElementById("statusFilter");

            roleFilter.addEventListener("change", filterTickets);
            statusFilter.addEventListener("change", filterTickets);

            function filterTickets() {
                const role = roleFilter.value.toLowerCase();
                const status = statusFilter.value.toLowerCase();

                const tickets = container.querySelectorAll(".ticket-card");
                tickets.forEach(ticket => {
                    const ticketRole = ticket.dataset.role.toLowerCase();
                    const ticketStatus = ticket.dataset.status.toLowerCase();
                    const show = (role === "all roles" || role === ticketRole) &&
                        (status === "all status" || status === ticketStatus);
                    ticket.style.display = show ? "block" : "none";
                });
            }

            let draggedItem = null;
            // Drag start hanya untuk tiket Open
            container.addEventListener("dragstart", function(e) {
                const card = e.target.closest(".ticket-card");
                if (card && card.dataset.status === "Open") {
                    draggedItem = card;
                    const ghost = document.createElement("div");
                    ghost.style.position = "absolute";
                    ghost.style.top = "-9999px";
                    document.body.appendChild(ghost);
                    e.dataTransfer.setDragImage(ghost, 0, 0);
                    card.classList.add("dragging");
                } else {
                    e.preventDefault();
                }
            });

            container.addEventListener("dragend", function() {
                if (draggedItem) {
                    draggedItem.classList.remove("dragging");
                    draggedItem = null;
                }
            });

            // Highlight target Open saat dragover
            container.addEventListener("dragover", function(e) {
                e.preventDefault();
                const target = e.target.closest('.ticket-card');
                if (!draggedItem || !target || target === draggedItem) return;
                if (target.dataset.status !== 'Open') return; // hanya open yang dapat menjadi target
            });

            // Insert (bukan swap): geser tiket Open lain (-1) saat drop
            container.addEventListener('drop', function(e) {
                e.preventDefault();
                const target = e.target.closest('.ticket-card');
                if (!draggedItem || !target || target === draggedItem) return;
                if (draggedItem.dataset.status !== 'Open' || target.dataset.status !== 'Open') return;

                const parent = container;
                const rect = target.getBoundingClientRect();
                const isBefore = e.clientY < (rect.top + rect.height / 2);

                // Jika sudah tepat posisinya, jangan lakukan apa-apa
                if (isBefore) {
                    // Insert sebelum target
                    if (target.previousSibling !== draggedItem) {
                        parent.insertBefore(draggedItem, target);
                    }
                } else {
                    // Insert setelah target
                    const after = target.nextSibling;
                    if (after !== draggedItem) {
                        parent.insertBefore(draggedItem, after);
                    }
                }
            });

            // Ganti: Profile click for logout
            // document.getElementById("profileImg").addEventListener("click", function () {
            //   var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            //   logoutModal.show();
            // });

            // Tambahkan: tombol logout di modal profile
            document.getElementById("btnLogoutProfile").addEventListener("click", function() {
                var profileModal = bootstrap.Modal.getInstance(document.getElementById('profileModal'));
                profileModal.hide();
                setTimeout(function() {
                    var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
                    logoutModal.show();
                }, 400); // beri jeda agar animasi modal tidak bertabrakan
            });
        });

        function duplicationPrevention(event,
            id) { //prevents double submission and submitting on pages you already submitted.
            event.preventDefault();
            const form = document.getElementById(id);
            if (!form.dataset.submitted) {
                form.dataset.submitted = 'true';
                form.requestSubmit();
            }
        }

        function saveData(ev) {
            if (ev) ev.preventDefault();
            const ticketContainer = document.getElementById("ticketContainer");
            const ticketIds = Array.from(ticketContainer.querySelectorAll(".priority-card"))
                .map(card => card.getAttribute("ticket-id"))
                .join(",");
            document.getElementById("ticketIdsInput").value = ticketIds;
            const form = document.getElementById("saveForm");
            form.removeAttribute('data-submitted'); // izinkan multi save setelah drag
            if (!form.dataset.submitted) {
                form.dataset.submitted = 'true';
                form.requestSubmit();
            }
        }
    </script>
    @include('layout.session')
</body>

</html>
