<?php

declare(strict_types=1);

namespace Dskripchenko\LaravelPhpDocx;

use Dskripchenko\PhpDocx\Build\DocumentBuilder;
use Dskripchenko\PhpDocx\Document;
use Dskripchenko\PhpDocx\Html\Converter;
use Dskripchenko\PhpDocx\Html\ImportedDocument;
use Dskripchenko\PhpDocx\Html\Serializer;
use Dskripchenko\PhpDocx\Reader\DocxPackageReader;
use Dskripchenko\PhpDocx\Reader\DocxReader;
use Dskripchenko\PhpDocx\Reader\VariableDetector;
use Dskripchenko\PhpDocx\Style\Orientation;
use Dskripchenko\PhpDocx\Style\PageSetup;
use Dskripchenko\PhpDocx\Style\PaperSize;
use Illuminate\Contracts\View\Factory as ViewFactory;

/**
 * Entry point behind the Docx facade. Turns the php-docx config file into
 * a ready PageSetup (paper, orientation, margins), so application code
 * deals with content only.
 */
class DocxFactory
{
    /**
     * @param  array<string, mixed>  $config  The `php-docx` config array.
     */
    public function __construct(
        private readonly array $config = [],
        private readonly ?ViewFactory $views = null,
    ) {}

    /**
     * Parse HTML (inline `style="..."` attributes) into a document.
     */
    public function fromHtml(
        string $body,
        ?string $header = null,
        ?string $footer = null,
        ?string $watermark = null,
    ): PendingDocx {
        $document = (new Converter($this->pageSetup()))
            ->fromHtml($body, $header, $footer, watermarkText: $watermark);

        return new PendingDocx($document);
    }

    /**
     * Like {@see fromHtml()}, but resolves `<style>` blocks and CSS
     * classes first (requires the suggested
     * `tijsverkoyen/css-to-inline-styles` package).
     */
    public function fromHtmlWithStyles(
        string $body,
        ?string $header = null,
        ?string $footer = null,
        ?string $watermark = null,
    ): PendingDocx {
        $document = (new Converter($this->pageSetup()))
            ->fromHtmlWithStyles($body, $header, $footer, watermarkText: $watermark);

        return new PendingDocx($document);
    }

    /**
     * Render Blade views straight to DOCX. Header and footer views (when
     * given) receive the same data. Set $inlineStyles when the views
     * carry `<style>` blocks or class-based styling.
     *
     * @param  array<string, mixed>  $data
     */
    public function view(
        string $view,
        array $data = [],
        ?string $headerView = null,
        ?string $footerView = null,
        ?string $watermark = null,
        bool $inlineStyles = false,
    ): PendingDocx {
        $views = $this->views ?? app(ViewFactory::class);
        $render = static fn (?string $name): ?string => $name === null
            ? null
            : $views->make($name, $data)->render();

        $method = $inlineStyles ? 'fromHtmlWithStyles' : 'fromHtml';

        return $this->{$method}(
            (string) $render($view),
            $render($headerView),
            $render($footerView),
            $watermark,
        );
    }

    /**
     * Fluent builder for programmatic documents. Wrap the result with
     * {@see render()} to produce a response-ready DOCX.
     */
    public function builder(): DocumentBuilder
    {
        return DocumentBuilder::new();
    }

    /**
     * Wrap a native php-docx document.
     */
    public function render(Document $document): PendingDocx
    {
        return new PendingDocx($document);
    }

    /**
     * DOCX bytes (Word, Google Docs, LibreOffice, PHPWord …) → HTML.
     * Returns body/header/footer HTML plus the watermark text.
     *
     * @param  array<string, string>  $variables  Placeholder values substituted during serialization.
     */
    public function toHtml(string $docxBytes, array $variables = []): ImportedDocument
    {
        return (new Serializer)->serialize((new DocxReader)->read($docxBytes), $variables);
    }

    /**
     * DOCX bytes → php-docx AST, for pipelines that continue natively.
     */
    public function read(string $docxBytes): Document
    {
        return (new DocxReader)->read($docxBytes);
    }

    /**
     * Template-variable detection (MERGEFIELD, SDT content controls,
     * `{{x}}` / `${x}` / `%x%` text patterns) over DOCX bytes.
     *
     * @return list<\Dskripchenko\PhpDocx\Reader\DetectedVariable>
     */
    public function detectVariables(string $docxBytes): array
    {
        return (new VariableDetector)->detect((new DocxPackageReader)->read($docxBytes));
    }

    /**
     * The PageSetup assembled from config.
     */
    public function pageSetup(): PageSetup
    {
        $paper = match (strtolower((string) ($this->config['paper'] ?? 'a4'))) {
            'a3' => PaperSize::A3,
            'a5' => PaperSize::A5,
            'letter' => PaperSize::Letter,
            'legal' => PaperSize::Legal,
            default => PaperSize::A4,
        };

        $margins = (array) ($this->config['margins'] ?? []);

        return PageSetup::fromMm(
            paperSize: $paper,
            orientation: strtolower((string) ($this->config['orientation'] ?? 'portrait')) === 'landscape'
                ? Orientation::Landscape
                : Orientation::Portrait,
            marginTopMm: (float) ($margins['top'] ?? 20),
            marginRightMm: (float) ($margins['right'] ?? 15),
            marginBottomMm: (float) ($margins['bottom'] ?? 20),
            marginLeftMm: (float) ($margins['left'] ?? 15),
        );
    }
}
