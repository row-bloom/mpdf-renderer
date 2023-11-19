<?php

use RowBloom\MpdfRenderer\MpdfDom;

test('Dom', function () {
    $htmlString = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Random HTML Document</title>
    </head>
    <body>
        <h1 class="title">Random HTML Document</h1>
        <div class="date">Date: November 19, 2023</div>
        <a href="#" class="url">https://www.example.com</a>
        <div class="pageNumber">Page Number: 5</div>
        <div class="totalPages">Total Pages: 100</div>
    </body>
    </html>';

    $results = MpdfDom::fromString($htmlString)
        ->translateHeaderFooterClasses()
        ->toHtml();

    expect($results)
        ->toContain('{DATE j-m-Y}')
        ->toContain('{PAGENO}')
        ->toContain('{nbpg}')
        ->toContain('<h1 class="title">Random HTML Document</h1>')
        ->not->toContain('<div class="date">Date: November 19, 2023</div>')
        ->not->toContain('<div class="pageNumber">Page Number: 5</div>')
        ->not->toContain('<div class="totalPages">Total Pages: 100</div>');
});
