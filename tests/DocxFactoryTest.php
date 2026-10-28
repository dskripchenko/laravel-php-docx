<?php

declare(strict_types=1);

namespace Dskripchenko\LaravelPhpDocx\Tests;

use Dskripchenko\LaravelPhpDocx\Facades\Docx;
use Dskripchenko\PhpDocx\Style\Orientation;
use Dskripchenko\PhpDocx\Style\PaperSize;
use PHPUnit\Framework\Attributes\Test;

final class DocxFactoryTest extends TestCase
{
    #[Test]
    public function from_html_produces_a_docx_package(): void
    {
        $bytes = Docx::fromHtml('<h1>Report</h1><p>Body with <b>bold</b> and кириллица.</p>')->bytes();

        self::assertStringStartsWith('PK', $bytes);
        self::assertStringContainsString('word/document.xml', $bytes);
    }

    #[Test]
    public function config_drives_the_page_setup(): void
    {
        config()->set('php-docx', [
            'paper' => 'letter',
            'orientation' => 'landscape',
            'margins' => ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10],
        ]);
        $this->app->forgetInstance(\Dskripchenko\LaravelPhpDocx\DocxFactory::class);
        Docx::clearResolvedInstances();

        $setup = Docx::pageSetup();

        self::assertSame(PaperSize::Letter, $setup->paperSize);
        self::assertSame(Orientation::Landscape, $setup->orientation);
        self::assertSame((int) round(10 * 56.6929), $setup->marginTopTwips);
    }

    #[Test]
    public function blade_view_renders_to_docx_with_footer(): void
    {
        $docx = Docx::view(
            'contract',
            ['number' => '42-A', 'customer' => 'ACME Corp'],
            footerView: 'footer',
        );

        $html = Docx::toHtml($docx->bytes());
        self::assertStringContainsString('Contract 42-A', $html->bodyHtml);
        self::assertStringContainsString('<strong>ACME Corp</strong>', $html->bodyHtml);
        self::assertStringContainsString('Page footer for ACME Corp', $html->footerHtml);
    }

    #[Test]
    public function inline_styles_view_resolves_css_classes(): void
    {
        $docx = Docx::view('styled', ['customer' => 'ACME Corp'], inlineStyles: true);

        $html = Docx::toHtml($docx->bytes());
        self::assertMatchesRegularExpression('@color:\s*#c0392b@i', $html->bodyHtml);
    }

    #[Test]
    public function builder_and_render_round_trip(): void
    {
        $document = Docx::builder()
            ->heading(1, 'Built')
            ->paragraph(fn ($p) => $p->text('From the fluent builder.'))
            ->build();

        $html = Docx::toHtml(Docx::render($document)->bytes());
        self::assertStringContainsString('Built', $html->bodyHtml);
        self::assertStringContainsString('From the fluent builder.', $html->bodyHtml);
    }

    #[Test]
    public function builder_starts_on_the_page_from_the_config(): void
    {
        config()->set('php-docx', ['paper' => 'a5', 'orientation' => 'landscape', 'margins' => ['top' => 10]]);
        $this->app->forgetInstance(\Dskripchenko\LaravelPhpDocx\DocxFactory::class);
        Docx::clearResolvedInstances();

        $document = Docx::builder()
            ->paragraph('Config page')
            ->newSection(start: \Dskripchenko\PhpDocx\Style\SectionStart::Continuous)
            ->paragraph('Same page setup')
            ->build();

        foreach ($document->sections() as $section) {
            self::assertSame(PaperSize::A5, $section->pageSetup->paperSize);
            self::assertSame(Orientation::Landscape, $section->pageSetup->orientation);
            self::assertSame((int) round(10 * 56.6929), $section->pageSetup->marginTopTwips);
        }
        self::assertCount(2, $document->sections());
    }

    #[Test]
    public function detect_variables_finds_placeholders(): void
    {
        $bytes = Docx::fromHtml('<p>Hello {{customer_name}}, total: ${total}</p>')->bytes();

        $names = array_map(fn ($v) => $v->name, Docx::detectVariables($bytes));

        self::assertContains('customer_name', $names);
        self::assertContains('total', $names);
    }

    #[Test]
    public function save_writes_the_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'laravel-docx-').'.docx';
        try {
            $written = Docx::fromHtml('<p>saved</p>')->save($path);

            self::assertGreaterThan(0, $written);
            self::assertStringStartsWith('PK', (string) file_get_contents($path));
        } finally {
            @unlink($path);
        }
    }
}
