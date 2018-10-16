<?php


Route::get('generation', 'GeneratorForLaravel\Generation\GenerationController@index');
Route::post('upload', 'GeneratorForLaravel\Generation\GenerationController@upload');
Route::post('generation', 'GeneratorForLaravel\Generation\GenerationController@generation');
