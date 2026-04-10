<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVesselRequest;
use App\Models\Vessel;
use App\Models\XmlImportLog;
use App\Services\NauticXmlExportService;
use App\Services\NauticXmlImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VesselController extends Controller
{
    public function __construct(
        private readonly NauticXmlImportService $importer,
        private readonly NauticXmlExportService $exporter,
    ) {}

    // ─── Listings ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vessels = Vessel::with(['price', 'location', 'dimensions', 'engine'])
            ->when($request->category, fn($q) => $q->byCategory($request->category))
            ->when($request->status,   fn($q) => $q->where('status', $request->status))
            ->when($request->search,   fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('make', 'like', "%{$request->search}%")
                  ->orWhere('model', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Vessel::distinct()->pluck('category')->sort();

        return view('vessels.index', compact('vessels', 'categories'));
    }

    public function show(Vessel $vessel)
    {
        $vessel->load(['dimensions', 'engine', 'price', 'location']);
        return view('vessels.show', compact('vessel'));
    }

    // ─── Create / Edit ────────────────────────────────────────────────────────

    public function create()
    {
        return view('vessels.create');
    }

    public function store(StoreVesselRequest $request)
    {
        $vessel = DB::transaction(function () use ($request) {
            $vessel = Vessel::create([
                'source'      => 'manual',
                'name'        => $request->make . ' ' . $request->model,
                'category'    => $request->category,
                'make'        => $request->make,
                'model'       => $request->model,
                'year_built'  => $request->year_built,
                'status'      => $request->status,
                'description' => $request->description,
            ]);

            $vessel->dimensions()->create([
                'loa_m'  => $request->loa_m,
                'beam_m' => $request->beam_m,
                'draft_m'=> $request->draft_m,
            ]);

            $vessel->engine()->create([
                'make'      => $request->engine_make,
                'model'     => $request->engine_model,
                'power_hp'  => $request->engine_power_hp,
                'fuel_type' => $request->fuel_type,
            ]);

            $vessel->price()->create([
                'amount'   => $request->price,
                'currency' => $request->currency,
            ]);

            $vessel->location()->create([
                'country' => $request->country,
                'port'    => $request->port,
            ]);

            return $vessel;
        });

        return redirect()
            ->route('vessels.show', $vessel)
            ->with('success', 'Vessel added successfully.');
    }

    public function edit(Vessel $vessel)
    {
        $vessel->load(['dimensions', 'engine', 'price', 'location']);
        return view('vessels.edit', compact('vessel'));
    }

    public function update(StoreVesselRequest $request, Vessel $vessel)
    {
        DB::transaction(function () use ($request, $vessel) {
            $vessel->update([
                'name'        => $request->make . ' ' . $request->model,
                'category'    => $request->category,
                'make'        => $request->make,
                'model'       => $request->model,
                'year_built'  => $request->year_built,
                'status'      => $request->status,
                'description' => $request->description,
            ]);

            $vessel->dimensions()->updateOrCreate(
                ['vessel_id' => $vessel->id],
                ['loa_m' => $request->loa_m, 'beam_m' => $request->beam_m, 'draft_m' => $request->draft_m]
            );

            $vessel->engine()->updateOrCreate(
                ['vessel_id' => $vessel->id],
                ['make' => $request->engine_make, 'model' => $request->engine_model,
                 'power_hp' => $request->engine_power_hp, 'fuel_type' => $request->fuel_type ?? 'diesel']
            );

            $vessel->price()->updateOrCreate(
                ['vessel_id' => $vessel->id],
                ['amount' => $request->price, 'currency' => $request->currency]
            );

            $vessel->location()->updateOrCreate(
                ['vessel_id' => $vessel->id],
                ['country' => $request->country, 'port' => $request->port]
            );
        });

        return redirect()
            ->route('vessels.show', $vessel)
            ->with('success', 'Vessel updated.');
    }

    public function destroy(Vessel $vessel)
    {
        $vessel->delete(); // soft delete
        return redirect()->route('vessels.index')->with('success', 'Vessel removed.');
    }

    // ─── XML Import ───────────────────────────────────────────────────────────

    public function importForm()
    {
        $logs = XmlImportLog::latest()->take(10)->get();
        return view('vessels.import', compact('logs'));
    }

    public function importUpload(Request $request)
    {
        $request->validate([
            'xml_file' => ['required', 'file', 'mimes:xml,txt', 'max:51200'], // 50MB
        ]);

        $path = $request->file('xml_file')->store('xml-imports');
        $fullPath = storage_path("app/{$path}");

        $log = $this->importer->importFromFile($fullPath, 'manual_upload');

        return redirect()
            ->route('vessels.import')
            ->with('import_log', $log->id)
            ->with('success', "Import complete: {$log->inserted} added, {$log->updated} updated, {$log->failed} failed.");
    }

    // ─── XML Export ───────────────────────────────────────────────────────────

    public function exportXml(Request $request)
    {
        $xml = $this->exporter->exportAll($request->only('status', 'category'));

        return response($xml, 200, [
            'Content-Type'        => 'application/xml',
            'Content-Disposition' => 'attachment; filename="nautic-export-' . now()->format('Y-m-d') . '.xml"',
        ]);
    }
}