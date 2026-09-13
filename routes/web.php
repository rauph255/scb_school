<?php

use App\Http\Controllers\AccountProfileController;
use App\Http\Controllers\AdminAdmissionEnquiryController;
use App\Http\Controllers\AdminAuditLogController;
use App\Http\Controllers\AdminContactMessageController;
use App\Http\Controllers\AdminDownloadController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\AdminExperienceController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\AdminGalleryController;
use App\Http\Controllers\AdminMediaController;
use App\Http\Controllers\AdminPageBlockController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\AdminProgrammeController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminStaffMemberController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PortalAuthController;
use App\Http\Controllers\PublicContentPageController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\PublicRedirectController;
use App\Http\Controllers\PublicSearchController;
use App\Http\Controllers\PublicSeoController;
use App\Http\Controllers\StaffContributionController;
use App\Http\Controllers\StaffPortalController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/', HomePageController::class)->name('home');
Route::get('/search', PublicSearchController::class)->middleware('throttle:search')->name('search');
Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('robots');

Route::get('/about', [PublicContentPageController::class, 'about'])->name('about');
Route::get('/academics', [PublicContentPageController::class, 'academics'])->name('academics');
Route::get('/admissions', [PublicContentPageController::class, 'admissions'])->name('admissions');
Route::post('/admissions/enquiries', [PublicContentPageController::class, 'storeAdmissionEnquiry'])
    ->middleware('throttle:public-forms')
    ->name('admissions.enquiries.store');
Route::get('/news', [PublicContentPageController::class, 'news'])->name('news.index');
Route::get('/news/{post:slug}', [PublicContentPageController::class, 'newsShow'])->name('news.show');
Route::get('/events', [PublicContentPageController::class, 'events'])->name('events.index');
Route::get('/events/{event:slug}/calendar.ics', [PublicContentPageController::class, 'eventCalendar'])->name('events.calendar');
Route::get('/events/{event:slug}', [PublicContentPageController::class, 'eventShow'])->name('events.show');
Route::get('/gallery', [PublicContentPageController::class, 'gallery'])->name('gallery');
Route::get('/downloads', [PublicContentPageController::class, 'downloads'])->name('downloads');
Route::get('/downloads/{download:slug}', [PublicMediaController::class, 'download'])->middleware('throttle:downloads')->name('downloads.show');
Route::get('/media/{media:uuid}', [PublicMediaController::class, 'show'])
    ->withoutMiddleware([AddQueuedCookiesToResponse::class, StartSession::class, ShareErrorsFromSession::class, ValidateCsrfToken::class])
    ->middleware('throttle:media')
    ->name('media.show');
Route::get('/media/{media:uuid}/image/{width}', [PublicMediaController::class, 'image'])
    ->whereNumber('width')
    ->withoutMiddleware([AddQueuedCookiesToResponse::class, StartSession::class, ShareErrorsFromSession::class, ValidateCsrfToken::class])
    ->middleware('throttle:media')
    ->name('media.image');
Route::get('/contact', [PublicContentPageController::class, 'contact'])->name('contact');
Route::post('/contact/messages', [PublicContentPageController::class, 'storeContactMessage'])
    ->middleware('throttle:public-forms')
    ->name('contact.messages.store');
Route::get('/faq', [PublicContentPageController::class, 'faq'])->name('faq');
Route::get('/privacy', [PublicContentPageController::class, 'privacy'])->name('privacy');

