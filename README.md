# Vessel Management System

A Laravel application for managing marine vessel listings using the
[Nautic Network XML specification](https://www.nautic-network.org/).
Supports both XML feed imports and manual data entry, with full export capability.

---

## Table of Contents

1. [Features](#features)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Database Schema](#database-schema)
5. [XML Import](#xml-import)
6. [XML Export](#xml-export)
7. [Manual Form Entry](#manual-form-entry)
8. [Scheduled Cron Import](#scheduled-cron-import)
9. [Running Tests](#running-tests)
10. [File Structure](#file-structure)

---

## Features

| Feature | Detail |
|---------|--------|
| **Database migrations** | 6 normalised tables covering vessels, dimensions, engines, pricing, locations, and import logs |
| **XML import** | Parses Nautic Network v2.0 XML; idempotent (safe to re-run); per-record error handling |
| **XML export** | Exports any filtered set of vessels back to spec-compliant XML |
| **Manual forms** | Full Blade form with server-side validation via FormRequest |
| **Import logging** | Every import logged to `xml_import_logs` with inserted/updated/failed counts |
| **Cron import** | Artisan command `vessels:import` for scheduled or webhook-triggered imports |
| **Soft deletes** | Vessels are soft-deleted, never hard-removed |
| **Test suite** | Feature and unit tests covering import, export, form validation, and CRUD |

---

## Requirements

- PHP 8.2+
- Laravel 11.x
- MySQL 8.0+ (or SQLite for local dev)
- Composer

---

## Installation

```bash
# 1. Clone and install
git clone https://github.com/your-username/nautic-network.git
cd nautic-network
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure your database in .env
DB_DATABASE=nautic_network
DB_USERNAME=root
DB_PASSWORD=

# 4. Run migrations
php artisan migrate

# 5. Seed with sample data (imports sample-feed.xml)
php artisan db:seed --class=VesselSeeder

# 6. Serve
php artisan serve
# Visit: http://localhost:8000/vessels
```

---

## Database Schema

Six tables, all with proper foreign keys and indexes:

```
vessels                 — core listing record (external_id, source, category, status)
  └── vessel_dimensions — loa_m, beam_m, draft_m, weight_kg, mast_height_m
  └── vessel_engines    — make, model, power_hp, fuel_type, hours
  └── vessel_prices     — amount, currency, vat_included
  └── vessel_locations  — country, region, port, latitude, longitude

xml_import_logs         — audit trail for every import run
```

Key design decisions:
- `external_id` on `vessels` is unique and nullable — used to deduplicate XML imports
- `source` enum (`xml` | `manual`) tracks how each record was created
- All related tables use `cascadeOnDelete` so removing a vessel cleans up automatically
- `vessels` uses `softDeletes` so no data is ever permanently lost

---

## XML Import

### Manual upload (web UI)

Visit `/vessels/import` and upload any Nautic Network-compliant XML file.

### Via Artisan (cron / CLI)

```bash
# Import from a local file
php artisan vessels:import --file=storage/xml-imports/sample-feed.xml

# Import from a remote URL (set NAUTIC_FEED_URL in .env)
php artisan vessels:import

# Specify source label for the audit log
php artisan vessels:import --source=cron
```

### How the importer works

1. Loads and validates the XML (catches malformed files cleanly)
2. Creates an `xml_import_logs` entry with `status=processing`
3. Loops over `<Vessel>` nodes, running each inside a DB transaction
4. Uses `updateOrCreate` on `external_id` — **idempotent by design**
5. Updates the log with inserted/updated/failed counts on completion

### Sample XML format

See `storage/xml-imports/sample-feed.xml` for a full working example. Key structure:

```xml
<NauticNetwork version="2.0">
  <Vessel>
    <ID>VES-00142</ID>
    <Category>Sailboat</Category>
    <Make>Bavaria Yachtbau</Make>
    <Model>44 Cruiser</Model>
    <YearBuilt>2019</YearBuilt>
    <Status>available</Status>
    <Dimensions>
      <LOA unit="m">13.60</LOA>
      <Beam unit="m">4.27</Beam>
      <Draft unit="m">1.85</Draft>
    </Dimensions>
    <Engine>
      <Make>Volvo Penta</Make>
      <Model>D2-40</Model>
      <Power unit="HP">40</Power>
      <FuelType>Diesel</FuelType>
    </Engine>
    <Price currency="EUR">89500</Price>
    <Location>
      <Country>Spain</Country>
      <Port>Palma de Mallorca</Port>
    </Location>
  </Vessel>
</NauticNetwork>
```

---

## XML Export

Export all vessels (or filtered) to a spec-compliant XML file:

```
GET /vessels/export/xml                     — export all
GET /vessels/export/xml?status=available    — filter by status
GET /vessels/export/xml?category=Sailboat   — filter by category
```

The response streams as a downloadable `.xml` file with correct `Content-Type` headers.

---

## Manual Form Entry

Visit `/vessels/create` to enter a vessel manually. The form covers:

- Basic info (make, model, category, year, status)
- Dimensions (LOA, beam, draft)
- Engine (make, model, power, fuel type)
- Pricing (amount, currency)
- Location (country, port)

Validation is handled by `StoreVesselRequest` with human-readable error messages.
All form data is stored in the same normalised tables as XML imports.

---

## Scheduled Cron Import

Add to `app/Console/Kernel.php`:

```php
$schedule->command('vessels:import')->daily();
// or
$schedule->command('vessels:import')->twiceDaily(6, 18);
```

Set `NAUTIC_FEED_URL=https://your-feed-provider.com/feed.xml` in `.env` to pull from a remote source automatically.

---

## Running Tests

```bash
# All tests
php artisan test

# Specific suites
php artisan test --filter XmlImportTest
php artisan test --filter XmlExportTest
php artisan test --filter VesselFormTest

# With coverage (requires Xdebug)
php artisan test --coverage
```

Test coverage includes:
- XML import: valid file, idempotency, related records, invalid file, audit log
- XML export: single vessel, parseable output, filtered export
- Form: create, validation, update, soft delete

---

## File Structure

```
app/
├── Console/Commands/
│   └── ImportVesselsCommand.php     # php artisan vessels:import
├── Http/
│   ├── Controllers/
│   │   └── VesselController.php     # index, show, create, store, update, destroy, import, export
│   └── Requests/
│       └── StoreVesselRequest.php   # validation rules
├── Models/
│   ├── Vessel.php                   # main model + scopes + accessor
│   └── VesselRelated.php            # VesselDimension, VesselEngine, VesselPrice, VesselLocation, XmlImportLog
└── Services/
    ├── NauticXmlImportService.php   # XML parsing + DB upsert
    └── NauticXmlExportService.php   # Vessel → XML serialisation

database/
├── migrations/                      # 6 migration files
└── seeders/
    └── VesselSeeder.php             # seeds via the real importer

resources/views/
├── layouts/app.blade.php            # base layout
└── vessels/
    ├── index.blade.php              # listings table + filters
    ├── create.blade.php             # add/edit form
    ├── show.blade.php               # detail view
    └── import.blade.php             # upload form + import log

routes/web.php                       # all application routes
storage/xml-imports/sample-feed.xml  # 5-vessel sample XML feed

tests/
├── Feature/
│   ├── XmlImportTest.php
│   └── VesselFormTest.php
└── Unit/
    └── XmlExportTest.php
```
