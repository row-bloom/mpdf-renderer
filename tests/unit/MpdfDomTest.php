<?php

use RowBloom\MpdfRenderer\MpdfDom;

test('Dom', function () {
    $htmlString = '
        <span class="title"></span>
        <span class="date"></span>
        <span class="url"></span>
        <span class="pageNumber"></span>
        <span class="totalPages"></span>
    ';

    $results = MpdfDom::fromString($htmlString)
        ->translateHeaderFooterClasses()
        ->toHtml();

    expect($results)->not->toContain('body')
        ->toContain('{PAGENO}')
        ->toContain('{nbpg}')
        ->toContain('<span class="title"></span>')
        ->not->toContain('{DATE j-m-Y}')
        ->not->toContain('<span class="date"></span>')
        ->not->toContain('<span class="pageNumber"></span>')
        ->not->toContain('<span class="totalPages"></span>');
});
