@extends('layouts.app')

@section('title', 'Vessel Listings')

@section('content')

{{-- Stats row --}}
<div class="stat-row">
    <div class="stat">
        <div class="stat-val">{{ $vessels->total() }}</div>
        <div class="stat-lbl">Total listings</div>
    </div>
    <div class="stat">
        <div class="stat-val">{{ \App\Models\Vessel::where('status','available')->count() }}</div>
        <div class="stat-lbl">Available</div>
    </div>
    <div class="stat">
        <div class="stat-val">{{ \App\Models\Vessel::where('source','xml')->count() }}</div>
        <div class="stat-lbl">Via XML import</div>
    </div>
    <div class="stat">
        <div class="stat-val">{{ \App\Models\Vessel::where('source','manual')->count() }}</div>
        <div class="stat-lbl">Manual entries</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">Vessel listings</h1>
        <div style="display:flex;gap:8px">
            <a href="{{ route('vessels.export') }}" class="btn">⬇ Export XML</a>
            <a href="{{ route('vessels.import') }}" class="btn">⬆ Import XML</a>
            <a href="{{ route('vessels.create') }}" class="btn btn-primary">+ Add vessel</a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('vessels.index') }}"
        style="display:flex;gap:8px;margin-bottom:16px;align-items:center">
        <input name="search" value="{{ request('search') }}" placeholder="Search make, model..."
            style="padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;width:220px">
        <select name="category" style="padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
            <option value="">All categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="status" style="padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
            <option value="">All statuses</option>
            <option value="available" @selected(request('status')==='available' )>Available</option>
            <option value="under_offer" @selected(request('status')==='under_offer' )>Under offer</option>
            <option value="sold" @selected(request('status')==='sold' )>Sold</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','category','status']))
        <a href="{{ route('vessels.index') }}" class="btn">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>Vessel</th>
                <th>Category</th>
                <th>Year</th>
                <th>Location</th>
                <th>Price</th>
                <th>Status</th>
                <th>Source</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($vessels as $vessel)
            <tr>
                <td>
                    <strong>{{ $vessel->make }}</strong> {{ $vessel->model }}
                </td>
                <td>{{ $vessel->category }}</td>
                <td>{{ $vessel->year_built ?? '—' }}</td>
                <td>{{ $vessel->location?->port ?? '—' }}, {{ $vessel->location?->country ?? '' }}</td>
                <td style="font-weight:600;color:#0f3460">
                    {{ $vessel->formatted_price }}
                </td>
                <td>
                    <span class="badge badge-{{ $vessel->status }}">
                        {{ str_replace('_', ' ', ucfirst($vessel->status)) }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $vessel->source }}">{{ $vessel->source }}</span>
                </td>
                <td style="text-align:right">
                    <a href="{{ route('vessels.show', $vessel) }}" class="btn btn-sm">View</a>
                    <a href="{{ route('vessels.edit', $vessel) }}" class="btn btn-sm">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;color:#94a3b8;padding:32px">
                    No vessels found. <a href="{{ route('vessels.create') }}">Add one</a> or
                    <a href="{{ route('vessels.import') }}">import XML</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination" style="margin-top:16px">
        {{ $vessels->links() }}
    </div>
</div>

@endsection