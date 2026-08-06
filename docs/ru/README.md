# dskripchenko/laravel-php-docx

> Мост для Laravel к [`dskripchenko/php-docx`](https://github.com/dskripchenko/php-docx) —
> набору инструментов DOCX ⇄ HTML без зависимостей, под лицензией **MIT**.
> Blade-шаблоны прямо в документы Word, импорт DOCX обратно в HTML, поиск
> переменных шаблона. Результат
> [сверяется со схемами ECMA-376 при каждом пуше](https://github.com/dskripchenko/php-docx/blob/main/docs/CONFORMANCE.md),
> а точность чтения
> [измеряется на внешнем корпусе документов](https://github.com/dskripchenko/php-docx/blob/main/docs/READER-FIDELITY.md).

> 🌐 [English](../../README.md) · [Deutsch](../de/README.md) · **Русский** · [中文](../zh/README.md)

[![Tests](https://img.shields.io/github/actions/workflow/status/dskripchenko/laravel-php-docx/tests.yml?branch=main&label=tests&logo=github)](https://github.com/dskripchenko/laravel-php-docx/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/dskripchenko/laravel-php-docx?logo=packagist&logoColor=white)](https://packagist.org/packages/dskripchenko/laravel-php-docx)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](../../LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-red.svg)](https://laravel.com)

## Установка

```bash
composer require dskripchenko/laravel-php-docx
```

Провайдер и фасад `Docx` регистрируются автоматически (package discovery).
Конфиг при желании публикуется:

```bash
php artisan vendor:publish --tag=php-docx-config
```

## Использование

### Blade-шаблон → DOCX

```php
use Dskripchenko\LaravelPhpDocx\Facades\Docx;

// отдать отрисованный Blade-шаблон документом Word
Route::get('/contracts/{contract}', function (Contract $contract) {
    return Docx::view('contracts.show', ['contract' => $contract])
        ->download("contract-{$contract->number}.docx");
});

// колонтитулы отдельными шаблонами и текстовая подложка
Docx::view('contracts.show', $data,
    headerView: 'contracts.header',
    footerView: 'contracts.footer',
    watermark: 'ЧЕРНОВИК',
);

// шаблоны с блоками <style> и CSS-классами (нужен рекомендуемый
// пакет tijsverkoyen/css-to-inline-styles)
Docx::view('contracts.styled', $data, inlineStyles: true);
```

### HTML → DOCX

```php
$bytes = Docx::fromHtml('<h1>Отчёт</h1><p>Текст с <b>выделением</b>.</p>')->bytes();

Docx::fromHtml($html)->save(storage_path('report.docx'));

// HTTP-ответы
return Docx::fromHtml($html)->download('report.docx');   // вложением
return Docx::fromHtml($html)->inline('report.docx');     // в браузере

// или макросом ответа — принимает PendingDocx, Document или байты
return response()->docx(Docx::fromHtml($html), 'report.docx');
```

`fromHtml()` понимает инлайновые атрибуты `style="…"`, а
`fromHtmlWithStyles()` дополнительно сначала раскрывает блоки `<style>` и
CSS-классы.

### DOCX → HTML (импорт)

```php
$imported = Docx::toHtml(file_get_contents('from-word.docx'));

$imported->bodyHtml;        // чистый HTML с инлайновыми стилями
$imported->headerHtml;      // колонтитулы как HTML
$imported->footerHtml;
$imported->watermarkText;
```

Читает документы из Word, Google Docs, LibreOffice и PHPWord; точность
измеряется на внешнем корпусе в CI пакета php-docx.

### Переменные шаблона

```php
$variables = Docx::detectVariables($docxBytes);
// MERGEFIELD, элементы управления SDT, шаблоны {{x}} / ${x} / %x%

// подставить значения прямо при импорте в HTML
$html = Docx::toHtml($docxBytes, ['customer_name' => 'ACME Corp'])->bodyHtml;
```

### Текучий билдер

```php
$document = Docx::builder()
    ->heading(1, 'Квартальный отчёт')
    ->paragraph(fn ($p) => $p->text('Подготовлено для ')->bold('ACME Corp'))
    ->build();

return Docx::render($document)->download('report.docx');
```

Полный API билдера — таблицы, списки, изображения, параметры страницы,
подложки — описан в [php-docx](https://github.com/dskripchenko/php-docx).

## Настройка

`config/php-docx.php`:

```php
return [
    'paper' => env('PHP_DOCX_PAPER', 'a4'),            // a3 | a4 | a5 | letter | legal
    'orientation' => env('PHP_DOCX_ORIENTATION', 'portrait'),
    'margins' => [                                      // миллиметры; null — умолчание библиотеки
        'top' => null, 'right' => null, 'bottom' => null, 'left' => null,
    ],
];
```

Применяется к каждому документу, созданному через фасад; настройки конкретного
документа через нативный API имеют приоритет.

## Соседние пакеты

- [dskripchenko/php-docx](https://github.com/dskripchenko/php-docx) — сам движок DOCX ⇄ HTML
- [dskripchenko/php-docx-phpword](https://github.com/dskripchenko/php-docx-phpword) — мост к объектной модели PHPWord и обратно
- [dskripchenko/laravel-php-pdf](https://github.com/dskripchenko/laravel-php-pdf) — такой же мост, только для PDF

## Лицензия

MIT.
