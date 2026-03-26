<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Schedule;

layout('layouts.blank');

state(['schedule' => null, 'company' => null, 'passengers' => [], 'cargos' => []]);

mount(function (Schedule $schedule) {
    // Otorisasi sederhana: cegah agen yang tidak terkait melihat Laporan
    $user = auth()->user();
    if (!$user->canViewAll() && $schedule->route->origin_agent_id !== $user->agent_id && $schedule->route->destination_agent_id !== $user->agent_id) {
        abort(403, 'Akses Ditolak');
    }

    $this->schedule = $schedule->loadMissing(['route.originAgent', 'route.destinationAgent', 'bus', 'driver']);
    $this->company = \App\Models\Company::first();
    
    $this->passengers = \App\Models\Passenger::whereHas('booking', function ($q) use ($schedule) {
        $q->where('schedule_id', $schedule->id);
    })->with('booking')->get();

    $this->cargos = \App\Models\Cargo::whereHas('booking', function ($q) use ($schedule) {
        $q->where('schedule_id', $schedule->id);
    })->with(['booking', 'originAgent', 'destinationAgent'])->get();
});
?>

<div>
    <style>
        /* A4 Print Styles */
        @page { size: A4 portrait; margin: 10mm; }
        body { margin: 0; padding: 0; background: #e5e7eb; color: #111827; font-family: sans-serif; }
        .sheet {
            background: white; width: 210mm; min-height: 297mm; margin: 10mm auto; padding: 15mm;
            box-shadow: 0 .5mm 2mm rgba(0,0,0,.3); box-sizing: border-box;
        }
        @media print {
            body { background: white; }
            .sheet { margin: 0; box-shadow: none; padding: 0; width: 100%; min-height: 100%; }
            .no-print { display: none !important; }
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-center { text-align: center; }
        .header-title { font-size: 24px; font-weight: 900; text-transform: uppercase; margin-bottom: 4px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { border: 1px solid #d1d5db; padding: 10px; border-radius: 4px; font-size: 13px; line-height: 1.5; }
        .signature-box { margin-top: 50px; display: flex; justify-content: space-between; text-align: center; font-size: 13px; }
        .signature-line { margin-top: 60px; border-top: 1px solid #000; width: 150px; display: inline-block; padding-top: 5px; font-weight: bold; }
    </style>

    {{-- Actions --}}
    <div class="no-print" style="text-align: center; margin: 20px 0;">
        <button onclick="window.print()" style="background: #059669; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            🖨️ Cetak Laporan Perjalanan (A4)
        </button>
        <br><br>
        <a href="{{ route('schedules.show', $schedule) }}" style="color: #4b5563; text-decoration: underline; font-family: sans-serif; font-size: 14px;">&larr; Tutup dan Kembali</a>
    </div>

    {{-- Manifesto Layout --}}
    <div class="sheet">
        <div class="text-center" style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
            <div class="header-title">{{ $company->name ?? 'AGEN TRAVEL & KARGO' }}</div>
            <div style="font-size: 16px; font-weight: bold; letter-spacing: 1px;">LAPORAN PERJALANAN (MANIFESTO)</div>
            <div style="font-size: 12px; color: #444; margin-top: 4px;">Dicetak pada: {{ now()->format('d/m/Y H:i') }} WIB</div>
        </div>

        <div class="info-grid text-sm">
            <div class="info-box">
                <strong>RUTE PERJALANAN</strong><br>
                Asal: {{ $schedule->route->originAgent->city }} ({{ $schedule->route->originAgent->name }})<br>
                Tujuan: {{ $schedule->route->destinationAgent->city }} ({{ $schedule->route->destinationAgent->name }})<br>
                Berangkat: <strong>{{ $schedule->departure_date->format('d/m/Y') }} {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</strong>
            </div>
            <div class="info-box">
                <strong>ARMADA & SUPIR</strong><br>
                Bus/Mobil: <strong>{{ $schedule->bus->name ?? '-' }}</strong> (Plat: {{ $schedule->bus->plate_number ?? '-' }})<br>
                Supir: <strong>{{ $schedule->driver->name ?? 'Belum Ditentukan' }}</strong><br>
                Status Perjalanan: {{ strtoupper($schedule->status) }}
            </div>
        </div>

        <h3 style="margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">A. Daftar Penumpang ({{ $passengers->count() }} Orang)</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 10%">Kursi</th>
                    <th style="width: 25%">Nama Penumpang</th>
                    <th style="width: 15%">No. HP</th>
                    <th style="width: 15%">Kode Booking</th>
                    <th style="width: 15%">Status Bayar</th>
                    <th style="width: 15%">Ket / Hadir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passengers as $index => $passenger)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><strong>{{ $passenger->seat_number ?? '-' }}</strong></td>
                    <td>{{ $passenger->name }}</td>
                    <td>{{ $passenger->phone ?? '-' }}</td>
                    <td><strong>{{ $passenger->booking->booking_code }}</strong></td>
                    <td>
                        @if($passenger->booking->payment_status === 'paid')
                            <span style="color: #059669; font-weight: bold;">LUNAS</span>
                        @else
                            <span style="color: #DC2626; font-weight: bold;">BLM LUNAS</span>
                        @endif
                    </td>
                    <td></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data penumpang untuk jadwal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <h3 style="margin-top: 30px; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">B. Daftar Kargo / Barang ({{ $cargos->count() }} Paket)</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 15%">No. Resi</th>
                    <th style="width: 20%">Detail Barang</th>
                    <th style="width: 25%">Pengirim &rarr; Penerima</th>
                    <th style="width: 15%">Tujuan Agen</th>
                    <th style="width: 5%">Bobot</th>
                    <th style="width: 15%">Status Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cargos as $index => $cargo)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $cargo->tracking_code }}</strong></td>
                    <td>{{ $cargo->description ?? 'Paket Kargo' }}<br><small style="color: #666;">{{ $cargo->quantity }} Koli</small></td>
                    <td>{{ $cargo->booking->booker_name }}<br>&rarr; {{ $cargo->recipient_name }}</td>
                    <td>{{ $cargo->destinationAgent->city ?? '-' }}</td>
                    <td>{{ $cargo->weight_kg }} kg</td>
                    <td>
                        @if($cargo->is_paid)
                            <span style="color: #059669; font-weight: bold;">LUNAS</span><br>
                            <small>Rp{{ number_format($cargo->fee, 0, ',', '.') }}</small>
                        @else
                            <span style="color: #DC2626; font-weight: bold;">BLM LUNAS</span><br>
                            <small>Rp{{ number_format($cargo->fee, 0, ',', '.') }}</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data kargo untuk jadwal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signature-box">
            <div>
                Mengetahui,<br><strong>Agen Asal / Keberangkatan</strong>
                <br><br><br>
                <span class="signature-line">{{ auth()->user()->agent->name ?? (auth()->user()->name ?? 'Petugas Agen') }}</span>
            </div>
            <div>
                <br><strong>Supir / Driver</strong>
                <br><br><br>
                <span class="signature-line">{{ $schedule->driver->name ?? 'Supir' }}</span>
            </div>
            <div>
                Diterima Oleh,<br><strong>Agen Tujuan / Kedatangan</strong>
                <br><br><br>
                <span class="signature-line">Petugas Agen Tujuan</span>
            </div>
        </div>

    </div>
</div>
