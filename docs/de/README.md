# dskripchenko/laravel-php-docx

> Laravel-Brücke zu [`dskripchenko/php-docx`](https://github.com/dskripchenko/php-docx) —
> dem abhängigkeitsfreien DOCX-⇄-HTML-Werkzeugkasten unter **MIT-Lizenz**.
> Blade-Views direkt als Word-Dokument, DOCX-Import zurück nach HTML,
> Erkennung von Vorlagenvariablen. Die Ausgabe wird
> [bei jedem Push gegen die ECMA-376-Schemata geprüft](https://github.com/dskripchenko/php-docx/blob/main/docs/CONFORMANCE.md),
> die Lesetreue
> [an einem externen Korpus gemessen](https://github.com/dskripchenko/php-docx/blob/main/docs/READER-FIDELITY.md).

> 🌐 [English](../../README.md) · **Deutsch** · [Русский](../ru/README.md) · [中文](../zh/README.md)

[![Tests](https://img.shields.io/github/actions/workflow/status/dskripchenko/laravel-php-docx/tests.yml?branch=main&label=tests&logo=github)](https://github.com/dskripchenko/laravel-php-docx/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/dskripchenko/laravel-php-docx?logo=packagist&logoColor=white)](https://packagist.org/packages/dskripchenko/laravel-php-docx)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](../../LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-red.svg)](https://laravel.com)

## Installation

```bash
composer require dskripchenko/laravel-php-docx
```

Service Provider und die `Docx`-Facade registrieren sich selbst (Package
Discovery). Die Konfiguration lässt sich bei Bedarf veröffentlichen:

```bash
php artisan vendor:publish --tag=php-docx-config
```

## Verwendung

### Blade-View → DOCX

```php
use Dskripchenko\LaravelPhpDocx\Facades\Docx;

// eine gerenderte Blade-View als Word-Dokument ausliefern
Route::get('/contracts/{contract}', function (Contract $contract) {
    return Docx::view('contracts.show', ['contract' => $contract])
        ->download("contract-{$contract->number}.docx");
});

// Kopf-/Fußzeilen als eigene Views und ein Text-Wasserzeichen
Docx::view('contracts.show', $data,
    headerView: 'contracts.header',
    footerView: 'contracts.footer',
    watermark: 'ENTWURF',
);

// Views mit <style>-Blöcken bzw. CSS-Klassen (benötigt das empfohlene
// Paket tijsverkoyen/css-to-inline-styles)
Docx::view('contracts.styled', $data, inlineStyles: true);
```

### HTML → DOCX

```php
$bytes = Docx::fromHtml('<h1>Bericht</h1><p>Text mit <b>Fettdruck</b>.</p>')->bytes();

Docx::fromHtml($html)->save(storage_path('report.docx'));

// HTTP-Antworten
return Docx::fromHtml($html)->download('report.docx');   // als Anhang
return Docx::fromHtml($html)->inline('report.docx');     // im Browser

// oder über das Response-Makro — nimmt PendingDocx, Document oder Bytes
return response()->docx(Docx::fromHtml($html), 'report.docx');
```

`fromHtml()` versteht Inline-Attribute `style="…"`;
`fromHtmlWithStyles()` löst zusätzlich zuerst `<style>`-Blöcke und CSS-Klassen auf.

### DOCX → HTML (Import)

```php
$imported = Docx::toHtml(file_get_contents('from-word.docx'));

$imported->bodyHtml;        // sauberes HTML mit Inline-Styles
$imported->headerHtml;      // Kopf- und Fußzeile als HTML
$imported->footerHtml;
$imported->watermarkText;
```

Liest Dokumente aus Word, Google Docs, LibreOffice und PHPWord — die Treue wird
in der CI von php-docx an einem externen Korpus gemessen.

### Vorlagenvariablen

```php
$variables = Docx::detectVariables($docxBytes);
// MERGEFIELD, SDT-Inhaltssteuerelemente, Muster {{x}} / ${x} / %x%

// Werte schon beim Import nach HTML einsetzen
$html = Docx::toHtml($docxBytes, ['customer_name' => 'ACME Corp'])->bodyHtml;
```

### Fluent Builder

```php
$document = Docx::builder()
    ->heading(1, 'Quartalsbericht')
    ->paragraph(fn ($p) => $p->text('Erstellt für ')->bold('ACME Corp'))
    ->build();

return Docx::render($document)->download('report.docx');
```

Die vollständige Builder-API — Tabellen, Listen, Bilder, Seiteneinrichtung,
Wasserzeichen — ist in [php-docx](https://github.com/dskripchenko/php-docx)
dokumentiert.

## Konfiguration

`config/php-docx.php`:

```php
return [
    'paper' => env('PHP_DOCX_PAPER', 'a4'),            // a3 | a4 | a5 | letter | legal
    'orientation' => env('PHP_DOCX_ORIENTATION', 'portrait'),
    'margins' => [                                      // Millimeter; null = Vorgabe der Bibliothek
        'top' => null, 'right' => null, 'bottom' => null, 'left' => null,
    ],
];
```

Gilt für jedes über die Facade erzeugte Dokument; dokumentspezifische
Einstellungen über die native API haben Vorrang.

## Verwandte Pakete

- [dskripchenko/php-docx](https://github.com/dskripchenko/php-docx) — die zugrunde liegende DOCX-⇄-HTML-Engine
- [dskripchenko/php-docx-phpword](https://github.com/dskripchenko/php-docx-phpword) — Brücke zu und von PHPWords Objektmodell
- [dskripchenko/laravel-php-pdf](https://github.com/dskripchenko/laravel-php-pdf) — dasselbe Brückenmuster für PDF

## Lizenz

MIT.
