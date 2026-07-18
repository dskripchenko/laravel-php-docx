<?php

declare(strict_types=1);

namespace Dskripchenko\LaravelPhpDocx\Tests;

use Dskripchenko\LaravelPhpDocx\PhpDocxServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [PhpDocxServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['Docx' => \Dskripchenko\LaravelPhpDocx\Facades\Docx::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('view.paths', [__DIR__.'/views']);
    }
}
