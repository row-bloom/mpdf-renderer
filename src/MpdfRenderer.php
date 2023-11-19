<?php

namespace RowBloom\MpdfRenderer;

use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use RowBloom\RowBloom\Config;
use RowBloom\RowBloom\Fs\File;
use RowBloom\RowBloom\Options;
use RowBloom\RowBloom\Renderers\Contract as RenderersContract;
use RowBloom\RowBloom\Renderers\Sizing\LengthUnit;
use RowBloom\RowBloom\Renderers\Sizing\Margin;
use RowBloom\RowBloom\Types\Css;
use RowBloom\RowBloom\Types\Html;

class MpdfRenderer implements RenderersContract
{
    public const NAME = 'mPDF';

    private string $rendering;

    private Html $html;

    private Css $css;

    private Options $options;

    private Mpdf $mpdf;

    public function __construct(protected ?Config $config = null)
    {
    }

    public function get(): string
    {
        return $this->rendering;
    }

    public function save(File $file): bool
    {
        return $file->mustBeExtension('pdf')
            ->startSaving()
            ->streamFilterAppend('convert.base64-decode')
            ->save($this->rendering);
    }

    public function render(Html $html, Css $css, Options $options, Config $config = null): static
    {
        $this->html = $html;
        $this->css = $css;
        $this->options = $options;
        $this->config = $config ?? $this->config;

        $this->mpdf = new Mpdf($this->getMargin());

        $this->setPageFormat();
        $this->setHeaderAndFooter();
        $this->setMetadata();

        $this->mpdf->WriteHTML($this->html);

        $this->mpdf->WriteHTML($this->css, HTMLParserMode::HEADER_CSS);

        $this->rendering = base64_encode($this->mpdf->OutputBinaryData());

        return $this;
    }

    public static function getOptionsSupport(): array
    {
        return [
            'displayHeaderFooter' => true,
            'headerTemplate' => true,
            'footerTemplate' => true,
            'printBackground' => false,
            'preferCssPageSize' => false,
            'landscape' => true,
            'format' => true,
            'width' => true,
            'height' => true,
            'margin' => true,
            'metadataTitle' => true,
            'metadataAuthor' => true,
            'metadataCreator' => true,
            'metadataSubject' => true,
            'metadataKeywords' => true,
        ];
    }

    // ============================================================
    // Options
    // ============================================================

    private function setPageFormat(): void
    {
        $size = $this->options->resolvePaperSize(LengthUnit::MILLIMETER_UNIT);
        $orientation = 'p';

        $this->mpdf->_setPageSize($size, $orientation);
    }

    private function getMargin(): array
    {
        $margin = Margin::fromOptions($this->options, LengthUnit::MILLIMETER_UNIT);

        return [
            'margin_top' => $margin->getRaw('marginTop'),
            'margin_right' => $margin->getRaw('marginRight'),
            'margin_bottom' => $margin->getRaw('marginBottom'),
            'margin_left' => $margin->getRaw('marginLeft'),
            // 'margin_header' => 0,
            // 'margin_footer' => 0,
        ];
    }

    private function setHeaderAndFooter(): void
    {
        if (! $this->options->displayHeaderFooter) {
            return;
        }

        if (! is_null($this->options->headerTemplate)) {
            $headerTemplate = MpdfDom::fromString($this->options->headerTemplate)
                ->translateHeaderFooterClasses()
                ->toHtml();

            $this->mpdf->SetHTMLHeader($headerTemplate);
        }

        if (! is_null($this->options->footerTemplate)) {
            $footerTemplate = MpdfDom::fromString($this->options->footerTemplate)
                ->translateHeaderFooterClasses()
                ->toHtml();

            $this->mpdf->SetHTMLHeader($footerTemplate);
        }

        $this->mpdf->SetHTMLFooter($this->options->footerTemplate);
    }

    private function setMetadata(): void
    {
        $this->mpdf->SetTitle($this->options->metadataTitle);
        $this->mpdf->SetAuthor($this->options->metadataAuthor);
        $this->mpdf->SetSubject($this->options->metadataSubject);
        $this->mpdf->SetCreator($this->options->metadataCreator);
        $this->mpdf->SetKeywords($this->options->metadataKeywords);
    }
}
