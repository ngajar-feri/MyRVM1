# Rencana Implementasi UI/UX Halaman List Assignment

**Versi Dokumen:** 1.0  
**Tanggal Revisi:** Minggu-12 Januari 2026 - 01:30 AM  
**Tujuan:** Implementasi UI/UX Halaman List Assignment dengan fitur Hoverable Rows, Detail Collapse, Map Integration, dan Avatar Group.  
**Status:** Draft / Ready for Implementation

---

## 1. UI/UX Design Prompt

**Konteks:**
Halaman ini digunakan oleh Admin/Manager untuk memantau tugas instalasi atau maintenance RVM (Reverse Vending Machine).

**Layout & Visual:**
-   Menggunakan layout **Table** di dalam **Card**.
-   **Header Tabel** (`h5` style) yang bersih.
-   **Hoverable Rows:** Baris tabel berubah warna saat di-hover untuk fokus visual.
-   **Interactive Collapse:** Baris utama berfungsi sebagai "Accordion". Saat diklik, baris tersebut akan *expand* untuk menampilkan detail tugas di bawahnya tanpa memuat ulang halaman.

**Elemen Kunci:**
1.  **Status Indikator:** Badge warna (misal: "Pending", "In Progress", "Completed") pada baris utama.
2.  **Location Marker:** Ikon peta (Marker) pada kolom lokasi.
    -   *Interaksi:* Hover/Klik memunculkan Toast/Tooltip "Klik untuk Melihat Lokasi".
    -   *Action:* Klik ikon akan membuka Google Maps di tab baru sesuai koordinat.
3.  **Team Assignment:** Menggunakan **Avatar Group** (`div.avatar-group`).
    -   Menampilkan foto profil teknisi yang bertugas.
    -   Jika lebih dari 3 orang, gunakan indikator count (misal: `+3`) dengan tooltip yang melist nama sisa tim.

---

## 2. Implementasi Teknis

Berikut adalah kode lengkap yang siap diimplementasikan ke dalam file Blade Laravel Anda (misal: `resources/views/assignments/index.blade.php`).

### A. Mock Data (Controller Simulation)
Masukkan ini di Controller Anda (`AssignmentController.php`) untuk simulasi data sebelum integrasi database.

```php
// app/Http/Controllers/AssignmentController.php

public function index()
{
    $assignments = [
        [
            'id' => 101,
            'rvm_name' => 'RVM-Jogja-01',
            'task_title' => 'Instalasi Baru Unit Alpha',
            'status' => 'In Progress',
            'status_class' => 'warning',
            'location_name' => 'Malioboro Mall, Yogyakarta',
            'lat' => -7.7926,
            'lng' => 110.3658,
            'notes' => 'Pastikan koneksi listrik stabil sebelum mounting unit. Cek grounding.',
            'due_date' => '2026-01-15',
            'team' => [
                ['name' => 'Budi Santoso', 'avatar' => 'assets/img/avatars/5.png'],
                ['name' => 'Siti Aminah', 'avatar' => 'assets/img/avatars/6.png'],
                ['name' => 'Agus Setiawan', 'avatar' => 'assets/img/avatars/7.png'],
                ['name' => 'Dewi Lestari', 'avatar' => 'assets/img/avatars/1.png'], // +1
            ]
        ],
        [
            'id' => 102,
            'rvm_name' => 'RVM-Sleman-03',
            'task_title' => 'Maintenance Sensor Optik',
            'status' => 'Pending',
            'status_class' => 'secondary',
            'location_name' => 'Sleman City Hall',
            'lat' => -7.7212,
            'lng' => 110.3637,
            'notes' => 'Sensor sering error saat mendeteksi botol bening. Bawa sparepart tipe X200.',
            'due_date' => '2026-01-16',
            'team' => [
                ['name' => 'Rina Wati', 'avatar' => 'assets/img/avatars/12.png'],
                ['name' => 'Joko Anwar', 'avatar' => 'assets/img/avatars/10.png'],
            ]
        ]
    ];

    return view('assignments.index', compact('assignments'));
}
```

### B. View (Blade Template)
Code ini menggunakan Bootstrap 5 dan syntax Blade.

