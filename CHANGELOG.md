# Changelog

All notable changes to `dskripchenko/laravel-php-docx` are documented in
this file. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versioning
follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-07-18

Initial release.

### Added
- `Docx` facade over a config-driven `DocxFactory` (auto-discovered
  service provider, publishable `config/php-docx.php` with paper,
  orientation and margins).
- `Docx::view()` — Blade views straight to DOCX, with optional header
  and footer views, text watermark, and `inlineStyles:` for views
  carrying `<style>` blocks or CSS classes.
- `Docx::fromHtml()` / `Docx::fromHtmlWithStyles()` returning a
  `PendingDocx` with `bytes()`, `save()`, `download()`, `inline()` and
  `response()`.
- `response()->docx($docx, $filename, $inline)` macro accepting a
  `PendingDocx`, a php-docx `Document`, or raw DOCX bytes.
- Import side: `Docx::toHtml()` (with optional variable substitution),
  `Docx::read()` and `Docx::detectVariables()`.
- `Docx::builder()` / `Docx::render()` for the fluent php-docx builder.
- Testbench suite across PHP 8.2–8.5 × Laravel 11/12/13.
