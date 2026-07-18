<?php

declare(strict_types=1);

namespace Dskripchenko\LaravelPhpDocx\Facades;

use Dskripchenko\LaravelPhpDocx\DocxFactory;
use Dskripchenko\LaravelPhpDocx\PendingDocx;
use Dskripchenko\PhpDocx\Build\DocumentBuilder;
use Dskripchenko\PhpDocx\Document;
use Dskripchenko\PhpDocx\Html\ImportedDocument;
use Illuminate\Support\Facades\Facade;

/**
 * @method static PendingDocx fromHtml(string $body, ?string $header = null, ?string $footer = null, ?string $watermark = null)
 * @method static PendingDocx fromHtmlWithStyles(string $body, ?string $header = null, ?string $footer = null, ?string $watermark = null)
 * @method static PendingDocx view(string $view, array $data = [], ?string $headerView = null, ?string $footerView = null, ?string $watermark = null, bool $inlineStyles = false)
 * @method static DocumentBuilder builder()
 * @method static PendingDocx render(Document $document)
 * @method static ImportedDocument toHtml(string $docxBytes, array $variables = [])
 * @method static Document read(string $docxBytes)
 * @method static list<\Dskripchenko\PhpDocx\Reader\DetectedVariable> detectVariables(string $docxBytes)
 * @method static \Dskripchenko\PhpDocx\Style\PageSetup pageSetup()
 *
 * @see DocxFactory
 */
class Docx extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DocxFactory::class;
    }
}
