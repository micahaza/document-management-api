<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function() {
    return view('welcome');
});

// Serving uploaded files
Route::get('api/v1/file/{file_cache_key}', 'FileController@getFileByCacheKey');

Route::group(['prefix' => 'api/v1', 'middleware' => 'api-token-auth'], function () {

    Route::post('documents', 'DocumentController@createDocument');
    Route::get('{user_id}/documents', 'DocumentController@getUserDocuments');
    Route::get('documents/{document}', 'DocumentController@getUserDocument');
    Route::delete('documents/{document}', 'DocumentController@deleteUserDocument');

    Route::get('documents/{document}/comments', 'CommentController@getDocumentComments');
    Route::post('documents/{document}/comments', 'CommentController@createDocumentComment');

    Route::post('documents/{document}/files', 'DocumentController@addFileToDocument');
    Route::get('documents/{document}/files', 'DocumentController@getFilesOfDocument');

    Route::delete('comments/{comment}', 'CommentController@deleteComment');

    // Get file
    Route::get('files/{file}', 'FileController@getFile');

    // updating statuses
    Route::patch('files/{file}', 'FileController@updateFileStatus');
    Route::patch('documents/{document}', 'DocumentController@updateDocumentStatus');

    // Replacing file
    Route::put('files/{file}', 'FileController@replaceFile');

    // File comments
    Route::post('files/{file}/comments', 'CommentController@createFileComment');
    Route::get('files/{file}/comments', 'CommentController@getFileComments');

    Route::delete('files/{file}', 'FileController@deleteFile');
});

