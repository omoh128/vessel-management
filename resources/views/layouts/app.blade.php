<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nautic Network') — Vessel Manager</title>
    <style>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 14px;
        color: #1a1a2e;
        background: #f5f7fa;
    }

    /* ── Nav ────────────────────────────────────────────── */
    .nav {
        background: #0f3460;
        color: #fff;
        padding: 0 24px;
        display: flex;
        align-items: center;
        height: 52px;
        gap: 24px;
    }

    .nav-brand {
        font-weight: 600;
        font-size: 16px;
        margin-right: auto;
    }

    .nav a {
        color: rgba(255, 255, 255, .75);
        text-decoration: none;
        font-size: 13px;
    }

    .nav a:hover {
        color: #fff;
    }

    /* ── Layout ─────────────────────────────────────────── */
    .container {
        max-width: 1100px;
        margin: 32px auto;
        padding: 0 20px;
    }

    /* ── Cards ──────────────────────────────────────────── */
    .card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 20px 24px;
        margin-bottom: 16px;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .card-title {
        font-size: 15px;
        font-weight: 600;
    }

    /* ── Buttons ─────────────────────────────────────────── */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #1a1a2e;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn:hover {
        background: #f1f5f9;
    }

    .btn-primary {
        background: #0f3460;
        border-color: #0f3460;
        color: #fff;
    }

    .btn-primary:hover {
        background: #0a2440;
    }

    .btn-success {
        background: #166534;
        border-color: #166534;
        color: #fff;
    }

    .btn-sm {
        padding: 4px 10px;
        font-size: 12px;
    }

    /* ── Alerts ──────────────────────────────────────────── */
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 13px;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    /* ── Badges ──────────────────────────────────────────── */
    .badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }

    .badge-available {
        background: #dcfce7;
        color: #166534;
    }

    .badge-under_offer {
        background: #fef9c3;
        color: #854d0e;
    }

    .badge-sold {
        background: #f1f5f9;
        color: #64748b;
    }

    .badge-xml {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .badge-manual {
        background: #faf5ff;
        color: #6b21a8;
    }

    /* ── Tables ──────────────────────────────────────────── */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    th {
        text-align: left;
        padding: 10px 12px;
        background: #f8fafc;
        color: #64748b;
        font-weight: 500;
        border-bottom: 1px solid #e2e8f0;
        font-size: 12px;
    }

    td {
        padding: 12px 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    tr:hover td {
        background: #f8fafc;
    }

    /* ── Forms ───────────────────────────────────────────── */
    .form-section {
        margin-bottom: 24px;
    }

    .form-section-title {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
    }

    .form-full {
        grid-column: 1 / -1;
    }

    .field label {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        color: #1a1a2e;
        background: #fff;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        outline: none;
        border-color: #0f3460;
        box-shadow: 0 0 0 3px rgba(15, 52, 96, .1);
    }

    .field .error {
        color: #dc2626;
        font-size: 11px;
        margin-top: 3px;
    }

    /* ── Stat row ────────────────────────────────────────── */
    .stat-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
    }

    .stat-val {
        font-size: 24px;
        font-weight: 600;
        color: #0f3460;
    }

    .stat-lbl {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* ── Pagination ──────────────────────────────────────── */
    .pagination {
        display: flex;
        gap: 4px;
        margin-top: 16px;
    }

    .pagination a,
    .pagination span {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 12px;
        text-decoration: none;
        color: #1a1a2e;
    }

    .pagination .active span {
        background: #0f3460;
        color: #fff;
        border-color: #0f3460;
    }
    </style>
</head>

<body>

    <nav class="nav">
        <span class="nav-brand">⚓ Nautic Network</span>
        <a href="{{ route('vessels.index') }}">Listings</a>
        <a href="{{ route('vessels.create') }}">Add vessel</a>
        <a href="{{ route('vessels.import') }}">Import XML</a>
        <a href="{{ route('vessels.export') }}">Export XML</a>
    </nav>

    <div class="container">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">
            <ul style="padding-left:16px">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </div>

</body>

</html>