<?php

declare(strict_types=1);

namespace Dskripchenko\LaravelPhpDocx\Tests;

use Dskripchenko\LaravelPhpDocx\Facades\Docx;
use Dskripchenko\LaravelPhpDocx\PendingDocx;
use Dskripchenko\PhpDocx\Document;
use Dskripchenko\PhpDocx\Element\Paragraph;
use Dskripchenko\PhpDocx\Element\Run;
use Dskripchenko\PhpDocx\Section;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;

final class ResponseMacroTest extends TestCase
{
    #[Test]
    public function macro_accepts_pending_docx(): void
    {
        /** @var Response $response */
        $response = response()->docx(Docx::fromHtml('<p>macro</p>'), 'report.docx');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(PendingDocx::MIME, $response->headers->get('Content-Type'));
        self::assertSame('attachment; filename="report.docx"', $response->headers->get('Content-Disposition'));
        self::assertStringStartsWith('PK', (string) $response->getContent());
    }

    #[Test]
    public function macro_accepts_ast_document_and_inline_disposition(): void
    {
        $document = new Document(new Section([new Paragraph([new Run('ast')])]));

        /** @var Response $response */
        $response = response()->docx($document, 'ast.docx', inline: true);

        self::assertSame('inline; filename="ast.docx"', $response->headers->get('Content-Disposition'));
        self::assertStringStartsWith('PK', (string) $response->getContent());
    }

    #[Test]
    public function macro_accepts_raw_bytes(): void
    {
        $bytes = Docx::fromHtml('<p>raw</p>')->bytes();

        /** @var Response $response */
        $response = response()->docx($bytes);

        self::assertSame(PendingDocx::MIME, $response->headers->get('Content-Type'));
        self::assertSame($bytes, $response->getContent());
    }

    #[Test]
    public function macro_rejects_unsupported_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        response()->docx(42);
    }

    #[Test]
    public function pending_docx_download_and_inline_helpers(): void
    {
        $docx = Docx::fromHtml('<p>helpers</p>');

        self::assertSame(
            'attachment; filename="a.docx"',
            $docx->download('a.docx')->headers->get('Content-Disposition'),
        );
        self::assertSame(
            'inline; filename="b.docx"',
            $docx->inline('b.docx')->headers->get('Content-Disposition'),
        );
    }
}
