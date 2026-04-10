<?php

namespace App\Services;

use App\Models\Vessel;
use Illuminate\Support\Enumerable;
use Illuminate\Database\Eloquent\Collection;

/**
 * NauticXmlExportService
 *
 * Serialises Vessel records back to Nautic Network-compliant XML.
 * Can export a single vessel or a full filtered collection.
 */
class NauticXmlExportService
{
    public function exportAll(array $filters = []): string
    {
        $vessels = Vessel::with(['dimensions', 'engine', 'price', 'location'])
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($filters['category'] ?? null, fn($q, $c) => $q->where('category', $c))
            ->get();

        return $this->buildXml($vessels);
    }

    public function exportSingle(Vessel $vessel): string
    {
        $vessel->load(['dimensions', 'engine', 'price', 'location']);
        return $this->buildXml(collect([$vessel]));
    }

    // ─── Private ──────────────────────────────────────────────────────────────
      private function buildXml(Enumerable $vessels): string

    {
        $dom               = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('NauticNetwork');
        $root->setAttribute('version', '2.0');
        $root->setAttribute('exported_at', now()->toIso8601String());
        $root->setAttribute('count', (string) $vessels->count());
        $dom->appendChild($root);

        foreach ($vessels as $vessel) {
            $root->appendChild($this->buildVesselNode($dom, $vessel));
        }

        return $dom->saveXML();
    }

    private function buildVesselNode(\DOMDocument $dom, Vessel $vessel): \DOMElement
    {
        $node = $dom->createElement('Vessel');

        $this->addChild($dom, $node, 'ID',          $vessel->external_id ?? 'MAN-' . $vessel->id);
        $this->addChild($dom, $node, 'Category',    $vessel->category);
        $this->addChild($dom, $node, 'Make',        $vessel->make);
        $this->addChild($dom, $node, 'Model',       $vessel->model);
        $this->addChild($dom, $node, 'YearBuilt',   (string) $vessel->year_built);
        $this->addChild($dom, $node, 'Status',      $vessel->status);
        $this->addChild($dom, $node, 'Description', $vessel->description);

        if ($vessel->dimensions) {
            $dim = $dom->createElement('Dimensions');
            $this->addChildWithAttr($dom, $dim, 'LOA',  (string) $vessel->dimensions->loa_m,  'unit', 'm');
            $this->addChildWithAttr($dom, $dim, 'Beam', (string) $vessel->dimensions->beam_m, 'unit', 'm');
            $this->addChildWithAttr($dom, $dim, 'Draft',(string) $vessel->dimensions->draft_m,'unit', 'm');
            $node->appendChild($dim);
        }

        if ($vessel->engine) {
            $eng = $dom->createElement('Engine');
            $this->addChild($dom, $eng, 'Make',     $vessel->engine->make);
            $this->addChild($dom, $eng, 'Model',    $vessel->engine->model);
            $this->addChildWithAttr($dom, $eng, 'Power', (string) $vessel->engine->power_hp, 'unit', 'HP');
            $this->addChild($dom, $eng, 'FuelType', $vessel->engine->fuel_type);
            $node->appendChild($eng);
        }

        if ($vessel->price) {
            $priceEl = $dom->createElement('Price', (string) $vessel->price->amount);
            $priceEl->setAttribute('currency', $vessel->price->currency);
            $node->appendChild($priceEl);
        }

        if ($vessel->location) {
            $loc = $dom->createElement('Location');
            $this->addChild($dom, $loc, 'Country', $vessel->location->country);
            $this->addChild($dom, $loc, 'Port',    $vessel->location->port);
            $node->appendChild($loc);
        }

        return $node;
    }

    private function addChild(\DOMDocument $dom, \DOMElement $parent, string $tag, ?string $value): void
    {
        if ($value === null || $value === '') return;
        $parent->appendChild($dom->createElement($tag, htmlspecialchars($value)));
    }

    private function addChildWithAttr(\DOMDocument $dom, \DOMElement $parent, string $tag, string $value, string $attr, string $attrVal): void
    {
        if ($value === '' || $value === '0') return;
        $el = $dom->createElement($tag, $value);
        $el->setAttribute($attr, $attrVal);
        $parent->appendChild($el);
    }
}