<?php


Route::get('generation', 'Paulohsilvestre\GeneratorForLaravel\Controllers\GenerationController@index');
Route::post('upload', 'Paulohsilvestre\GeneratorForLaravel\Controllers\GenerationController@upload');
Route::post('generation', 'Paulohsilvestre\GeneratorForLaravel\Controllers\GenerationController@generation');
