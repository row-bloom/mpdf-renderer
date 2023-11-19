<?php

use RowBloom\MpdfRenderer\MpdfDom;

it('substitutes special class elements', function () {
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
        ->toContain(date('d/m/Y'))
        ->not->toContain('<span class="date"></span>')
        ->not->toContain('<span class="pageNumber"></span>')
        ->not->toContain('<span class="totalPages"></span>');
});

it('outputs same string for {PAGENO}/{nbpg}', function () {
    $output1 = MpdfDom::fromString('{PAGENO}/{nbpg}')
        ->translateHeaderFooterClasses()
        ->toHtml();

    $output2 = MpdfDom::fromString('<p><span class="pageNumber"></span>/<span class="totalPages"></span></p>')
        ->translateHeaderFooterClasses()
        ->toHtml();

    expect($output1)->toBe($output2);

    expect(strcmp($output1, $output2))->toBe(0);
});
