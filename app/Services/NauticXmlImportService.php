<?php

namespace App\Services;

use App\Models\Vessel;
use App\Models\XmlImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * NauticXmlImportService
 *
 * Parses Nautic Network XML feeds and upserts vessels into the database.
 * Designed to be idempotent — safe to run multiple times on the same file.
 * Tracks every import in xml_import_logs for full auditability.
 */
class NauticXmlImportService
{
    private XmlImportLog $log;
    private array $errors = [];

    public function importFromFile(string $filePath, string $source = 'manual_upload'): XmlImportLog
    {
        // Create an audit log entry immediately
        $this->log = XmlImportLog::create([
            'filename'   => basename($filePath),
            'source'     => $source,
            'status'     => 'processing',
            'started_at' => now(),
        ]);

        try {
            $xml = $this->loadAndValidateXml($filePath);
            $this->processVessels($xml);

            $this->log->update([
                'status'      => 'complete',
                'finished_at' => now(),
                'errors'      => $this->errors ?: null,
            ]);
        } catch (\Exception $e) {
            $this->log->update([
                'status'      => 'failed',
                'finished_at' => now(),
                'errors'      => [['fatal' => $e->getMessage()]],
            ]);

            Log::error('NauticXmlImport fatal error', ['error' => $e->getMessage()]);
        }

        return $this->log->fresh();
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function loadAndValidateXml(string $filePath): \SimpleXMLElement
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("XML file not found: {$filePath}");
        }

        // Suppress libxml errors so we can handle them cleanly
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            $errors = array_map(fn($e) => $e->message, libxml_get_errors());
            libxml_clear_errors();
            throw new \RuntimeException('Invalid XML: ' . implode('; ', $errors));
        }

        return $xml;
    }

    private function processVessels(\SimpleXMLElement $xml): void
    {
        $vessels = $xml->Vessel ?? [];
        $total   = count($vessels);

        $this->log->update(['total_records' => $total]);

        foreach ($vessels as $vesselNode) {
            $externalId = (string) ($vesselNode->ID ?? '');

            try {
                DB::transaction(function () use ($vesselNode, $externalId) {
                    $this->upsertVessel($vesselNode, $externalId);
                });
            } catch (\Exception $e) {
                $this->errors[] = [
                    'external_id' => $externalId,
                    'error'       => $e->getMessage(),
                ];

                $this->log->increment('failed');
                Log::warning('NauticXmlImport: vessel failed', [
                    'id'    => $externalId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function upsertVessel(\SimpleXMLElement $node, string $externalId): void
    {
        // updateOrCreate ensures the import is idempotent
        $vessel = Vessel::updateOrCreate(
            ['external_id' => $externalId ?: null],
            [
                'source'     => 'xml',
                'name'       => trim((string) $node->Make . ' ' . (string) $node->Model),
                'category'   => (string) ($node->Category ?? ''),
                'make'       => (string) ($node->Make ?? ''),
                'model'      => (string) ($node->Model ?? ''),
                'year_built' => (int)    ($node->YearBuilt ?? 0) ?: null,
                'status'     => $this->mapStatus((string) ($node->Status ?? 'available')),
                'description'=> (string) ($node->Description ?? ''),
            ]
        );

        $wasRecentlyCreated = $vessel->wasRecentlyCreated;

        // Upsert related tables
        $this->upsertDimensions($vessel, $node->Dimensions ?? null);
        $this->upsertEngine($vessel, $node->Engine ?? null);
        $this->upsertPrice($vessel, $node);
        $this->upsertLocation($vessel, $node->Location ?? null);

        // Update counters on the log
        if ($wasRecentlyCreated) {
            $this->log->increment('inserted');
        } else {
            $this->log->increment('updated');
        }
    }

    private function upsertDimensions(Vessel $vessel, ?\SimpleXMLElement $node): void
    {
        if (!$node) return;

        $vessel->dimensions()->updateOrCreate(
            ['vessel_id' => $vessel->id],
            [
                'loa_m'         => (float) ($node->LOA ?? 0) ?: null,
                'beam_m'        => (float) ($node->Beam ?? 0) ?: null,
                'draft_m'       => (float) ($node->Draft ?? 0) ?: null,
                'weight_kg'     => (int)   ($node->Weight ?? 0) ?: null,
                'mast_height_m' => (float) ($node->MastHeight ?? 0) ?: null,
            ]
        );
    }

    private function upsertEngine(Vessel $vessel, ?\SimpleXMLElement $node): void
    {
        if (!$node) return;

        $vessel->engine()->updateOrCreate(
            ['vessel_id' => $vessel->id],
            [
                'make'      => (string) ($node->Make ?? ''),
                'model'     => (string) ($node->Model ?? ''),
                'power_hp'  => (int)    ($node->Power ?? 0) ?: null,
                'hours'     => (int)    ($node->Hours ?? 0) ?: null,
                'fuel_type' => strtolower((string) ($node->FuelType ?? 'diesel')),
                'year'      => (int)    ($node->Year ?? 0) ?: null,
            ]
        );
    }

    private function upsertPrice(Vessel $vessel, \SimpleXMLElement $node): void
    {
        $priceNode = $node->Price ?? null;
        if (!$priceNode) return;

        $vessel->price()->updateOrCreate(
            ['vessel_id' => $vessel->id],
            [
                'amount'           => (float) $priceNode,
                'currency'         => (string) ($priceNode['currency'] ?? 'EUR'),
                'vat_included'     => filter_var((string) ($node->VatIncluded ?? 'false'), FILTER_VALIDATE_BOOLEAN),
                'price_on_request' => filter_var((string) ($node->PriceOnRequest ?? 'false'), FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }

    private function upsertLocation(Vessel $vessel, ?\SimpleXMLElement $node): void
    {
        if (!$node) return;

        $vessel->location()->updateOrCreate(
            ['vessel_id' => $vessel->id],
            [
                'country'   => (string) ($node->Country ?? ''),
                'region'    => (string) ($node->Region ?? ''),
                'port'      => (string) ($node->Port ?? ''),
                'latitude'  => (float) ($node->Latitude ?? 0) ?: null,
                'longitude' => (float) ($node->Longitude ?? 0) ?: null,
            ]
        );
    }

    private function mapStatus(string $xmlStatus): string
    {
        return match (strtolower($xmlStatus)) {
            'available', 'active', '1' => 'available',
            'underoffer', 'under_offer' => 'under_offer',
            'sold', 'inactive', '0'    => 'sold',
            default                    => 'available',
        };
    }
}