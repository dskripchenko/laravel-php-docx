# dskripchenko/laravel-php-docx

> Laravel bridge for [`dskripchenko/php-docx`](https://github.com/dskripchenko/php-docx) —
> the zero-dependency, **MIT-licensed** DOCX ⇄ HTML toolkit.
> Blade views straight to Word documents, DOCX import back to HTML,
> template-variable detection — with output
> [validated against the ECMA-376 schemas on every push](https://github.com/dskripchenko/php-docx/blob/main/docs/CONFORMANCE.md)
> and reader fidelity
> [tracked on an external corpus](https://github.com/dskripchenko/php-docx/blob/main/docs/READER-FIDELITY.md).

> 🌐 **English** · [Deutsch](docs/de/README.md) · [Русский](docs/ru/README.md) · [中文](docs/zh/README.md)

[![Tests](https://img.shields.io/github/actions/workflow/status/dskripchenko/laravel-php-docx/tests.yml?branch=main&label=tests&logo=github)](https://github.com/dskripchenko/laravel-php-docx/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/dskripchenko/laravel-php-docx?logo=packagist&logoColor=white)](https://packagist.org/packages/dskripchenko/laravel-php-docx)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-red.svg)](https://laravel.com)

## Installation

```bash
composer require dskripchenko/laravel-php-docx
```

The service provider and the `Docx` facade register automatically
(package discovery). Optionally publish the config:

```bash
php artisan vendor:publish --tag=php-docx-config
```

## Usage

### Blade view → DOCX

```php
use Dskripchenko\LaravelPhpDocx\Facades\Docx;

// download a rendered Blade view as a Word document
Route::get('/contracts/{contract}', function (Contract $contract) {
    return Docx::view('contracts.show', ['contract' => $contract])
        ->download("contract-{$contract->number}.docx");
});

// header/footer views and a text watermark
Docx::view('contracts.show', $data,
    headerView: 'contracts.header',
    footerView: 'contracts.footer',
    watermark: 'DRAFT',
);

// views with <style> blocks / CSS classes (needs the suggested
// tijsverkoyen/css-to-inline-styles package)
Docx::view('contracts.styled', $data, inlineStyles: true);
```

### HTML → DOCX

```php
$bytes = Docx::fromHtml('<h1>Report</h1><p>Body with <b>bold</b>.</p>')->bytes();

Docx::fromHtml($html)->save(storage_path('report.docx'));

// HTTP responses
return Docx::fromHtml($html)->download('report.docx');   // attachment
return Docx::fromHtml($html)->inline('report.docx');     // inline

// or via the response macro — accepts PendingDocx, Document, or bytes
return response()->docx(Docx::fromHtml($html), 'report.docx');
```

`fromHtml()` understands inline `style="…"` attributes;
`fromHtmlWithStyles()` additionally resolves `<style>` blocks and CSS
classes first.

### DOCX → HTML (import)

```php
$imported = Docx::toHtml(file_get_contents('from-word.docx'));

$imported->bodyHtml;        // clean HTML with inline styles
$imported->headerHtml;      // header / footer as HTML
$imported->footerHtml;
$imported->watermarkText;
```

Reads documents produced by Word, Google Docs, LibreOffice, PHPWord —
fidelity is tracked on an external corpus in php-docx's CI.

### Template variables

```php
$variables = Docx::detectVariables($docxBytes);
// MERGEFIELD, SDT content controls, {{x}} / ${x} / %x% patterns

// substitute values while importing to HTML
$html = Docx::toHtml($docxBytes, ['customer_name' => 'ACME Corp'])->bodyHtml;
```

### Fluent builder

```php
$document = Docx::builder()
    ->heading(1, 'Quarterly report')
    ->paragraph(fn ($p) => $p->text('Prepared for ')->bold('ACME Corp'))
    ->build();

return Docx::render($document)->download('report.docx');
```

The full builder API (tables, lists, images, page setup, watermarks) is
documented in [php-docx](https://github.com/dskripchenko/php-docx).

## Configuration

`config/php-docx.php`:

```php
return [
    'paper' => env('PHP_DOCX_PAPER', 'a4'),            // a3 | a4 | a5 | letter | legal
    'orientation' => env('PHP_DOCX_ORIENTATION', 'portrait'),
    'margins' => [                                      // millimetres; null = library default
        'top' => null, 'right' => null, 'bottom' => null, 'left' => null,
    ],
];
```

Applied to every document produced through the facade; per-document
setups via the native API win.

## Related packages

- [dskripchenko/php-docx](https://github.com/dskripchenko/php-docx) — the underlying DOCX ⇄ HTML engine
- [dskripchenko/php-docx-phpword](https://github.com/dskripchenko/php-docx-phpword) — bridge to/from PHPWord's object model
- [dskripchenko/laravel-php-pdf](https://github.com/dskripchenko/laravel-php-pdf) — the same bridge pattern for PDF

## License

MIT.