Route::get('/password/forgot', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/password/forgot', [PasswordResetController::class, 'sendLink'])
    ->middleware('throttle:3,1')
    ->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:authentication')
    ->name('password.update');

Route::get('/staff-portal/login', [StaffPortalController::class, 'login'])->name('staff.login');
Route::post('/staff-portal/login', [PortalAuthController::class, 'staffLogin'])
    ->middleware('throttle:authentication')
    ->name('staff.login.store');
Route::post('/staff-portal/logout', [PortalAuthController::class, 'staffLogout'])->name('staff.logout');
Route::get('/staff-portal/logout', [PortalAuthController::class, 'rejectGetLogout'])->name('staff.logout.invalid');
Route::get('/staff-portal', [StaffPortalController::class, 'dashboard'])
    ->middleware('portal.role:staff')
    ->name('staff.dashboard');
Route::post('/staff-portal/contributions', [StaffContributionController::class, 'store'])
    ->middleware(['portal.role:staff', 'throttle:5,1'])
    ->name('staff.contributions.store');
Route::patch('/staff-portal/profile', [AccountProfileController::class, 'updateStaff'])
    ->middleware(['portal.role:staff', 'throttle:10,1'])
    ->name('staff.profile.update');

Route::get('/admin/login', AdminExperienceController::class)
    ->defaults('screen', 'admin-login')
    ->name('admin.login');
Route::post('/admin/login', [PortalAuthController::class, 'adminLogin'])
    ->middleware('throttle:authentication')
    ->name('admin.login.store');
Route::post('/admin/logout', [PortalAuthController::class, 'adminLogout'])->name('admin.logout');
Route::get('/admin/logout', [PortalAuthController::class, 'rejectGetLogout'])->name('admin.logout.invalid');

Route::prefix('admin')->name('admin.')->middleware('portal.role:admin')->group(function (): void {
    Route::get('/', AdminExperienceController::class)->defaults('screen', 'admin-dashboard')->name('dashboard');
    Route::get('/dashboard', AdminExperienceController::class)->defaults('screen', 'admin-dashboard')->name('dashboard.legacy');
    Route::get('/profile', [AccountProfileController::class, 'admin'])->name('profile');
    Route::patch('/profile', [AccountProfileController::class, 'updateAdmin'])
        ->middleware('throttle:admin-mail')
        ->name('profile.update');
    Route::get('/pages', AdminExperienceController::class)->defaults('screen', 'admin-pages')->name('pages');
    Route::post('/pages', [AdminPageController::class, 'store'])->name('pages.store');
    Route::get('/pages/editor', AdminExperienceController::class)->defaults('screen', 'admin-page-editor')->name('pages.editor');
    Route::patch('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
    Route::post('/pages/{page}/blocks', [AdminPageBlockController::class, 'store'])->name('page-blocks.store');
    Route::patch('/page-blocks/{pageBlock}', [AdminPageBlockController::class, 'update'])->name('page-blocks.update');
    Route::patch('/page-blocks/{pageBlock}/visibility', [AdminPageBlockController::class, 'updateVisibility'])->name('page-blocks.visibility');
    Route::patch('/page-blocks/{pageBlock}/order', [AdminPageBlockController::class, 'reorder'])->name('page-blocks.order');
    Route::post('/page-blocks/{pageBlock}/duplicate', [AdminPageBlockController::class, 'duplicate'])->name('page-blocks.duplicate');
    Route::delete('/page-blocks/{pageBlock}', [AdminPageBlockController::class, 'destroy'])->name('page-blocks.destroy');
    Route::get('/news', AdminExperienceController::class)->defaults('screen', 'admin-news')->name('news');
    Route::post('/news', [AdminPostController::class, 'store'])->name('news.store');
    Route::get('/news/editor', AdminExperienceController::class)->defaults('screen', 'admin-news-editor')->name('news.editor');
    Route::patch('/news/{post}', [AdminPostController::class, 'update'])->name('news.update');
    Route::get('/events', AdminExperienceController::class)->defaults('screen', 'admin-events')->name('events');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::get('/events/editor', AdminExperienceController::class)->defaults('screen', 'admin-event-editor')->name('events.editor');
    Route::patch('/events/{event}', [AdminEventController::class, 'update'])->name('events.update');
    Route::get('/gallery', AdminExperienceController::class)->defaults('screen', 'admin-gallery')->name('gallery');
    Route::post('/gallery', [AdminGalleryController::class, 'store'])->name('gallery.store');
    Route::get('/gallery/editor', AdminExperienceController::class)->defaults('screen', 'admin-gallery-editor')->name('gallery.editor');
    Route::post('/gallery/{gallery}/items', [AdminGalleryController::class, 'storeItem'])->name('gallery-items.store');
    Route::delete('/gallery/{gallery}/items/{galleryItem}', [AdminGalleryController::class, 'destroyItem'])->name('gallery-items.destroy');
    Route::delete('/gallery/{gallery}/items/{galleryItem}/media', [AdminGalleryController::class, 'destroyItemMedia'])->name('gallery-items.destroy-media');
    Route::patch('/gallery/{gallery}', [AdminGalleryController::class, 'update'])->name('gallery.update');
    Route::get('/downloads', AdminExperienceController::class)->defaults('screen', 'admin-downloads')->name('downloads');
    Route::post('/downloads', [AdminDownloadController::class, 'store'])->name('downloads.store');
    Route::patch('/downloads/{download}/status', [AdminDownloadController::class, 'updateStatus'])->name('downloads.status');
    Route::patch('/downloads/{download}/metadata', [AdminDownloadController::class, 'updateMetadata'])->name('downloads.metadata');
    Route::patch('/downloads/{download}/file', [AdminDownloadController::class, 'replaceFile'])->name('downloads.file');
    Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs');
    Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
    Route::patch('/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
    Route::patch('/faqs/{faq}/verification', [AdminFaqController::class, 'verify'])->name('faqs.verify');
    Route::get('/staff', AdminExperienceController::class)->defaults('screen', 'admin-staff')->name('staff');
    Route::post('/staff', [AdminStaffMemberController::class, 'store'])->name('staff.store');
    Route::patch('/staff/{staffMember}/visibility', [AdminStaffMemberController::class, 'updateVisibility'])->name('staff.visibility');
    Route::patch('/staff/{staffMember}/metadata', [AdminStaffMemberController::class, 'updateMetadata'])->name('staff.metadata');
    Route::get('/programmes', AdminExperienceController::class)->defaults('screen', 'admin-programmes')->name('programmes');
    Route::post('/programmes', [AdminProgrammeController::class, 'store'])->name('programmes.store');
    Route::patch('/programmes/{programme}/status', [AdminProgrammeController::class, 'updateStatus'])->name('programmes.status');
    Route::patch('/programmes/{programme}/metadata', [AdminProgrammeController::class, 'updateMetadata'])->name('programmes.metadata');
    Route::get('/admissions', [AdminAdmissionEnquiryController::class, 'index'])->name('admissions');
    Route::get('/admissions/export', [AdminAdmissionEnquiryController::class, 'export'])->name('admissions.export');
    Route::get('/admissions/{admissionEnquiry}', [AdminAdmissionEnquiryController::class, 'show'])->name('admissions.show');
    Route::patch('/admissions/{admissionEnquiry}/assignment', [AdminAdmissionEnquiryController::class, 'updateAssignment'])->name('admissions.assignment');
    Route::patch('/admissions/{admissionEnquiry}/status', [AdminAdmissionEnquiryController::class, 'updateStatus'])->name('admissions.status');
    Route::post('/admissions/{admissionEnquiry}/notes', [AdminAdmissionEnquiryController::class, 'storeNote'])->name('admissions.notes.store');
    Route::post('/admissions/{admissionEnquiry}/reply', [AdminAdmissionEnquiryController::class, 'reply'])
        ->middleware('throttle:admin-mail')
        ->name('admissions.reply');
    Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages');
    Route::get('/contact-messages/export', [AdminContactMessageController::class, 'export'])->name('contact-messages.export');
    Route::get('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::patch('/contact-messages/{contactMessage}/assignment', [AdminContactMessageController::class, 'updateAssignment'])->name('contact-messages.assignment');
    Route::patch('/contact-messages/{contactMessage}/status', [AdminContactMessageController::class, 'updateStatus'])->name('contact-messages.status');
    Route::post('/contact-messages/{contactMessage}/notes', [AdminContactMessageController::class, 'storeNote'])->name('contact-messages.notes.store');
    Route::post('/contact-messages/{contactMessage}/reply', [AdminContactMessageController::class, 'reply'])
        ->middleware('throttle:admin-mail')
        ->name('contact-messages.reply');
    Route::get('/media', AdminExperienceController::class)->defaults('screen', 'admin-media')->name('media');
    Route::post('/media', [AdminMediaController::class, 'store'])->name('media.store');
    Route::get('/media/{media}/preview', [AdminMediaController::class, 'preview'])->name('media.preview');
    Route::patch('/media/{media}/metadata', [AdminMediaController::class, 'updateMetadata'])->name('media.metadata');
    Route::patch('/media/{media}/publication', [AdminMediaController::class, 'updatePublication'])->name('media.publication');
    Route::patch('/media/{media}/replacement', [AdminMediaController::class, 'replace'])->name('media.replace');
    Route::delete('/media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');
    Route::get('/users', AdminExperienceController::class)->defaults('screen', 'admin-users')->name('users');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/profile', [AdminUserController::class, 'updateProfile'])->name('users.profile');
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
    Route::patch('/users/{user}/roles', [AdminUserController::class, 'updateRoles'])->name('users.roles');
    Route::get('/roles', AdminExperienceController::class)->defaults('screen', 'admin-roles')->name('roles');
    Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
    Route::patch('/roles/{role}/permissions', [AdminRoleController::class, 'updatePermissions'])->name('roles.permissions');
    Route::get('/settings', AdminExperienceController::class)->defaults('screen', 'admin-settings')->name('settings');
    Route::patch('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('/audit-log', [AdminAuditLogController::class, 'index'])->name('audit-log');
    Route::get('/audit-log/export', [AdminAuditLogController::class, 'export'])->name('audit-log.export');
    Route::get('/audit-log/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-log.show');
});

Route::get('/{page:slug}', [PublicContentPageController::class, 'page'])
    ->where('page', '[A-Za-z0-9-]+')
    ->name('pages.show');

Route::fallback(PublicRedirectController::class);
