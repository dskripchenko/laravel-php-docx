<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Page defaults
    |--------------------------------------------------------------------------
    |
    | Applied to every document produced through the Docx facade. Paper is
    | one of: a3, a4, a5, letter, legal. Margins are in millimetres; null
    | keeps the library default (20 mm top/bottom, 15 mm left/right).
    |
    */

    'paper' => env('PHP_DOCX_PAPER', 'a4'),

    'orientation' => env('PHP_DOCX_ORIENTATION', 'portrait'),

    'margins' => [
        'top' => null,
        'right' => null,
        'bottom' => null,
        'left' => null,
    ],

];
