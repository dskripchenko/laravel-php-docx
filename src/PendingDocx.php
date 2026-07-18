<?php

declare(strict_types=1);

namespace Dskripchenko\LaravelPhpDocx;

use Dskripchenko\PhpDocx\Document;
use Dskripchenko\PhpDocx\Writer\Word2007Writer;
use Illuminate\Http\Response;

/**
 * A document ready to be written as bytes, a file, or an HTTP response.
 */
class PendingDocx
{
    public const MIME = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private ?string $bytes = null;

    public function __construct(private readonly Document $document) {}

    public function bytes(): string
    {
        return $this->bytes ??= (new Word2007Writer)->write($this->document);
    }

    /**
     * @return int  Bytes written.
     */
    public function save(string $path): int
    {
        $written = file_put_contents($path, $this->bytes());
        if ($written === false) {
            throw new \RuntimeException("Cannot write DOCX to: $path");
        }

        return $written;
    }

    /**
     * Inline HTTP response (browsers usually download DOCX anyway, but
     * office-viewer iframes and WebDAV clients honour inline).
     */
    public function inline(string $filename = 'document.docx'): Response
    {
        return $this->response($filename, inline: true);
    }

    /**
     * Attachment HTTP response (browser downloads the file).
     */
    public function download(string $filename = 'document.docx'): Response
    {
        return $this->response($filename, inline: false);
    }

    public function response(string $filename = 'document.docx', bool $inline = false): Response
    {
        $bytes = $this->bytes();

        return new Response($bytes, 200, [
            'Content-Type' => self::MIME,
            'Content-Length' => (string) strlen($bytes),
            'Content-Disposition' => sprintf(
                '%s; filename="%s"',
                $inline ? 'inline' : 'attachment',
                addslashes($filename),
            ),
        ]);
    }

    /**
     * The underlying document, for native-API features.
     */
    public function document(): Document
    {
        return $this->document;
    }
}
