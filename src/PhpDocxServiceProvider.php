<?php

declare(strict_types=1);

namespace Dskripchenko\LaravelPhpDocx;

use Dskripchenko\PhpDocx\Document;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\ServiceProvider;

class PhpDocxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/php-docx.php', 'php-docx');

        $this->app->singleton(DocxFactory::class, function ($app): DocxFactory {
            return new DocxFactory(
                (array) $app['config']->get('php-docx', []),
                $app->make(ViewFactory::class),
            );
        });
        $this->app->alias(DocxFactory::class, 'php-docx');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/php-docx.php' => $this->app->configPath('php-docx.php'),
            ], 'php-docx-config');
        }

        $this->registerResponseMacro();
    }

    /**
     * response()->docx($document, 'contract.docx')
     *
     * Accepts a PendingDocx, a php-docx Document, or raw DOCX bytes.
     */
    private function registerResponseMacro(): void
    {
        if (ResponseFacade::hasMacro('docx')) {
            return;
        }

        ResponseFacade::macro('docx', function (
            mixed $docx,
            string $filename = 'document.docx',
            bool $inline = false,
        ): Response {
            $app = app();
            $bytes = match (true) {
                $docx instanceof PendingDocx => $docx->bytes(),
                $docx instanceof Document => $app->make(DocxFactory::class)->render($docx)->bytes(),
                is_string($docx) => $docx,
                default => throw new \InvalidArgumentException(
                    'response()->docx() expects PendingDocx, Document, or DOCX bytes.',
                ),
            };

            return new Response($bytes, 200, [
                'Content-Type' => PendingDocx::MIME,
                'Content-Length' => (string) strlen($bytes),
                'Content-Disposition' => sprintf(
                    '%s; filename="%s"',
                    $inline ? 'inline' : 'attachment',
                    addslashes($filename),
                ),
            ]);
        });
    }
}
