<?php

use App\Http\Controllers\PrototypePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', PrototypePageController::class)->defaults('screen', 'home')->name('home');

Route::get('/about', PrototypePageController::class)->defaults('screen', 'about')->name('about');
Route::get('/academics', PrototypePageController::class)->defaults('screen', 'academics')->name('academics');
Route::get('/admissions', PrototypePageController::class)->defaults('screen', 'admissions')->name('admissions');
Route::get('/news', PrototypePageController::class)->defaults('screen', 'news')->name('news.index');
Route::get('/news/young-learners-shine', PrototypePageController::class)->defaults('screen', 'news-detail')->name('news.show');
Route::get('/events', PrototypePageController::class)->defaults('screen', 'events')->name('events.index');
Route::get('/events/parent-orientation', PrototypePageController::class)->defaults('screen', 'event-detail')->name('events.show');
Route::get('/gallery', PrototypePageController::class)->defaults('screen', 'gallery')->name('gallery');
Route::get('/downloads', PrototypePageController::class)->defaults('screen', 'downloads')->name('downloads');
Route::get('/contact', PrototypePageController::class)->defaults('screen', 'contact')->name('contact');
Route::get('/faq', PrototypePageController::class)->defaults('screen', 'faq')->name('faq');
Route::get('/privacy', PrototypePageController::class)->defaults('screen', 'privacy')->name('privacy');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', PrototypePageController::class)->defaults('screen', 'admin-login')->name('login');
    Route::get('/dashboard', PrototypePageController::class)->defaults('screen', 'admin-dashboard')->name('dashboard');
    Route::get('/pages', PrototypePageController::class)->defaults('screen', 'admin-pages')->name('pages.index');
    Route::get('/pages/editor', PrototypePageController::class)->defaults('screen', 'admin-page-editor')->name('pages.editor');
    Route::get('/news', PrototypePageController::class)->defaults('screen', 'admin-news')->name('news.index');
    Route::get('/news/editor', PrototypePageController::class)->defaults('screen', 'admin-news-editor')->name('news.editor');
    Route::get('/events', PrototypePageController::class)->defaults('screen', 'admin-events')->name('events.index');
    Route::get('/events/editor', PrototypePageController::class)->defaults('screen', 'admin-event-editor')->name('events.editor');
    Route::get('/gallery', PrototypePageController::class)->defaults('screen', 'admin-gallery')->name('gallery.index');
    Route::get('/gallery/editor', PrototypePageController::class)->defaults('screen', 'admin-gallery-editor')->name('gallery.editor');
    Route::get('/downloads', PrototypePageController::class)->defaults('screen', 'admin-downloads')->name('downloads.index');
    Route::get('/staff', PrototypePageController::class)->defaults('screen', 'admin-staff')->name('staff.index');
    Route::get('/programmes', PrototypePageController::class)->defaults('screen', 'admin-programmes')->name('programmes.index');
    Route::get('/admissions', PrototypePageController::class)->defaults('screen', 'admin-admissions')->name('admissions.index');
    Route::get('/contact-messages', PrototypePageController::class)->defaults('screen', 'admin-contact-messages')->name('contact-messages.index');
    Route::get('/media', PrototypePageController::class)->defaults('screen', 'admin-media')->name('media.index');
    Route::get('/users', PrototypePageController::class)->defaults('screen', 'admin-users')->name('users.index');
    Route::get('/roles', PrototypePageController::class)->defaults('screen', 'admin-roles')->name('roles.index');
    Route::get('/settings', PrototypePageController::class)->defaults('screen', 'admin-settings')->name('settings');
    Route::get('/audit-log', PrototypePageController::class)->defaults('screen', 'admin-audit-log')->name('audit-log');
});
