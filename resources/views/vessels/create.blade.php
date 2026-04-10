@extends('layouts.app')

@section('title', isset($vessel) ? 'Edit Vessel' : 'Add Vessel')

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <a href="{{ route('vessels.index') }}" class="btn btn-sm">← Back</a>
    <h1 style="font-size:18px;font-weight:600">
        {{ isset($vessel) ? 'Edit: ' . $vessel->name : 'Add new vessel' }}
    </h1>
</div>

<form method="POST" action="{{ isset($vessel) ? route('vessels.update', $vessel) : route('vessels.store') }}">
    @csrf
    @if(isset($vessel)) @method('PUT') @endif

    {{-- ── Basic Information ──────────────────────────────────────────── --}}
    <div class="card">
        <div class="form-section-title">Basic information</div>
        <div class="form-grid">
            <div class="field">
                <label>Vessel make / builder *</label>
                <input name="make" value="{{ old('make', $vessel->make ?? '') }}" placeholder="e.g. Bavaria Yachtbau">
                @error('make')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Model *</label>
                <input name="model" value="{{ old('model', $vessel->model ?? '') }}" placeholder="e.g. 44 Cruiser">
                @error('model')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Category *</label>
                <select name="category">
                    <option value="">Select category</option>
                    @foreach(['Sailboat','Motorboat','Catamaran','RIB','Classic','Superyacht'] as $cat)
                    <option value="{{ $cat }}"
                        {{ old('category', $vessel->category ?? '') === $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                    @endforeach
                </select>
                @error('category')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Year built</label>
                <input name="year_built" type="number" min="1900" max="{{ date('Y') }}"
                    value="{{ old('year_built', $vessel->year_built ?? '') }}" placeholder="{{ date('Y') }}">
                @error('year_built')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Status *</label>
                <select name="status">
                    <option value="available"
                        {{ old('status', $vessel->status ?? '') === 'available'   ? 'selected' : '' }}>Available
                    </option>
                    <option value="under_offer"
                        {{ old('status', $vessel->status ?? '') === 'under_offer' ? 'selected' : '' }}>Under offer
                    </option>
                    <option value="sold" {{ old('status', $vessel->status ?? '') === 'sold'        ? 'selected' : '' }}>
                        Sold</option>
                </select>
            </div>
            <div class="field form-full">
                <label>Description</label>
                <textarea name="description" rows="3"
                    placeholder="Additional details about this vessel...">{{ old('description', $vessel->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── Dimensions ──────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="form-section-title">Dimensions</div>
        <div class="form-grid-3">
            <div class="field">
                <label>LOA — length overall (m)</label>
                <input name="loa_m" type="number" step="0.01"
                    value="{{ old('loa_m', $vessel->dimensions->loa_m ?? '') }}" placeholder="13.60">
                @error('loa_m')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Beam (m)</label>
                <input name="beam_m" type="number" step="0.01"
                    value="{{ old('beam_m', $vessel->dimensions->beam_m ?? '') }}" placeholder="4.27">
            </div>
            <div class="field">
                <label>Draft (m)</label>
                <input name="draft_m" type="number" step="0.01"
                    value="{{ old('draft_m', $vessel->dimensions->draft_m ?? '') }}" placeholder="1.85">
            </div>
        </div>
    </div>

    {{-- ── Engine ──────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="form-section-title">Engine</div>
        <div class="form-grid">
            <div class="field">
                <label>Engine make</label>
                <input name="engine_make" value="{{ old('engine_make', $vessel->engine->make ?? '') }}"
                    placeholder="e.g. Volvo Penta">
            </div>
            <div class="field">
                <label>Engine model</label>
                <input name="engine_model" value="{{ old('engine_model', $vessel->engine->model ?? '') }}"
                    placeholder="e.g. D2-40">
            </div>
            <div class="field">
                <label>Power (HP)</label>
                <input name="engine_power_hp" type="number"
                    value="{{ old('engine_power_hp', $vessel->engine->power_hp ?? '') }}" placeholder="40">
            </div>
            <div class="field">
                <label>Fuel type</label>
                <select name="fuel_type">
                    @foreach(['diesel','petrol','electric','hybrid'] as $fuel)
                    <option value="{{ $fuel }}"
                        {{ old('fuel_type', $vessel->engine->fuel_type ?? 'diesel') === $fuel ? 'selected' : '' }}>
                        {{ ucfirst($fuel) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Pricing & Location ──────────────────────────────────────────── --}}
    <div class="card">
        <div class="form-section-title">Pricing & location</div>
        <div class="form-grid">
            <div class="field">
                <label>Price *</label>
                <input name="price" type="number" step="0.01" value="{{ old('price', $vessel->price->amount ?? '') }}"
                    placeholder="89500">
                @error('price')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Currency</label>
                <select name="currency">
                    @foreach(['EUR','USD','GBP'] as $cur)
                    <option value="{{ $cur }}"
                        {{ old('currency', $vessel->price->currency ?? 'EUR') === $cur ? 'selected' : '' }}>
                        {{ $cur }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Country</label>
                <input name="country" value="{{ old('country', $vessel->location->country ?? '') }}"
                    placeholder="e.g. Spain">
            </div>
            <div class="field">
                <label>Marina / port</label>
                <input name="port" value="{{ old('port', $vessel->location->port ?? '') }}"
                    placeholder="e.g. Palma de Mallorca">
            </div>
        </div>
    </div>

    <div style="display:flex;gap:8px;justify-content:flex-end;margin-bottom:40px">
        <a href="{{ route('vessels.index') }}" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">
            {{ isset($vessel) ? 'Update vessel' : 'Save vessel' }}
        </button>
    </div>
</form>

@endsection