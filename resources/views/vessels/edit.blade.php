@extends('layouts.app')

@section('title', 'Edit Vessel: ' . $vessel->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h1 class="card-title">Edit Vessel: {{ $vessel->name }}</h1>
    </div>

    <form action="{{ route('vessels.update', $vessel) }}" method="POST" style="padding: 20px;">
        @csrf
        @method('PUT')

        {{-- Section 1: Basic Info --}}
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px;">General Information</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div>
                <label>Make</label>
                <input type="text" name="make" value="{{ old('make', $vessel->make) }}" class="form-control" required>
            </div>
            <div>
                <label>Model</label>
                <input type="text" name="model" value="{{ old('model', $vessel->model) }}" class="form-control"
                    required>
            </div>
            <div>
                <label>Category</label>
                <input type="text" name="category" value="{{ old('category', $vessel->category) }}"
                    class="form-control">
            </div>
            <div>
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="available" @selected(old('status', $vessel->status) == 'available')>Available
                    </option>
                    <option value="under_offer" @selected(old('status', $vessel->status) == 'under_offer')>Under Offer
                    </option>
                    <option value="sold" @selected(old('status', $vessel->status) == 'sold')>Sold</option>
                </select>
            </div>
        </div>

        {{-- Section 2: Price & Location --}}
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Price & Location</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div>
                <label>Price</label>
                <input type="number" name="price" value="{{ old('price', $vessel->price?->amount) }}"
                    class="form-control">
            </div>
            <div>
                <label>Currency</label>
                <input type="text" name="currency" value="{{ old('currency', $vessel->price?->currency ?? 'USD') }}"
                    class="form-control">
            </div>
            <div>
                <label>Country</label>
                <input type="text" name="country" value="{{ old('country', $vessel->location?->country) }}"
                    class="form-control">
            </div>
            <div>
                <label>Port</label>
                <input type="text" name="port" value="{{ old('port', $vessel->location?->port) }}" class="form-control">
            </div>
        </div>

        {{-- Section 3: Engine Details --}}
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Engine Details</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div>
                <label>Engine Make</label>
                <input type="text" name="engine_make" value="{{ old('engine_make', $vessel->engine?->make) }}"
                    class="form-control">
            </div>
            <div>
                <label>Engine Model</label>
                <input type="text" name="engine_model" value="{{ old('engine_model', $vessel->engine?->model) }}"
                    class="form-control">
            </div>
            <div>
                <label>Power (HP)</label>
                <input type="number" name="engine_power_hp"
                    value="{{ old('engine_power_hp', $vessel->engine?->power_hp) }}" class="form-control">
            </div>
            <div>
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control @error('fuel_type') is-invalid @enderror">
                    <option value="">-- Select Fuel --</option>
                    <option value="diesel" @selected(old('fuel_type', $vessel->engine?->fuel_type) == 'diesel')>Diesel
                    </option>
                    <option value="petrol" @selected(old('fuel_type', $vessel->engine?->fuel_type) == 'petrol')>Petrol
                    </option>
                    <option value="electric" @selected(old('fuel_type', $vessel->engine?->fuel_type) ==
                        'electric')>Electric</option>
                    <option value="hybrid" @selected(old('fuel_type', $vessel->engine?->fuel_type) == 'hybrid')>Hybrid
                    </option>
                </select>
                @error('fuel_type')
                <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Section 4: Dimensions --}}
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Dimensions</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div>
                <label>LOA (m)</label>
                <input type="number" step="0.01" name="loa_m" value="{{ old('loa_m', $vessel->dimensions?->loa_m) }}"
                    class="form-control">
            </div>
            <div>
                <label>Beam (m)</label>
                <input type="number" step="0.01" name="beam_m" value="{{ old('beam_m', $vessel->dimensions?->beam_m) }}"
                    class="form-control">
            </div>
            <div>
                <label>Draft (m)</label>
                <input type="number" step="0.01" name="draft_m"
                    value="{{ old('draft_m', $vessel->dimensions?->draft_m) }}" class="form-control">
            </div>
        </div>

        {{-- Buttons at the bottom --}}
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #f1f5f9; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Update Vessel</button>
            <a href="{{ route('vessels.show', $vessel) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection