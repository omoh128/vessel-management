@extends('layouts.app')

@section('title', 'Import XML Feed')

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <a href="{{ route('vessels.index') }}" class="btn btn-sm">← Back</a>
    <h1 style="font-size:18px;font-weight:600">Import XML feed</h1>
</div>

{{-- Upload form --}}
<div class="card">
    <div class="card-title" style="margin-bottom:16px">Upload Nautic Network XML file</div>

    <form method="POST" action="{{ route('vessels.import.upload') }}" enctype="multipart/form-data">
        @csrf

        <label for="xml_file" style="display:block;border:2px dashed #cbd5e1;border-radius:10px;padding:36px;
                      text-align:center;cursor:pointer;transition:background .15s"
            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
            <div style="font-size:32px;margin-bottom:8px">📂</div>
            <div style="font-weight:500;margin-bottom:4px">Click to choose XML file</div>
            <div style="font-size:12px;color:#94a3b8">Nautic Network XML v2.0 format · Max 50MB</div>
            <input type="file" id="xml_file" name="xml_file" accept=".xml" style="display:none"
                onchange="document.getElementById('fname').textContent = this.files[0]?.name ?? ''">
        </label>
        <div id="fname" style="text-align:center;font-size:13px;color:#0f3460;margin-top:8px"></div>

        @error('xml_file')
        <div class="alert alert-error" style="margin-top:12px">{{ $message }}</div>
        @enderror

        <div style="margin-top:16px;text-align:right">
            <button type="submit" class="btn btn-primary">⬆ Run import</button>
        </div>
    </form>
</div>

{{-- Cron info --}}
<div class="card" style="background:#f8fafc;border-style:dashed">
    <div
        style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px">
        Automated import (cron)
    </div>
    <p style="font-size:13px;color:#475569;margin-bottom:10px">
        Add to <code style="background:#e2e8f0;padding:2px 6px;border-radius:4px">app/Console/Kernel.php</code>
        to run on a schedule:
    </p>
    <pre style="background:#1e293b;color:#94a3b8;padding:14px 16px;border-radius:8px;font-size:12px;overflow-x:auto">
<span style="color:#7dd3fc">$schedule</span><span style="color:#e2e8f0">->command(</span><span style="color:#86efac">'vessels:import'</span><span style="color:#e2e8f0">)->daily();</span>

<span style="color:#64748b"># Or run manually:</span>
<span style="color:#e2e8f0">php artisan vessels:import</span>
<span style="color:#e2e8f0">php artisan vessels:import --file=/path/to/feed.xml</span></pre>
</div>

{{-- Import log --}}
@if($logs->count())
<div class="card">
    <div class="card-title" style="margin-bottom:14px">Recent import history</div>
    <table>
        <thead>
            <tr>
                <th>File</th>
                <th>Source</th>
                <th>Total</th>
                <th>Inserted</th>
                <th>Updated</th>
                <th>Failed</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td style="font-family:monospace;font-size:12px">{{ $log->filename }}</td>
                <td>{{ $log->source }}</td>
                <td>{{ $log->total_records }}</td>
                <td style="color:#166534">{{ $log->inserted }}</td>
                <td style="color:#1d4ed8">{{ $log->updated }}</td>
                <td style="color:{{ $log->failed ? '#dc2626' : '#94a3b8' }}">{{ $log->failed }}</td>
                <td>
                    <span class="badge {{ $log->status === 'complete' ? 'badge-available' : 'badge-under_offer' }}">
                        {{ $log->status }}
                    </span>
                </td>
                <td style="color:#94a3b8;font-size:12px">{{ $log->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection