<?php


Route::get('generation', 'Paulohsilvestre\GeneratorForLaravel\Generation\GenerationController@index');
Route::post('upload', 'Paulohsilvestre\GeneratorForLaravel\Generation\GenerationController@upload');
Route::post('generation', 'Paulohsilvestre\GeneratorForLaravel\Generation\GenerationController@generation');
