<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Base
Route::get('', 'BaseController@index')->name('index');

Route::get('contacts', 'BaseController@contacts')->name('contacts');
Route::post('contacts', 'BaseController@contactsForm')->name('contacts');

Route::get('cookies', 'BaseController@cookies')->name('cookies');

Route::get('search', 'BaseController@search')->name('search');

Auth::routes();

// Admin
Route::prefix('admin')->middleware(['is_admin'])->group(function () {
    Route::get('', 'AdminController@index')->name('administration');
    Route::get('', 'AdminController@index');

    Route::prefix('logins')->group(function () {
        Route::get('', 'AdminController@logins')->name('logins');
        Route::get('reset', 'AdminController@resetlogins')->name('reset-logins');
    });

    Route::prefix('visits')->group(function () {
        Route::get('', 'AdminController@visits')->name('visits');
        Route::get('reset', 'AdminController@resetVisits')->name('reset-visits');
    });
});

// Authors
Route::prefix('authors')->group(function () {
    Route::get('', 'AuthorController@index')->name('authors');

    Route::get('{id:[0-9]+}', 'AuthorController@datail')->name('author');

    Route::middleware(['auth'])->group(function () {
        Route::get('new', 'AuthorController@form')->name('new-author');
        Route::post('new', 'AuthorController@formPost');

        Route::get('edit/{id:[0-9]+}', 'AuthorController@form')->name('edit-author');
        Route::post('edit/{id:[0-9]+}', 'AuthorController@formPost');

        Route::get('delete/{id:[0-9]+}', 'AuthorController@delete')->name('delete-author');
        Route::post('delete/{id:[0-9]+}', 'AuthorController@deletePost');
    });
});

// Subjects
Route::prefix('subjects')->group(function () {
    Route::get('', 'SubjectController@index')->name('subjects');

    Route::get('{id:[0-9]+}', 'SubjectController@datail')->name('subject');

    Route::middleware(['is_admin'])->group(function () {
        Route::get('new', 'SubjectController@form')->name('new-subject');
        Route::post('new', 'SubjectController@formPost');

        Route::get('edit/{id:[0-9]+}', 'SubjectController@form')->name('edit-subject');
        Route::post('edit/{id:[0-9]+}', 'SubjectController@formPost');

        Route::get('delete/{id:[0-9]+}', 'SubjectController@delete')->name('delete-subject');
        Route::post('delete/{id:[0-9]+}', 'SubjectController@deletePost');
    });
});

// Books
Route::prefix('books')->group(function () {
    Route::get('', 'BookController@index')->name('books');

    Route::get('{id:[0-9]+}', 'BookController@datail')->name('book');

    Route::middleware(['auth'])->group(function () {
        Route::get('new', 'BookController@form')->name('new-book');
        Route::post('new', 'BookController@formPost');

        Route::get('edit/{id:[0-9]+}', 'BookController@form')->name('edit-book');
        Route::post('edit/{id:[0-9]+}', 'BookController@formPost');

        Route::get('delete/{id:[0-9]+}', 'BookController@delete')->name('delete-book');
        Route::post('delete/{id:[0-9]+}', 'BookController@deletePost');

        Route::get('ask/{id:[0-9]+}', 'BookController@ask')->name('ask-book');

        Route::get('concede/{id:[0-9]+}/{user:[0-9]+}', 'BookController@concede')->name('concede-book');
    });
});

// Users
Route::middleware(['auth'])->group(function () {
    Route::prefix('users')->middleware(['is_admin'])->group(function () {
        Route::get('', 'UserController@index')->name('users');

        Route::get('delete/{id:[0-9]+}', 'UserController@delete')->name('delete-user');
        Route::post('delete/{id:[0-9]+}', 'UserController@deletePost');

        Route::get('enable/{id:[0-9]+}', 'UserController@enable')->name('enable-user');

        Route::get('admin/{id:[0-9]+}', 'UserController@admin')->name('admin');
    });

    Route::get('profile[/{id:[0-9]+}]', 'UserController@datail')->name('profile');
});