```html
{{-- resources/views/assignments/index.blade.php --}}

@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Daftar Penugasan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Management /</span> Penugasan</h4>

    <!-- Card Table -->
    <div class="card">
        <h5 class="card-header">Daftar Penugasan Instalasi & Maintenance</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID Tugas</th>
                        <th>Judul Tugas</th>
                        <th>Tim</th>
                        <th>Status</th>
                        <th>Lokasi</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach($assignments as $task)
                    {{-- Baris Utama (Clickable) --}}
                    <tr class="cursor-pointer" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $task['id'] }}" aria-expanded="false" aria-controls="collapse-{{ $task['id'] }}">
                        <td><strong>#{{ $task['id'] }}</strong></td>
                        <td>
                            <span class="fw-bold">{{ $task['task_title'] }}</span><br>
                            <small class="text-muted">{{ $task['rvm_name'] }}</small>
                        </td>
                        <td>
                            {{-- Avatar Group --}}
                            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                                @foreach(array_slice($task['team'], 0, 3) as $member)
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{ $member['name'] }}" class="avatar avatar-xs pull-up">
                                    <img src="{{ asset($member['avatar']) }}" alt="Avatar" class="rounded-circle">
                                </li>
                                @endforeach

                                {{-- Counter jika lebih dari 3 --}}
                                @if(count($task['team']) > 3)
                                <li class="avatar avatar-xs">
                                    <span class="avatar-initial rounded-circle pull-up bg-secondary text-white" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ count($task['team']) - 3 }} more">+{{ count($task['team']) - 3 }}</span>
                                </li>
                                @endif
                            </ul>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $task['status_class'] }} me-1">{{ $task['status'] }}</span>
                        </td>
                        <td>
                            {{-- Location Marker --}}
                            <button type="button" 
                                    class="btn btn-icon btn-outline-primary btn-sm btn-location" 
                                    data-lat="{{ $task['lat'] }}" 
                                    data-lng="{{ $task['lng'] }}"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    title="Lihat Lokasi: {{ $task['location_name'] }}">
                                <i class="bx bx-map"></i>
                            </button>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" onclick="event.stopPropagation();">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-1"></i> Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Baris Detail (Collapse) --}}
                    <tr>
                        <td colspan="6" class="p-0">
                            <div id="collapse-{{ $task['id'] }}" class="accordion-collapse collapse bg-lighter">
                                <div class="p-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="mb-2">Detail Lokasi & RVM:</h6>
                                            <p class="mb-1"><strong>RVM:</strong> {{ $task['rvm_name'] }}</p>
                                            <p class="mb-1"><strong>Alamat:</strong> {{ $task['location_name'] }}</p>
                                            <p class="mb-0 text-muted"><i class="bx bx-time"></i> Due Date: {{ $task['due_date'] }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-2">Catatan Teknis:</h6>
                                            <div class="alert alert-warning mb-0" role="alert">
                                                <i class="bx bx-note me-1"></i> {{ $task['notes'] }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-end">
                                         <button class="btn btn-sm btn-primary">Update Progress</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!--/ Card Table -->
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="locationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <i class="bx bx-map rounded me-2 text-primary"></i>
          <strong class="me-auto">Navigasi</strong>
          <small>Now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          Membuka Google Maps...
        </div>
      </div>
    </div>

</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Handle Location Button Click
    const locationBtns = document.querySelectorAll('.btn-location');
    const toastEl = document.getElementById('locationToast');
    const toast = new bootstrap.Toast(toastEl);

    locationBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); // Mencegah row collapse saat klik tombol map
            
            const lat = this.getAttribute('data-lat');
            const lng = this.getAttribute('data-lng');
            
            // Show Toast
            toast.show();
            
            // Open Maps after short delay
            setTimeout(() => {
                window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`, '_blank');
            }, 500);
        });
    });
});
</script>
@endsection
```

### C. Custom CSS (Optional)
Tambahkan di file CSS utama atau section style jika belum ada di template.

```css
/* Styling untuk Avatar Pull-Up effect */
.avatar-group .avatar {
    transition: all 0.2s ease;
}
.avatar-group .avatar:hover {
    z-index: 10;
    transform: translateY(-4px); /* Efek naik saat hover */
}

/* Cursor pointer untuk row yang bisa diklik */
tr[data-bs-toggle="collapse"] {
    cursor: pointer;
}

/* Background detail row */
.bg-lighter {
    background-color: #f9f9f9;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
}
```

---

## 3. Changelog & Rollback Plan

**Changelog:**
-   [x] Menambahkan mock data assignment di Controller.
-   [x] Membuat struktur tabel HTML dengan Bootstrap 5.
-   [x] Implementasi Collapse Row untuk detail.
-   [x] Implementasi Avatar Group dengan counter.
-   [x] Integrasi tombol Maps dengan event `stopPropagation`.

**Rollback Plan:**
-   Jika terjadi error JS, hapus bagian `<script>` dan kembalikan ke tabel standar tanpa collapse.
-   Jika layout berantakan, pastikan library Bootstrap 5 dan Icon (Boxicons) sudah ter-load dengan benar di layout utama.
