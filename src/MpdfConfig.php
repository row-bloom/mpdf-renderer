<?php

namespace RowBloom\ChromePhpRenderer;

class MpdfConfig
{
    public function __construct(public bool $chromePdfViewerClassesHandling = false)
    {
    }
}
