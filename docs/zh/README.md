# dskripchenko/laravel-php-docx

> [`dskripchenko/php-docx`](https://github.com/dskripchenko/php-docx) 的 Laravel 桥接包 ——
> 一个零依赖、**MIT 许可**的 DOCX ⇄ HTML 工具箱。
> 把 Blade 视图直接变成 Word 文档，把 DOCX 导回 HTML，并识别模板变量。
> 输出结果[每次推送都会对照 ECMA-376 架构校验](https://github.com/dskripchenko/php-docx/blob/main/docs/CONFORMANCE.md)，
> 读取保真度则[在外部语料集上持续跟踪](https://github.com/dskripchenko/php-docx/blob/main/docs/READER-FIDELITY.md)。

> 🌐 [English](../../README.md) · [Deutsch](../de/README.md) · [Русский](../ru/README.md) · **中文**

[![Tests](https://img.shields.io/github/actions/workflow/status/dskripchenko/laravel-php-docx/tests.yml?branch=main&label=tests&logo=github)](https://github.com/dskripchenko/laravel-php-docx/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/dskripchenko/laravel-php-docx?logo=packagist&logoColor=white)](https://packagist.org/packages/dskripchenko/laravel-php-docx)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](../../LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-red.svg)](https://laravel.com)

## 安装

```bash
composer require dskripchenko/laravel-php-docx
```

服务提供者与 `Docx` 门面会自动注册（package discovery）。如需修改配置，可以发布它：

```bash
php artisan vendor:publish --tag=php-docx-config
```

## 用法

### Blade 视图 → DOCX

```php
use Dskripchenko\LaravelPhpDocx\Facades\Docx;

// 把渲染后的 Blade 视图作为 Word 文档下载
Route::get('/contracts/{contract}', function (Contract $contract) {
    return Docx::view('contracts.show', ['contract' => $contract])
        ->download("contract-{$contract->number}.docx");
});

// 页眉/页脚各用一个视图，并加上文字水印
Docx::view('contracts.show', $data,
    headerView: 'contracts.header',
    footerView: 'contracts.footer',
    watermark: '草稿',
);

// 含 <style> 块或 CSS 类的视图（需要可选依赖
// tijsverkoyen/css-to-inline-styles）
Docx::view('contracts.styled', $data, inlineStyles: true);
```

### HTML → DOCX

```php
$bytes = Docx::fromHtml('<h1>报告</h1><p>正文中有<b>加粗</b>。</p>')->bytes();

Docx::fromHtml($html)->save(storage_path('report.docx'));

// HTTP 响应
return Docx::fromHtml($html)->download('report.docx');   // 作为附件
return Docx::fromHtml($html)->inline('report.docx');     // 浏览器内打开

// 也可以用响应宏——接受 PendingDocx、Document 或原始字节
return response()->docx(Docx::fromHtml($html), 'report.docx');
```

`fromHtml()` 能识别行内的 `style="…"` 属性；
`fromHtmlWithStyles()` 会先额外解析 `<style>` 块与 CSS 类。

### DOCX → HTML（导入）

```php
$imported = Docx::toHtml(file_get_contents('from-word.docx'));

$imported->bodyHtml;        // 带行内样式的干净 HTML
$imported->headerHtml;      // 页眉/页脚的 HTML
$imported->footerHtml;
$imported->watermarkText;
```

可读取由 Word、Google Docs、LibreOffice 和 PHPWord 生成的文档——保真度在
php-docx 的 CI 中以外部语料集持续跟踪。

### 模板变量

```php
$variables = Docx::detectVariables($docxBytes);
// MERGEFIELD、SDT 内容控件，以及 {{x}} / ${x} / %x% 形式

// 在导入 HTML 的同时替换取值
$html = Docx::toHtml($docxBytes, ['customer_name' => 'ACME Corp'])->bodyHtml;
```

### 链式构建器

```php
$document = Docx::builder()
    ->heading(1, '季度报告')
    ->paragraph(fn ($p) => $p->text('呈送 ')->bold('ACME Corp'))
    ->build();

return Docx::render($document)->download('report.docx');
```

完整的构建器 API（表格、列表、图片、页面设置、水印）见
[php-docx](https://github.com/dskripchenko/php-docx)。

## 配置

`config/php-docx.php`：

```php
return [
    'paper' => env('PHP_DOCX_PAPER', 'a4'),            // a3 | a4 | a5 | letter | legal
    'orientation' => env('PHP_DOCX_ORIENTATION', 'portrait'),
    'margins' => [                                      // 毫米；null 表示使用库的默认值
        'top' => null, 'right' => null, 'bottom' => null, 'left' => null,
    ],
];
```

它作用于通过门面生成的每一份文档；用原生 API 为单份文档所做的设置优先。

## 相关包

- [dskripchenko/php-docx](https://github.com/dskripchenko/php-docx) —— 底层的 DOCX ⇄ HTML 引擎
- [dskripchenko/php-docx-phpword](https://github.com/dskripchenko/php-docx-phpword) —— 与 PHPWord 对象模型互通的桥接
- [dskripchenko/laravel-php-pdf](https://github.com/dskripchenko/laravel-php-pdf) —— 同样模式的 PDF 桥接

## 许可证

MIT。
