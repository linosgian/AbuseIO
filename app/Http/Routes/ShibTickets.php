<?php

Route::model('tickets', 'AbuseIO\Models\Ticket');
Route::resource('shibtickets', 'ShibTicketsController');

Route::group(
    [
        'prefix' => 'tickets',
        'as'     => 'tickets.',
    ],
    function () {
        /*
        | Ticket search
        */
        Route::get(
            'search/{one?}/{two?}/{three?}/{four?}/{five?}',
            [
                'middleware' => 'shibauth',
                'as'         => 'search',
                'uses'       => 'ShibTicketsController@search',
            ]
        );

        /*
        | Index tickets
        */
        Route::get(
            '',
            [
                'middleware' => 'shibauth',
                'as'         => 'index',
                'uses'       => 'ShibTicketsController@index',
            ]
        );

        /*
        | Show ticket
        */
        Route::get(
            '{tickets}',
            [
                'middleware' => 'shibauth',
                'as'         => 'show',
                'uses'       => 'ShibTicketsController@show',
            ]
        );

        /*
        | Export tickets
        */
        Route::get(
            'export/{format}',
            [
                'middleware' => 'shibauth',
                'as'         => 'export',
                'uses'       => 'ShibTicketsController@export',
            ]
        );

        /*
        | Edit ticket status
        */
        Route::group(
            [
                'prefix' => '{tickets}/status',
            ],
            function () {
                Route::get(
                    '{status}',
                    [
                        'middleware' => 'shibauth',
                        'as'         => 'status',
                        'uses'       => 'ShibTicketsController@status',
                    ]
                );
            }
        );
    }
);
