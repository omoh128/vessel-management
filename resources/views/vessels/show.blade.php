@extends('layouts.app')

@section('title', $vessel->make . ' ' . $vessel->model)

@section('content')

<div style="margin-bottom: 20px;">
    <a href="{{ route('vessels.index') }}" style="text-decoration: none; color: #64748b;">← Back to listings</a>
</div>

<div class="card">
    <div class="card-header" style="justify-content: space-between; display: flex; align-items: center;">
        <div>
            <h1 class="card-title" style="font-size: 24px;">{{ $vessel->make }} {{ $vessel->model }}</h1>
            <span class="badge badge-{{ $vessel->status }}">
                {{ str_replace('_', ' ', ucfirst($vessel->status)) }}
            </span>
        </div>
        <div style="display:flex; gap:8px">
            <a href="{{ route('vessels.edit', $vessel) }}" class="btn">Edit Details</a>
            <form action="{{ route('vessels.destroy', $vessel) }}" method="POST"
                onsubmit="return confirm('Delete this vessel?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="color: #ef4444; border-color: #fecaca;">Delete</button>
            </form>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding: 20px;">

        {{-- Specifications Column --}}
        <div>
            <h3 style="margin-bottom: 12px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                Specifications</h3>
            <table class="details-table" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Category</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $vessel->category }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Year Built</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $vessel->year_built }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Length (LOA)</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $vessel->dimensions->loa_m ?? '—' }} m</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Beam</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $vessel->dimensions->beam_m ?? '—' }} m</td>
                </tr>
            </table>
        </div>

        {{-- Engine & Pricing Column --}}
        <div>
            <h3 style="margin-bottom: 12px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                Engine & Location</h3>
            <table class="details-table" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Engine</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $vessel->engine->make ?? '—' }}
                        {{ $vessel->engine->model ?? '' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Location</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $vessel->location->port ?? 'Unknown Port' }},
                        {{ $vessel->location->country ?? '' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Price</td>
                    <td style="padding: 8px 0; font-weight: 700; color: #0f3460; font-size: 1.2em;">
                        {{ number_format($vessel->price->amount ?? 0) }} {{ $vessel->price->currency ?? 'USD' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if($vessel->description)
    <div style="padding: 20px; border-top: 1px solid #e2e8f0;">
        <h3 style="margin-bottom: 8px; color: #1e293b;">Description</h3>
        <p style="color: #475569; line-height: 1.6;">{{ $vessel->description }}</p>
    </div>
    @endif
</div>

@endsection