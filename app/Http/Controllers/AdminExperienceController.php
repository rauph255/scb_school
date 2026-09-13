<?php

namespace App\Http\Controllers;

use App\Models\AdmissionEnquiry;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Permission;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Programme;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\StaffMember;
use App\Models\User;
use App\Support\AdminAlertFeed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

class AdminExperienceController extends Controller
{
    /**
     * Render the approved administration experience with MySQL-backed content
     * and server-side form controls.
     */
    public function __invoke(Request $request): Response
    {
        return $this->render((string) $request->route('screen', 'admin-dashboard'));
    }

    public function render(string $screen): Response
    {
        $map = [
            'admin-login' => 'login.html',
            'admin-dashboard' => 'dashboard.html',
            'admin-pages' => 'pages.html',
            'admin-page-editor' => 'page-editor.html',
            'admin-news' => 'news.html',
            'admin-news-editor' => 'news-editor.html',
            'admin-events' => 'events.html',
            'admin-event-editor' => 'event-editor.html',
            'admin-gallery' => 'gallery.html',
            'admin-gallery-editor' => 'gallery-editor.html',
            'admin-downloads' => 'downloads.html',
            'admin-staff' => 'staff.html',
            'admin-programmes' => 'programmes.html',
            'admin-admissions' => 'admissions.html',
            'admin-contact-messages' => 'contact-messages.html',
            'admin-media' => 'media.html',
            'admin-users' => 'users.html',
            'admin-roles' => 'roles.html',
            'admin-settings' => 'settings.html',
            'admin-audit-log' => 'audit-log.html',
        ];

        abort_unless(isset($map[$screen]), 404);

        $path = resource_path('admin-experience/'.$map[$screen]);

        abort_unless(File::exists($path), 404);

        $html = File::get($path);

        return response($this->prepareHtml($html, $screen), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function prepareHtml(string $html, string $screen): string
    {
        $isAdmin = str_contains($html, '../assets/');

        $html = $this->rewriteAssetUrls($html);
        $html = $this->rewritePageLinks($html, $isAdmin);

        if ($screen === 'admin-dashboard') {
            $html = $this->injectAdminDashboardData($html);
        }

        if ($screen === 'admin-pages') {
            $html = $this->injectAdminPagesData($html);
        }

        if ($screen === 'admin-page-editor') {
            $html = $this->injectAdminPageEditorData($html);
        }

        if ($screen === 'admin-news') {
            $html = $this->injectAdminNewsData($html);
        }

        if ($screen === 'admin-news-editor') {
            $html = $this->injectAdminNewsEditorData($html);
        }

        if ($screen === 'admin-events') {
            $html = $this->injectAdminEventsData($html);
        }

        if ($screen === 'admin-event-editor') {
            $html = $this->injectAdminEventEditorData($html);
        }

        if ($screen === 'admin-gallery') {
            $html = $this->injectAdminGalleryData($html);
        }

        if ($screen === 'admin-gallery-editor') {
            $html = $this->injectAdminGalleryEditorData($html);
        }

        if ($screen === 'admin-admissions') {
            $html = $this->injectAdminAdmissionsData($html);
        }

        if ($screen === 'admin-contact-messages') {
            $html = $this->injectAdminContactMessagesData($html);
        }

        if ($screen === 'admin-downloads') {
            $html = $this->injectAdminDownloadsData($html);
        }

        if ($screen === 'admin-staff') {
            $html = $this->injectAdminStaffData($html);
        }

        if ($screen === 'admin-programmes') {
            $html = $this->injectAdminProgrammesData($html);
        }

        if ($screen === 'admin-media') {
            $html = $this->injectAdminMediaData($html);
        }

        if ($screen === 'admin-users') {
            $html = $this->injectAdminUsersData($html);
        }

        if ($screen === 'admin-roles') {
            $html = $this->injectAdminRolesData($html);
        }

        if ($screen === 'admin-audit-log') {
            $html = $this->injectAdminAuditLogData($html);
        }

        if ($screen === 'admin-settings') {
            $html = $this->injectAdminSettingsData($html);
        }

        if ($isAdmin && $screen !== 'admin-login') {
            $html = $this->prepareAdminFilter($html, $screen);
            $html = $this->prepareAdminLogout($html);
            $html = preg_replace(
                '#(<a href="/admin/downloads"[^>]*>.*?<span>Downloads</span></a>)#s',
                '$1<a href="'.e(route('admin.faqs')).'"><span class="nav-ico"><i data-lucide="messages-square"></i></span><span>FAQs</span></a>',
                $html,
                1,
            ) ?? $html;
        }

        if ($isAdmin && $screen !== 'admin-login' && auth()->check()) {
            $alerts = view('admin.partials.notifications', [
                'adminAlerts' => app(AdminAlertFeed::class)->for(auth()->user()),
            ])->render();
            $html = str_replace(
                '<div><b class="small">School Administrator</b><div class="small muted">Signed-in account</div></div><div class="avatar">SA</div>',
                '<div class="admin-topbar-actions">'.$alerts.'<a class="admin-user admin-user-link" href="'.e(route('admin.profile')).'" aria-label="Edit your account details"><div><b class="small">'.e(auth()->user()->name).'</b><div class="small muted">'.e(auth()->user()->email).'</div></div><div class="avatar">'.e(Str::upper(Str::substr(auth()->user()->name, 0, 1))).'</div></a></div>',
                $html
            );
            $html = preg_replace(
                '/(<a href="\/admin\/dashboard"[^>]*>.*?<\/a>)/s',
                '$1<a href="'.e(route('admin.profile')).'"><span class="nav-ico"><i data-lucide="user-round-cog"></i></span><span>My profile</span></a>',
                $html,
                1,
            ) ?? $html;
        }

        $html = str_replace(
            '<div class="toolbar"><button class="icon-btn">‹</button><button class="icon-btn">1</button><button class="icon-btn">2</button><button class="icon-btn">›</button></div>',
            '<span class="small muted">Use the filters above to refine this view</span>',
            $html
        );
        $html = str_replace('Bulk actions · Export', 'Select records to compare', $html);
        $html = str_replace('Search resources, records or actions', 'Search visible records', $html);
        $html = str_replace(
            'Laravel policies and Filament access controls expressed in a clear matrix.',
            'Manage access for each school administration role.',
            $html,
        );
        $html = str_replace(
            'Sampled from the uploaded crest and used consistently across public and admin interfaces.',
            'Approved school colours used consistently across the website.',
            $html,
        );
        $html = preg_replace('/<button class="icon-btn">⋮<\/button>/', '', $html) ?? $html;
        $html = $this->rewriteMediaAssetUrls($html);
        $html = preg_replace_callback(
            '/<body([^>]*)>/',
            fn (array $matches): string => '<body'.$matches[1].'>'.$this->loadingMarkup($isAdmin ? 'Preparing administration' : 'Preparing the school website', $isAdmin),
            $html,
            1
        ) ?? $html;
        $html = str_replace(
            '</head>',
            $this->creditStyles().'<link rel="stylesheet" href="/assets/css/scb-experience.css"></head>',
            $html
        );
        $html = str_replace('</body>', '<script src="/assets/js/lucide.min.js"></script><script src="/assets/js/scb-experience.js"></script></body>', $html);

        if (str_contains($html, 'class="admin-login"')) {
            return $this->prepareAdminLoginHtml($html);
        }

        if ($isAdmin) {
            return str_replace('</aside>', $this->creditMarkup('admin').'</aside>', $html);
        }

        return str_replace('</div></footer>', $this->creditMarkup('public').'</div></footer>', $html);
    }

    private function rewriteAssetUrls(string $html): string
    {
        return preg_replace(
            '/(href|src)="(?:\.\.\/)?assets\//',
            '$1="/assets/scb/',
            $html
        );
    }

    private function rewriteMediaAssetUrls(string $html): string
    {
        $mediaBySourceName = Media::query()
            ->publiclyVisible()
            ->get()
            ->keyBy('stored_name');

        $sourceMap = [
            'care.webp' => '03-sisters-community-group.jpg',
            'children_dance.webp' => 'cultural-performance.webp',
            'classroom.webp' => '05-classroom-learning.jpg',
            'community_group.webp' => '03-sisters-community-group.jpg',
            'football.webp' => '06-students-playing-football.jpg',
            'hall_crowd.webp' => '07-students-school-assembly.jpg',
            'hall_wide.webp' => '07-students-school-assembly.jpg',
            'inspection.webp' => '03-sisters-community-group.jpg',
            'library.webp' => '02-school-library-reading.jpg',
            'playground.webp' => '11-school-playground.jpg',
            'sisters_ceremony.webp' => '01-religious-community-ceremony.jpg',
            'sisters_group.webp' => '03-sisters-community-group.jpg',
            'staff_admin.webp' => '14-school-administration-staff.jpg',
            'staff_outdoors.webp' => '14-school-administration-staff.jpg',
            'students_ceremony.webp' => '01-religious-community-ceremony.jpg',
            'students_tree.webp' => 'tree-planting-community.webp',
            'waf.webp' => 'cultural-performance.webp',
            'young_learners.webp' => 'cultural-performance.webp',
        ];

        return preg_replace_callback(
            '#/assets/scb/images/([^"\'?)]+)#',
            function (array $matches) use ($mediaBySourceName, $sourceMap): string {
                $sourceName = basename($matches[1]);

                if ($sourceName === 'logo-original.jpg') {
                    return '/assets/images/brand/scb-logo-original.jpg';
                }

                $media = $mediaBySourceName->get($sourceMap[$sourceName] ?? '');
                $url = $media?->publicUrl();

                return is_string($url) ? (parse_url($url, PHP_URL_PATH) ?: $url) : '/assets/images/brand/scb-logo-original.jpg';
            },
            $html,
        ) ?? $html;
    }

    private function prepareAdminFilter(string $html, string $screen): string
    {
        $options = match ($screen) {
            'admin-staff' => ['All visibility', 'Public', 'Private', 'Inactive'],
            'admin-users' => ['All statuses', 'Active', 'Disabled'],
            'admin-admissions' => ['All statuses', 'New', 'Assigned', 'Contacted', 'Visit scheduled', 'Converted', 'Closed'],
            'admin-contact-messages' => ['All statuses', 'New', 'Assigned', 'Responded', 'Closed', 'Spam'],
            'admin-events' => ['All statuses', 'Published', 'Draft', 'Review', 'Cancelled', 'Archived'],
            'admin-news' => ['All statuses', 'Published', 'Draft', 'Review', 'Scheduled', 'Archived'],
            'admin-audit-log' => [
                'All actions',
                ...AuditLog::query()
                    ->distinct()
                    ->orderBy('action')
                    ->pluck('action')
                    ->map(fn (string $action): string => $this->statusLabel($action))
                    ->all(),
            ],
            default => ['All statuses', 'Published', 'Draft', 'Review', 'Archived'],
        };

        $select = '<select aria-label="Filter visible records by status">'.collect($options)
            ->map(fn (string $option): string => '<option>'.e($option).'</option>')
            ->implode('').'</select>';

        return preg_replace(
            '/<select><option>All statuses<\/option>.*?<\/select>/',
            $select,
            $html,
            1,
        ) ?? $html;
    }

    private function prepareAdminLogout(string $html): string
    {
        return preg_replace(
            '#<a href="/admin/logout"([^>]*)>(.*?)</a>#s',
            '<form class="admin-nav-form" method="POST" action="/admin/logout"><input type="hidden" name="_token" value="'.e(csrf_token()).'"><button type="submit"$1>$2</button></form>',
            $html,
        ) ?? $html;
    }

    private function rewritePageLinks(string $html, bool $isAdmin): string
    {
        $publicLinks = [
            'index.html' => '/',
            'about.html' => '/about',
            'academics.html' => '/academics',
            'admissions.html' => '/admissions',
            'news.html' => '/news',
            'news-detail.html' => '/news/young-learners-shine',
            'events.html' => '/events',
            'event-detail.html' => '/events/parent-orientation',
            'gallery.html' => '/gallery',
            'downloads.html' => '/downloads',
            'contact.html' => '/contact',
            'faq.html' => '/faq',
            'privacy.html' => '/privacy',
            'admin/login.html' => '/admin/login',
        ];

        $adminLinks = [
            '../index.html' => '/',
            'dashboard.html' => '/admin/dashboard',
            'pages.html' => '/admin/pages',
            'page-editor.html' => '/admin/pages/editor',
            'news.html' => '/admin/news',
            'news-editor.html' => '/admin/news/editor',
            'events.html' => '/admin/events',
            'event-editor.html' => '/admin/events/editor',
            'gallery.html' => '/admin/gallery',
            'gallery-editor.html' => '/admin/gallery/editor',
            'downloads.html' => '/admin/downloads',
            'staff.html' => '/admin/staff',
            'programmes.html' => '/admin/programmes',
            'admissions.html' => '/admin/admissions',
            'contact-messages.html' => '/admin/contact-messages',
            'media.html' => '/admin/media',
            'users.html' => '/admin/users',
            'roles.html' => '/admin/roles',
            'settings.html' => '/admin/settings',
            'audit-log.html' => '/admin/audit-log',
            'login.html' => '/admin/logout',
        ];

        $links = $isAdmin ? $adminLinks : $publicLinks;

        return str_replace(array_keys($links), array_values($links), $html);
    }

    private function creditStyles(): string
    {
        return <<<'HTML'
<style>
.site-credit--admin{margin:20px 8px 4px;justify-content:center}
.site-credit--public{margin-top:22px}
.login-card-wrap{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px}
.site-credit--login{width:max-content;margin:0;border-color:#CFC4B8;background:#fff;color:#574E57;box-shadow:0 8px 26px rgba(61,45,63,.1)}
.site-credit--login:hover{border-color:#DAAD18;color:#3D2D3F}
.admin-nav-form,.staff-nav-form{margin:0}
.admin-nav-form button,.staff-nav-form button{appearance:none;width:100%;border:0;background:transparent;color:inherit;font:inherit;text-align:left;cursor:pointer}
.admin-nav-form button{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;font-size:.84rem;font-weight:750}
.admin-nav-form button:hover{background:rgba(218,173,24,.16);color:#fff}
@media(max-width:800px){.login-card-wrap{padding:26px 18px}}
</style>
HTML;
    }

    private function creditMarkup(string $placement): string
    {
        $classes = $placement === 'login'
            ? 'site-credit site-credit--light login-credit site-credit--login'
            : 'site-credit site-credit--'.$placement;
        $marker = $placement === 'login' ? ' data-admin-login-credit' : '';

        return <<<HTML
<a class="{$classes}"{$marker} href="https://www.falconode.net" target="_blank" rel="noopener noreferrer">Powered by Falconode (T) Ltd</a>
HTML;
    }

    private function loadingMarkup(string $label, bool $isAdmin = false): string
    {
        return '<div class="site-loader'.($isAdmin ? ' site-loader--admin' : '').'" data-site-loader role="status" aria-live="polite" aria-label="Loading St. Charles Borromeo"><div class="site-loader__content"><div class="site-loader__crest"><img src="/assets/images/brand/scb-logo-original.jpg" alt=""></div><b>St. Charles Borromeo</b><span>'.e($label).'</span></div></div><noscript><style>.site-loader{display:none!important}</style></noscript>';
    }

    private function prepareAdminLoginHtml(string $html): string
    {
        $aerialMedia = Media::query()
            ->publiclyVisible()
            ->where('stored_name', 'campus-aerial.webp')
            ->first();

        if ($aerialMedia) {
            $image = '<img src="'.e((string) $aerialMedia->responsiveUrl(1600)).'"'
                .' srcset="'.e((string) $aerialMedia->responsiveSrcset()).'"'
                .' sizes="(max-width: 800px) 100vw, 55vw"'
                .' alt="'.e((string) $aerialMedia->alt_text).'"'
                .($aerialMedia->width ? ' width="'.$aerialMedia->width.'"' : '')
                .($aerialMedia->height ? ' height="'.$aerialMedia->height.'"' : '')
                .' loading="eager" decoding="async" fetchpriority="high">';

            $html = preg_replace(
                '/(<div class="login-visual">)<img[^>]*>/',
                '$1'.$image,
                $html,
                1,
            ) ?? $html;
        }

        $html = str_replace(
            '<a class="small muted" href="#">Forgot password?</a>',
            '<a class="small muted" href="'.e(route('password.request')).'">Forgot password?</a>',
            $html
        );

        $html = str_replace(
            '<form class="login-card">',
            '<form class="login-card" method="POST" action="/admin/login"><input type="hidden" name="_token" value="'.csrf_token().'">',
            $html
        );

        $html = str_replace(
            '<input type="email">',
            '<input type="email" name="email" value="'.e((string) old('email')).'" autocomplete="username" required>',
            $html
        );

        $html = str_replace(
            '<input type="password">',
            '<input type="password" name="password" autocomplete="current-password" required>',
            $html
        );

        $html = str_replace(
            '<input type="checkbox">',
            '<input type="checkbox" name="remember" value="1"'.(old('remember') ? ' checked' : '').'>',
            $html
        );

        $errors = session('errors');

        if ($errors instanceof ViewErrorBag && $errors->any()) {
            $html = str_replace(
                '<div class="login-brand">',
                '<div class="notice" style="margin-bottom:16px;border-left-color:var(--crimson)">'.e($errors->first()).'</div><div class="login-brand">',
                $html,
            );
        }

        if (is_string(session('status')) && session('status') !== '') {
            $html = str_replace(
                '<div class="login-brand">',
                '<div class="notice" style="margin-bottom:16px">'.e(session('status')).'</div><div class="login-brand">',
                $html,
            );
        }

        $html = str_replace(
            '<a class="btn btn-primary" style="width:100%" href="/admin/dashboard">Sign in securely</a>',
            '<button class="btn btn-primary" style="width:100%;border:0" type="submit">Sign in securely</button>',
            $html
        );

        return str_replace(
            '</form></div></div><script',
            '</form>'.$this->creditMarkup('login').'</div></div><script',
            $html,
        );
    }

    private function injectAdminDashboardData(string $html): string
    {
        $canViewAdmissions = Gate::allows('viewAny', AdmissionEnquiry::class);
        $canViewContacts = Gate::allows('viewAny', ContactMessage::class);

        $metrics = <<<'HTML'
<div class="admin-grid-4"><div class="metric"><div class="metric-top"><span>Published pages</span><div class="icon">▤</div></div><strong>{{pages}}</strong><span>{{recentPages}} updated this week</span></div><div class="metric"><div class="metric-top"><span>Upcoming events</span><div class="icon">▦</div></div><strong>{{events}}</strong><span>Next: {{nextEvent}}</span></div><div class="metric"><div class="metric-top"><span>New enquiries</span><div class="icon">✦</div></div><strong>{{enquiries}}</strong><span>{{messages}} contact messages</span></div><div class="metric"><div class="metric-top"><span>Media files</span><div class="icon">▣</div></div><strong>{{media}}</strong><span>Consent-filtered public assets</span></div></div>
HTML;

        $metrics = strtr($metrics, [
            '{{pages}}' => (string) Page::query()->published()->count(),
            '{{recentPages}}' => (string) Page::query()->where('updated_at', '>=', now()->subWeek())->count(),
            '{{events}}' => (string) Event::query()->published()->where('starts_at', '>=', now())->count(),
            '{{nextEvent}}' => e(Event::query()->published()->where('starts_at', '>=', now())->orderBy('starts_at')->value('title') ?? 'No scheduled event'),
            '{{enquiries}}' => $canViewAdmissions ? (string) AdmissionEnquiry::query()->where('status', 'new')->count() : 'N/A',
            '{{messages}}' => $canViewContacts ? (string) ContactMessage::query()->where('status', 'new')->count() : 'restricted',
            '{{media}}' => (string) Media::query()->publiclyVisible()->count(),
        ]);

        $html = preg_replace(
            '/<div class="admin-grid-4">.*?<\/div><\/div>\n/s',
            $metrics."\n",
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/<tbody>.*?<\/tbody>/s',
            '<tbody>'.$this->adminDashboardRows().'</tbody>',
            $html,
            1
        ) ?? $html;

        $html = str_replace(
            '<button class="btn btn-primary btn-sm" data-toast="Quick-create menu opened">＋ Quick create</button>',
            '<details class="quick-create"><summary class="btn btn-primary btn-sm">＋ Quick create</summary><div class="quick-create__menu"><a href="'.e(route('admin.pages')).'#page-create-panel">Page</a><a href="'.e(route('admin.news')).'#news-create-panel">News story</a><a href="'.e(route('admin.events')).'#event-create-panel">Event</a><a href="'.e(route('admin.gallery')).'#gallery-create-panel">Gallery</a></div></details>',
            $html
        );

        return preg_replace(
            '/<div class="panel-body activity">.*?<\/div><\/div><\/div><div class="admin-panel"><div class="panel-head"><h2>Brand palette<\/h2>/s',
            '<div class="panel-body activity">'.$this->adminActivityRows().'</div></div><div class="admin-panel"><div class="panel-head"><h2>Brand palette</h2>',
            $html,
            1
        ) ?? $html;
    }

    private function injectAdminPagesData(string $html): string
    {
        $rows = Page::query()
            ->latest('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (Page $page): string => '<tr><td><input type="checkbox" aria-label="Select '.e($page->title).'"></td><td><b>'.e($page->title).'</b><div class="small muted">'.e($this->pagePath($page)).'</div></td><td>'.e(str($page->template_key)->replace('_', ' ')->title()).'</td><td><span class="status '.$this->statusClass($page->status).'">'.e($this->statusLabel($page->status)).'</span></td><td>Website</td><td>'.e($page->updated_at?->diffForHumans() ?? 'Pending').'</td><td><div class="row-actions">'.$this->publicPreviewAction($page->status === 'published', $this->pagePath($page), $page->title).'<a class="icon-btn" href="'.e(route('admin.pages.editor', ['page' => $page->slug])).'" title="Edit '.e($page->title).'" aria-label="Edit '.e($page->title).'">✎</a></div></td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="7"><b>No pages found</b></td></tr>');

        $html = $this->injectCreatePanel(
            $html,
            '<a class="btn btn-primary btn-sm" href="/admin/pages/editor">＋ Create page</a>',
            'page-create-panel',
            '＋ Create page',
            route('admin.pages.store'),
            '<div class="admin-field"><label for="new-page-title">Page title</label><input id="new-page-title" name="title" required maxlength="255"></div><div class="admin-field"><label for="new-page-excerpt">Short introduction</label><textarea id="new-page-excerpt" name="excerpt" maxlength="1000"></textarea></div>',
            Page::class,
        );

        return str_replace(
            'Showing 1–5 of 24 records',
            'Showing '.min(Page::query()->count(), 12).' of '.Page::query()->count().' records',
            $html
        );
    }

    private function injectAdminPageEditorData(string $html): string
    {
        $requestedSlug = request()->query('page', 'about');
        $page = Page::query()
            ->with(['featuredMedia', 'blocks'])
            ->where('slug', $requestedSlug)
            ->first()
            ?? Page::query()->with(['featuredMedia', 'blocks'])->orderBy('id')->firstOrFail();

        $media = $page->featuredMedia;
        $mediaSrc = $media && $media->mime_type && str_starts_with($media->mime_type, 'image/')
            ? $media->adminPreviewUrl()
            : asset('assets/scb/images/staff_outdoors.webp');
        $mediaName = $media?->original_name ?? 'No hero image selected';
        $publishDate = $page->published_at?->format('Y-m-d\TH:i') ?? '';
        $availableMedia = Media::query()
            ->publiclyVisible()
            ->where('mime_type', 'like', 'image/%')
            ->orderBy('original_name')
            ->get(['id', 'original_name']);

        $html = str_replace(
            '<h1>Edit page</h1><p class="muted">About our school · reusable block editor</p>',
            '<h1>Edit page</h1><p class="muted">'.e($page->title).' · reusable block editor</p>',
            $html
        );

        $html = str_replace(
            '<button class="btn btn-primary btn-sm" data-toast="Page saved as draft">Save changes</button>',
            '<button class="btn btn-primary btn-sm" type="submit" form="page-editor-form">Save changes</button>',
            $html
        );

        $html = str_replace(
            '<span class="status status-published">Published</span>',
            '<span class="status '.$this->statusClass($page->status).'">'.e($this->statusLabel($page->status)).'</span>',
            $html
        );

        $html = str_replace(
            [
                '<input value="About our school">',
                '<input value="about">',
                '<img src="/assets/scb/images/staff_outdoors.webp" style="max-height:210px;margin:0 auto 14px;border-radius:9px"><b>staff_outdoors.webp</b>',
                '<select><option>Published</option><option>Draft</option><option>Pending review</option></select>',
                '<input type="datetime-local" value="2026-08-03T09:00">',
                '<label class="check"><input type="checkbox" checked> Show in search results</label>',
                '<input value="About St. Charles Borromeo School">',
                '<textarea style="min-height:110px">Discover the school’s values, leadership and learning community.</textarea>',
            ],
            [
                '<input form="page-editor-form" name="title" value="'.e($page->title).'" required>',
                '<input form="page-editor-form" name="slug" value="'.e($page->slug).'" required>',
                '<img src="'.e($mediaSrc).'" style="max-height:210px;margin:0 auto 14px;border-radius:9px"><b>'.e($mediaName).'</b>',
                '<select form="page-editor-form" name="status">'.$this->pageStatusOptions($page->status).'</select>',
                '<input form="page-editor-form" name="published_at" type="datetime-local" value="'.e($publishDate).'">',
                '<label class="check"><input form="page-editor-form" name="robots_index" value="1" type="checkbox" '.($page->robots_index ? 'checked' : '').'> Show in search results</label>',
                '<input form="page-editor-form" name="seo_title" value="'.e($page->seo_title ?? '').'">',
                '<textarea form="page-editor-form" name="seo_description" style="min-height:110px">'.e($page->seo_description ?? '').'</textarea>',
            ],
            $html
        );

        $blocks = $page->blocks
            ->values()
            ->map(fn (PageBlock $block, int $index): string => $this->pageBlockEditorPanel(
                $block,
                $index,
                $page->blocks->count(),
                $availableMedia,
            ))
            ->implode('');

        $html = preg_replace(
            '/<div class="admin-field"><label>Page blocks<\/label>.*?<\/div><\/div><\/div><\/div><aside>/s',
            '<div class="admin-field"><label>Page blocks</label>'.($blocks ?: '<div class="admin-panel" style="margin:0"><div class="panel-body small muted">No blocks yet.</div></div>').$this->pageBlockCreatePanel($page, $availableMedia).$this->pageBlockTypeDatalist().'</div></div></div></div><aside>',
            $html,
            1
        ) ?? $html;

        $html = str_replace(
            '<div class="admin-form-grid">',
            $this->adminEditorFeedback().'<div class="admin-form-grid">',
            $html
        );

        return str_replace(
            '</main>',
            '<form id="page-editor-form" method="POST" action="'.e(route('admin.pages.update', $page)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form></main>',
            $html
        );
    }

    private function injectAdminNewsData(string $html): string
    {
        $rows = Post::query()
            ->with(['category', 'author'])
            ->latest('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (Post $post): string => '<tr><td><input type="checkbox" aria-label="Select '.e($post->title).'"></td><td><b>'.e($post->title).'</b></td><td>'.e($post->category?->name ?? 'School news').'</td><td><span class="status '.$this->statusClass($post->status).'">'.e($this->statusLabel($post->status)).'</span></td><td>'.e($post->published_at?->format('d M Y') ?? 'Scheduled').'</td><td>'.e($post->author?->name ?? 'Communications').'</td><td><div class="row-actions">'.$this->publicPreviewAction($post->status === 'published', route('news.show', ['post' => $post->slug]), $post->title).'<a class="icon-btn" href="'.e(route('admin.news.editor', ['post' => $post->slug])).'" title="Edit '.e($post->title).'" aria-label="Edit '.e($post->title).'">✎</a></div></td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="7"><b>No news posts found</b></td></tr>');

        $html = $this->injectCreatePanel(
            $html,
            '<a class="btn btn-primary btn-sm" href="/admin/news/editor">＋ Create story</a>',
            'news-create-panel',
            '＋ Create story',
            route('admin.news.store'),
            '<div class="admin-field"><label for="new-story-title">Story title</label><input id="new-story-title" name="title" required maxlength="255"></div><div class="admin-field"><label for="new-story-excerpt">Summary</label><textarea id="new-story-excerpt" name="excerpt" maxlength="1000"></textarea></div><div class="admin-field admin-field--wide"><label for="new-story-body">Opening content</label><textarea id="new-story-body" name="body"></textarea></div>',
            Post::class,
        );

        return str_replace(
            'Showing 1–5 of 24 records',
            'Showing '.min(Post::query()->count(), 12).' of '.Post::query()->count().' records',
            $html
        );
    }

    private function injectAdminNewsEditorData(string $html): string
    {
        $requestedSlug = request()->query('post', 'young-learners-shine');
        $post = Post::query()
            ->with(['category', 'featuredMedia', 'tags'])
            ->where('slug', $requestedSlug)
            ->first()
            ?? Post::query()->with(['category', 'featuredMedia', 'tags'])->orderBy('id')->firstOrFail();

        Gate::authorize('update', $post);

        $media = $post->featuredMedia;
        $availableMedia = $this->availableImageMedia();
        $publishDate = ($post->status === 'scheduled' ? $post->scheduled_at : $post->published_at)?->format('Y-m-d\TH:i') ?? '';
        $tags = $post->tags->pluck('name')->implode(', ');

        $html = str_replace(
            '<h1>Create news story</h1><p class="muted">Editorial form with structured SEO, media and publishing controls.</p>',
            '<h1>Edit news story</h1><p class="muted">'.e($post->title).' · editorial form with structured media and publishing controls.</p>',
            $html
        );

        $html = str_replace(
            '<button class="btn btn-primary btn-sm" data-toast="Story saved">Save story</button>',
            '<button class="btn btn-primary btn-sm" type="submit" form="news-editor-form">Save story</button>',
            $html
        );

        $html = str_replace(
            [
                '<input value="Creative arts day celebrates confidence">',
                '<textarea style="min-height:100px">Young learners practised courage, movement and teamwork through performance.</textarea>',
                '<div class="editor-area" contenteditable="true"><h3>Learning through expression</h3><p>Performances become meaningful learning experiences when children are guided through preparation, rehearsal and reflection.</p></div>',
                '<div class="upload-zone"><img src="/assets/scb/images/children_dance.webp" style="max-height:240px;margin:0 auto 12px;border-radius:9px"><b>children_dance.webp</b><span class="small muted">Alt text: Young learners performing on stage</span></div>',
                '<select><option>Published</option><option>Draft</option><option>Pending review</option><option>Scheduled</option></select>',
                '<input type="datetime-local" value="2026-06-12T08:00">',
                '<label class="check"><input type="checkbox" checked> Feature on home page</label>',
                '<select><option>Celebration</option><option>Learning</option><option>Sport</option></select>',
                '<input value="pre-primary, creative arts">',
            ],
            [
                '<input form="news-editor-form" name="title" value="'.e($post->title).'" required>',
                '<textarea form="news-editor-form" name="excerpt" style="min-height:100px">'.e($post->excerpt ?? '').'</textarea>',
                '<textarea class="editor-area" form="news-editor-form" name="body" style="min-height:180px">'.e($post->body).'</textarea>',
                $this->featuredImagePicker('news-editor-form', $media, $availableMedia, 'news story'),
                '<select form="news-editor-form" name="status">'.$this->publicationStatusOptions($post->status, includeScheduled: true).'</select>',
                '<input form="news-editor-form" name="published_at" type="datetime-local" value="'.e($publishDate).'">',
                '<label class="check"><input form="news-editor-form" name="is_featured" value="1" type="checkbox" '.($post->is_featured ? 'checked' : '').'> Feature on home page</label>',
                '<select form="news-editor-form" name="post_category_id">'.$this->postCategoryOptions($post->post_category_id).'</select>',
                '<input form="news-editor-form" name="tags" value="'.e($tags).'">',
            ],
            $html
        );

        $html = str_replace(
            '<div class="admin-form-grid">',
            $this->adminEditorFeedback().'<div class="admin-form-grid">',
            $html
        );

        $html = str_replace(
            '<div class="admin-panel"><div class="panel-head"><h2>Child image review</h2></div><div class="panel-body"><label class="check"><input type="checkbox" checked> Publication consent confirmed</label><label class="check"><input type="checkbox" checked> No sensitive identifying data</label></div></div>',
            $this->featuredImageReviewPanel($media),
            $html,
        );

        return str_replace(
            '</main>',
            '<form id="news-editor-form" method="POST" action="'.e(route('admin.news.update', $post)).'" enctype="multipart/form-data"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form></main>',
            $html
        );
    }

    private function injectAdminEventsData(string $html): string
    {
        $rows = Event::query()
            ->latest('starts_at')
            ->limit(12)
            ->get()
            ->map(fn (Event $event): string => '<tr><td><input type="checkbox" aria-label="Select '.e($event->title).'"></td><td><b>'.e($event->title).'</b></td><td>'.e($event->starts_at->format('d M Y · H:i')).'</td><td>'.e($event->venue_name ?? 'School campus').'</td><td><span class="status '.$this->statusClass($event->publication_status).'">'.e($this->statusLabel($event->publication_status)).'</span></td><td><div class="row-actions">'.$this->publicPreviewAction($event->publication_status === 'published', route('events.show', ['event' => $event->slug]), $event->title).'<a class="icon-btn" href="'.e(route('admin.events.editor', ['event' => $event->slug])).'" title="Edit '.e($event->title).'" aria-label="Edit '.e($event->title).'">✎</a></div></td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="6"><b>No events found</b></td></tr>');

        $html = $this->injectCreatePanel(
            $html,
            '<a class="btn btn-primary btn-sm" href="/admin/events/editor">＋ Create event</a>',
            'event-create-panel',
            '＋ Create event',
            route('admin.events.store'),
            '<div class="admin-field"><label for="new-event-title">Event title</label><input id="new-event-title" name="title" required maxlength="255"></div><div class="admin-field"><label for="new-event-start">Start date and time</label><input id="new-event-start" name="starts_at" type="datetime-local" required></div><div class="admin-field"><label for="new-event-venue">Venue</label><input id="new-event-venue" name="venue_name" maxlength="255"></div>',
            Event::class,
        );

        return str_replace(
            'Showing 1–5 of 24 records',
            'Showing '.min(Event::query()->count(), 12).' of '.Event::query()->count().' records',
            $html
        );
    }

    private function injectAdminEventEditorData(string $html): string
    {
        $requestedSlug = request()->query('event', 'parent-orientation');
        $event = Event::query()
            ->with(['category', 'featuredMedia'])
            ->where('slug', $requestedSlug)
            ->first()
            ?? Event::query()->with(['category', 'featuredMedia'])->orderBy('id')->firstOrFail();

        Gate::authorize('update', $event);

        $media = $event->featuredMedia;
        $availableMedia = $this->availableImageMedia();

        $html = str_replace(
            '<h1>Create event</h1><p class="muted">Date, audience, venue, registration and calendar controls.</p>',
            '<h1>Edit event</h1><p class="muted">'.e($event->title).' · date, venue, category and publishing controls.</p>',
            $html
        );

        $html = str_replace(
            '<button class="btn btn-primary btn-sm" data-toast="Event saved">Save event</button>',
            '<button class="btn btn-primary btn-sm" type="submit" form="event-editor-form">Save event</button>',
            $html
        );

        $html = str_replace(
            [
                '<input value="New family orientation">',
                '<textarea>Welcome, programme briefing and campus orientation for new families.</textarea>',
                '<input type="datetime-local" value="2026-08-18T09:00">',
                '<input type="datetime-local" value="2026-08-18T11:00">',
                '<input value="School hall">',
                '<div class="editor-area" style="min-height:180px" contenteditable="true"><p>Welcome and prayer<br>School overview<br>Academic briefing<br>Campus orientation</p></div>',
                '<select><option>Published</option><option>Draft</option><option>Cancelled</option></select>',
                '<select><option>New families</option><option>Whole school</option><option>Staff</option></select>',
                '<label class="check"><input type="checkbox" checked> Show on home page</label>',
                '<label class="check"><input type="checkbox" checked> Allow calendar export</label>',
                '<img src="/assets/scb/images/hall_wide.webp" style="border-radius:8px"><button class="btn btn-outline btn-sm" style="margin-top:12px">Replace image</button>',
            ],
            [
                '<input form="event-editor-form" name="title" value="'.e($event->title).'" required>',
                '<textarea form="event-editor-form" name="summary">'.e($event->summary ?? '').'</textarea>',
                '<input form="event-editor-form" name="starts_at" type="datetime-local" value="'.e($event->starts_at->format('Y-m-d\TH:i')).'" required>',
                '<input form="event-editor-form" name="ends_at" type="datetime-local" value="'.e($event->ends_at?->format('Y-m-d\TH:i') ?? '').'">',
                '<input form="event-editor-form" name="venue_name" value="'.e($event->venue_name ?? '').'">',
                '<textarea class="editor-area" form="event-editor-form" name="body" style="min-height:180px">'.e($event->body ?? '').'</textarea>',
                '<select form="event-editor-form" name="publication_status">'.$this->publicationStatusOptions($event->publication_status).'</select>',
                '<select form="event-editor-form" name="event_category_id">'.$this->eventCategoryOptions($event->event_category_id).'</select>',
                '<span class="small muted">Published upcoming events appear automatically on the home page.</span>',
                '<a class="btn btn-outline btn-sm" href="'.e(route('events.calendar', ['event' => $event->slug])).'" download>Download calendar preview</a>',
                $this->featuredImagePicker('event-editor-form', $media, $availableMedia, 'event'),
            ],
            $html
        );

        $html = str_replace(
            '<div class="admin-form-grid">',
            $this->adminEditorFeedback().'<div class="admin-form-grid">',
            $html
        );

        return str_replace(
            '</main>',
            '<form id="event-editor-form" method="POST" action="'.e(route('admin.events.update', $event)).'" enctype="multipart/form-data"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form></main>',
            $html
        );
    }

    private function injectAdminGalleryData(string $html): string
    {
        $rows = Gallery::query()
            ->with('category')
            ->withCount('items')
            ->latest('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (Gallery $gallery): string => '<tr><td><input type="checkbox" aria-label="Select '.e($gallery->title).'"></td><td><b>'.e($gallery->title).'</b><div class="small muted">'.e($gallery->description ?? 'No description supplied').'</div></td><td>'.e($gallery->items_count).' images</td><td>'.e($gallery->category?->name ?? 'Uncategorised').'</td><td><span class="status '.$this->statusClass($gallery->status).'">'.e($this->statusLabel($gallery->status)).'</span></td><td>'.e($gallery->event_date?->format('d M Y') ?? 'Pending').'</td><td><div class="row-actions">'.$this->publicPreviewAction($gallery->status === 'published', route('gallery'), $gallery->title).'<a class="icon-btn" href="'.e(route('admin.gallery.editor', ['gallery' => $gallery->slug])).'" title="Edit '.e($gallery->title).'" aria-label="Edit '.e($gallery->title).'">✎</a></div></td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="7"><b>No galleries found</b></td></tr>');

        $html = $this->injectCreatePanel(
            $html,
            '<a class="btn btn-primary btn-sm" href="/admin/gallery/editor">＋ Create album</a>',
            'gallery-create-panel',
            '＋ Create gallery',
            route('admin.gallery.store'),
            '<div class="admin-field"><label for="new-gallery-title">Gallery title</label><input id="new-gallery-title" name="title" required maxlength="255"></div><div class="admin-field"><label for="new-gallery-description">Description</label><textarea id="new-gallery-description" name="description" maxlength="1000"></textarea></div>',
            Gallery::class,
        );

        return $this->replaceRecordCount($html, Gallery::query()->count());
    }

    private function injectAdminGalleryEditorData(string $html): string
    {
        $requestedSlug = request()->query('gallery', 'school-life-gallery');
        $gallery = Gallery::query()
            ->with(['category', 'items.media'])
            ->where('slug', $requestedSlug)
            ->first()
            ?? Gallery::query()->with(['category', 'items.media'])->orderBy('id')->firstOrFail();

        $availableMedia = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->whereNotIn('id', $gallery->items->pluck('media_id'))
            ->when($gallery->status === 'published', fn ($query) => $query
                ->publiclyVisible()
                ->whereNotNull('alt_text'))
            ->orderBy('original_name')
            ->limit(200)
            ->get()
            ->filter(fn (Media $media): bool => $media->storedFileExists())
            ->take(100)
            ->values();
        $mediaOptions = $availableMedia
            ->map(function (Media $media): string {
                $approval = $media->isPubliclyAvailable() ? 'approved' : 'review required';

                return '<option value="'.$media->id.'">'.e($media->original_name).' · '.e($approval).'</option>';
            })
            ->implode('');
        $addDisabled = $availableMedia->isEmpty() ? ' disabled' : '';

        $html = str_replace(
            '<h1>Edit gallery</h1><p class="muted">Creative arts day · drag, caption and review images.</p>',
            '<h1>Edit gallery</h1><p class="muted">'.e($gallery->title).' · drag, caption and review images.</p>',
            $html
        );

        $html = str_replace(
            '<button class="btn btn-primary btn-sm" data-toast="Gallery saved">Save gallery</button>',
            '<button class="btn btn-primary btn-sm" type="submit" form="gallery-editor-form">Save gallery</button>',
            $html
        );

        $html = str_replace(
            '<button class="btn btn-primary btn-sm">＋ Add images</button>',
            '<button class="btn btn-primary btn-sm" type="submit" form="gallery-image-add-form"'.$addDisabled.'>＋ Add image</button>',
            $html
        );

        $html = str_replace(
            '<input value="Creative arts day"><select><option>Published</option><option>Draft</option></select>',
            '<input form="gallery-editor-form" name="title" value="'.e($gallery->title).'" required><select form="gallery-editor-form" name="status">'.$this->publicationStatusOptions($gallery->status).'</select>',
            $html
        );

        $items = $gallery->items
            ->values()
            ->map(function ($item, int $index) use ($gallery): string {
                $media = $item->media;
                $fileAvailable = $media?->storedFileExists() ?? false;
                $src = $media && $media->mime_type && str_starts_with($media->mime_type, 'image/') && $fileAvailable
                    ? $media->adminPreviewUrl()
                    : asset('assets/scb/images/logo-original.jpg');
                $alt = $fileAvailable
                    ? ($media?->alt_text ?? $item->caption ?? 'Gallery image')
                    : 'Preview unavailable for '.($media?->original_name ?? 'gallery image');
                $approval = ! $fileAvailable
                    ? 'File unavailable'
                    : ($media?->consent_required && ! $media?->consent_confirmed
                        ? 'Consent pending'
                        : ($media?->publication_restricted ? 'Restricted' : 'Approved'));

                $removeForm = '<form method="POST" action="'.e(route('admin.gallery-items.destroy', [$gallery, $item])).'" onsubmit="return confirm(\'Remove this image from the gallery?\')"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="DELETE"><button class="icon-btn" type="submit" title="Remove from gallery" aria-label="Remove from gallery">−</button></form>';
                $deleteForm = $media && Gate::allows('delete', $media)
                    ? '<form method="POST" action="'.e(route('admin.gallery-items.destroy-media', [$gallery, $item])).'" onsubmit="return confirm(\'Delete this media record and stored file? This cannot be undone.\')"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="DELETE"><button class="icon-btn" type="submit" title="Delete unused media" aria-label="Delete unused media">×</button></form>'
                    : '';

                return '<div class="media-item"><img src="'.e($src).'" alt="'.e($alt).'"><div><input form="gallery-editor-form" type="hidden" name="items['.$index.'][id]" value="'.$item->id.'"><b class="small"><input form="gallery-editor-form" name="items['.$index.'][caption]" value="'.e($item->caption ?? $media?->original_name ?? 'Gallery image').'" placeholder="Caption"></b><div class="small muted">↕ '.e($media?->original_name ?? 'Stored media').' · '.e($approval).'</div><label class="check"><input form="gallery-editor-form" type="radio" name="featured_item_id" value="'.$item->id.'" '.($item->is_featured ? 'checked' : '').'> Cover image</label><label class="small muted">Order <input form="gallery-editor-form" type="number" name="items['.$index.'][sort_order]" value="'.e((string) $item->sort_order).'" min="0" max="9999" style="width:74px"></label><div class="row-actions" style="padding:8px 0 0">'.$removeForm.$deleteForm.'</div></div></div>';
            })
            ->implode('');

        $html = preg_replace(
            '/<div class="media-grid" style="margin-top:18px">.*<\/div><\/div><\/div><\/div><\/main>/s',
            '<div class="media-grid" style="margin-top:18px">'.($items ?: '<div class="media-item"><div><b class="small">No gallery items yet</b></div></div>').'</div></div></div></div></main>',
            $html,
            1
        ) ?? $html;

        $addForm = '<form id="gallery-image-add-form" method="POST" action="'.e(route('admin.gallery-items.store', $gallery)).'" class="toolbar" style="margin-bottom:18px"><input type="hidden" name="_token" value="'.csrf_token().'"><select name="media_id" required'.$addDisabled.'><option value="">'.($availableMedia->isEmpty() ? 'No eligible images available' : 'Choose an image from the media library').'</option>'.$mediaOptions.'</select><input name="caption" maxlength="500" placeholder="Optional caption"'.$addDisabled.'><label class="check"><input name="is_featured" type="checkbox" value="1"'.$addDisabled.'> Set as cover</label></form>';

        $html = str_replace(
            '<div class="admin-panel" style="margin-top:0">',
            $this->adminEditorFeedback().'<div class="admin-panel" style="margin-top:0">',
            $html
        );
        $html = str_replace(
            '<div class="panel-body"><div class="notice">',
            '<div class="panel-body">'.$addForm.'<div class="notice">',
            $html
        );

        return str_replace(
            '</main>',
            '<form id="gallery-editor-form" method="POST" action="'.e(route('admin.gallery.update', $gallery)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><textarea name="description" hidden>'.e($gallery->description ?? '').'</textarea><input name="gallery_category_id" type="hidden" value="'.e((string) ($gallery->gallery_category_id ?? '')).'"><input name="event_date" type="hidden" value="'.e($gallery->event_date?->toDateString() ?? '').'"></form></main>',
            $html
        );
    }

    private function injectAdminAdmissionsData(string $html): string
    {
        $html = str_replace(
            '<a class="btn btn-primary btn-sm" href="#">＋ Export CSV</a>',
            '<a class="btn btn-primary btn-sm" href="'.e(route('admin.admissions.export')).'">＋ Export CSV</a>',
            $html,
        );

        $rows = AdmissionEnquiry::query()
            ->with('notes')
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (AdmissionEnquiry $enquiry): string => '<tr><td><input type="checkbox"></td><td><b>'.e($enquiry->reference_code).'</b><div class="small muted">'.e($enquiry->guardian_name).'</div>'.$this->admissionNoteForm($enquiry).'</td><td>'.e($enquiry->intended_level ?? 'Not specified').'</td><td>'.e((string) ($enquiry->intended_year ?? 'Pending')).'</td><td><span class="status '.$this->statusClass($enquiry->status).'">'.e($this->statusLabel($enquiry->status)).'</span></td><td>'.e($enquiry->created_at?->format('d M Y H:i') ?? 'Pending').'</td><td>'.$this->admissionStatusActions($enquiry).'</td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="7"><b>No admission enquiries found</b></td></tr>');

        return $this->replaceRecordCount($html, AdmissionEnquiry::query()->count());
    }

    private function injectAdminContactMessagesData(string $html): string
    {
        $html = str_replace(
            '<a class="btn btn-primary btn-sm" href="#">＋ Export</a>',
            '<a class="btn btn-primary btn-sm" href="'.e(route('admin.contact-messages.export')).'">＋ Export</a>',
            $html,
        );

        $rows = ContactMessage::query()
            ->with('notes')
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (ContactMessage $message): string => '<tr><td><input type="checkbox"></td><td><b>'.e($message->subject).'</b><div class="small muted">'.e($message->full_name).' · '.e($message->email).'</div>'.$this->contactNoteForm($message).'</td><td>'.e($this->messageTopic($message->subject)).'</td><td><span class="status '.$this->statusClass($message->status).'">'.e($this->statusLabel($message->status)).'</span></td><td>'.e($message->created_at?->format('d M Y H:i') ?? 'Pending').'</td><td>'.$this->contactMessageStatusActions($message).'</td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="6"><b>No contact messages found</b></td></tr>');

        return $this->replaceRecordCount($html, ContactMessage::query()->count());
    }

    private function injectAdminDownloadsData(string $html): string
    {
        $categories = DownloadCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $html = str_replace(
            '<a class="btn btn-primary btn-sm" href="#">＋ Upload document</a>',
            '<button class="btn btn-primary btn-sm" type="button" onclick="document.getElementById(\'download-upload-panel\').open=true;document.getElementById(\'download-upload-panel\').scrollIntoView({behavior:\'smooth\'})">＋ Upload document</button>',
            $html,
        );

        $html = str_replace(
            '<div class="admin-panel">',
            $this->adminEditorFeedback().$this->downloadUploadPanel($categories).'<div class="admin-panel" style="margin-top:0">',
            $html,
        );

        $rows = Download::query()
            ->with(['category', 'media'])
            ->latest('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (Download $download): string => '<tr><td><input type="checkbox"></td><td>'.$this->downloadMetadataFormOpening($download).'<b><input form="download-metadata-form-'.$download->id.'" name="title" value="'.e($download->title).'" required></b><div class="small muted"><input form="download-metadata-form-'.$download->id.'" name="description" value="'.e($download->description ?? '').'" placeholder="Description"></div></td><td>'.$this->downloadCategorySelect($download, $categories).'</td><td>'.e(strtoupper($download->media?->extension ?? 'FILE')).' · '.e($this->formatBytes((int) ($download->media?->size_bytes ?? 0))).'<div class="small muted"><input form="download-metadata-form-'.$download->id.'" name="version" value="'.e($download->version ?? '').'" placeholder="Version"></div>'.$this->downloadReplacementPanel($download).'</td><td><span class="status '.$this->statusClass($download->status).'">'.e($this->statusLabel($download->status)).'</span></td><td><input form="download-metadata-form-'.$download->id.'" name="publication_date" type="date" value="'.e($download->publication_date?->format('Y-m-d') ?? '').'" aria-label="'.e($download->title).' publication date"><div class="small muted">'.e($download->updated_at?->format('d M Y') ?? 'Pending').'</div></td><td>'.$this->downloadStatusActions($download).'</td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="7"><b>No downloads found</b></td></tr>');

        return $this->replaceRecordCount($html, Download::query()->count());
    }

    private function injectAdminStaffData(string $html): string
    {
        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rows = StaffMember::query()
            ->with('department')
            ->orderBy('sort_order')
            ->limit(12)
            ->get()
            ->map(fn (StaffMember $member): string => '<tr><td><input type="checkbox"></td><td>'.$this->staffMetadataFormOpening($member).'<b><input form="staff-metadata-form-'.$member->id.'" name="name" value="'.e($member->name).'" required></b><div class="small muted"><input form="staff-metadata-form-'.$member->id.'" name="job_title" value="'.e($member->job_title ?? '').'" placeholder="Staff role"></div></td><td>'.$this->departmentSelect($member, $departments).'</td><td><span class="status '.($member->is_active ? 'status-published' : 'status-archived').'">'.($member->is_active ? ($member->is_public ? 'Public' : 'Private') : 'Inactive').'</span></td><td><input form="staff-metadata-form-'.$member->id.'" name="sort_order" type="number" min="0" max="9999" value="'.e((string) $member->sort_order).'" style="width:76px"></td><td>'.$this->staffVisibilityActions($member).'</td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="6"><b>No staff records found</b></td></tr>');

        $departmentOptions = '<option value="">No department</option>'.$departments
            ->map(fn (Department $department): string => '<option value="'.$department->id.'">'.e($department->name).'</option>')
            ->implode('');
        $html = $this->injectCreatePanel(
            $html,
            '<a class="btn btn-primary btn-sm" href="#">＋ Add staff profile</a>',
            'staff-create-panel',
            '＋ Add staff profile',
            route('admin.staff.store'),
            '<div class="admin-field"><label for="new-staff-name">Name</label><input id="new-staff-name" name="name" required maxlength="190"></div><div class="admin-field"><label for="new-staff-role">Job title</label><input id="new-staff-role" name="job_title" required maxlength="190"></div><div class="admin-field"><label for="new-staff-department">Department</label><select id="new-staff-department" name="department_id">'.$departmentOptions.'</select></div>',
            StaffMember::class,
        );

        return $this->replaceRecordCount($html, StaffMember::query()->count());
    }

    private function injectAdminProgrammesData(string $html): string
    {
        $rows = Programme::query()
            ->with('department')
            ->orderBy('sort_order')
            ->limit(12)
            ->get()
            ->map(fn (Programme $programme): string => '<tr><td><input type="checkbox"></td><td>'.$this->programmeMetadataFormOpening($programme).'<b><input form="programme-metadata-form-'.$programme->id.'" name="name" value="'.e($programme->name).'" required></b></td><td><input form="programme-metadata-form-'.$programme->id.'" name="level" value="'.e($programme->level ?? $programme->programme_type ?? '').'"></td><td><input form="programme-metadata-form-'.$programme->id.'" name="summary" value="'.e($programme->summary ?? $programme->department?->name ?? '').'" placeholder="Content summary"></td><td><span class="status '.$this->statusClass($programme->status).'">'.e($this->statusLabel($programme->status)).'</span></td><td>'.$this->programmeStatusActions($programme).'</td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="6"><b>No programmes found</b></td></tr>');

        $html = $this->injectCreatePanel(
            $html,
            '<a class="btn btn-primary btn-sm" href="#">＋ Create programme</a>',
            'programme-create-panel',
            '＋ Create programme',
            route('admin.programmes.store'),
            '<div class="admin-field"><label for="new-programme-name">Programme name</label><input id="new-programme-name" name="name" required maxlength="190"></div><div class="admin-field"><label for="new-programme-type">Programme type</label><input id="new-programme-type" name="programme_type" required maxlength="64" placeholder="Primary, early years, co-curricular"></div><div class="admin-field"><label for="new-programme-level">Level</label><input id="new-programme-level" name="level" maxlength="100"></div><div class="admin-field admin-field--wide"><label for="new-programme-summary">Summary</label><textarea id="new-programme-summary" name="summary" maxlength="500"></textarea></div>',
            Programme::class,
        );

        return $this->replaceRecordCount($html, Programme::query()->count());
    }

    private function injectAdminMediaData(string $html): string
    {
        $html = str_replace(
            '<button class="btn btn-primary btn-sm" data-toast="Upload panel opened">＋ Upload media</button>',
            '<button class="btn btn-primary btn-sm" type="button" onclick="document.getElementById(\'media-upload-panel\').toggleAttribute(\'open\')">＋ Upload media</button>',
            $html
        );

        $html = str_replace(
            '<div class="admin-panel" style="margin-top:0">',
            $this->adminEditorFeedback().$this->mediaUploadPanel().'<div class="admin-panel" style="margin-top:0">',
            $html
        );

        $search = trim((string) request()->query('q', ''));
        $type = (string) request()->query('type', 'all');
        $status = (string) request()->query('status', 'all');
        $sort = (string) request()->query('sort', 'newest');

        $mediaRecords = Media::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('original_name', 'like', '%'.$search.'%')
                    ->orWhere('alt_text', 'like', '%'.$search.'%')
                    ->orWhere('caption', 'like', '%'.$search.'%');
            }))
            ->when($type === 'images', fn ($query) => $query->where('mime_type', 'like', 'image/%'))
            ->when($type === 'documents', fn ($query) => $query->where('mime_type', 'not like', 'image/%'))
            ->when($status === 'public', fn ($query) => $query->publiclyVisible())
            ->when($status === 'review', fn ($query) => $query->where('publication_restricted', true))
            ->when($status === 'consent', fn ($query) => $query->where('consent_required', true)->where('consent_confirmed', false))
            ->when($sort === 'name', fn ($query) => $query->orderBy('original_name'))
            ->when($sort === 'size', fn ($query) => $query->orderByDesc('size_bytes'))
            ->when(! in_array($sort, ['name', 'size'], true), fn ($query) => $query->latest('updated_at'))
            ->paginate(24)
            ->withQueryString();

        $items = $mediaRecords
            ->map(function (Media $media): string {
                $isImage = $media->isImage();
                $fileAvailable = $media->storedFileExists();
                $preview = ! $fileAvailable
                    ? '<div class="media-card__preview media-card__preview--file" role="img" aria-label="Stored file unavailable"><span>!</span><b>File unavailable</b></div>'
                    : ($isImage
                    ? '<a class="media-card__preview" href="'.e($media->adminPreviewUrl()).'" target="_blank" rel="noopener"><img src="'.e($media->adminPreviewUrl()).'" alt="'.e($media->alt_text ?? $media->original_name).'" loading="lazy" decoding="async"></a>'
                    : '<a class="media-card__preview media-card__preview--file" href="'.e($media->adminPreviewUrl()).'" target="_blank" rel="noopener"><span>'.e(strtoupper($media->extension)).'</span><b>Document</b></a>');

                [$approval, $statusClass] = match (true) {
                    ! $fileAvailable => ['Missing file', 'status-review'],
                    $media->is_protected_asset => ['Protected', 'status-draft'],
                    $media->consent_required && ! $media->consent_confirmed => ['Consent needed', 'status-review'],
                    $media->publication_restricted => ['In review', 'status-review'],
                    ! $media->isPubliclyAvailable() => ['Private', 'status-draft'],
                    default => ['Public', 'status-published'],
                };
                $dimensions = $media->width && $media->height ? ' · '.$media->width.'×'.$media->height : '';
                $delete = $media->is_protected_asset ? '' : $this->mediaDeleteForm($media);

                return '<article class="media-item media-card">'.$preview.'<div class="media-card__body">'
                    .'<div class="media-card__title"><div><b>'.e($media->original_name).'</b><span>'.e(strtoupper($media->extension)).$dimensions.' · '.e($this->formatBytes((int) $media->size_bytes)).'</span></div><span class="status '.$statusClass.'">'.$approval.'</span></div>'
                    .$this->mediaPublicationActions($media)
                    .'<details class="media-card__details"><summary>Edit details</summary><div class="media-card__editor">'.$this->mediaMetadataFormOpening($media)
                    .'<div class="admin-field"><label for="media-name-'.$media->id.'">File label</label><input id="media-name-'.$media->id.'" form="media-metadata-form-'.$media->id.'" name="original_name" value="'.e($media->original_name).'" required></div>'
                    .'<div class="admin-field"><label for="media-alt-'.$media->id.'">Alternative text'.($isImage ? ' (required)' : '').'</label><input id="media-alt-'.$media->id.'" form="media-metadata-form-'.$media->id.'" name="alt_text" value="'.e($media->alt_text ?? '').'" '.($isImage ? 'required ' : '').'maxlength="500"></div>'
                    .'<div class="admin-field"><label for="media-caption-'.$media->id.'">Caption</label><input id="media-caption-'.$media->id.'" form="media-metadata-form-'.$media->id.'" name="caption" value="'.e($media->caption ?? '').'" maxlength="1000"></div>'
                    .'<div class="admin-field"><label for="media-credit-'.$media->id.'">Credit</label><input id="media-credit-'.$media->id.'" form="media-metadata-form-'.$media->id.'" name="credit" value="'.e($media->credit ?? '').'" maxlength="255"></div>'
                    .'<details class="media-governance"><summary>Consent and safeguarding</summary><label class="check"><input form="media-metadata-form-'.$media->id.'" type="hidden" name="consent_required" value="0"><input form="media-metadata-form-'.$media->id.'" type="checkbox" name="consent_required" value="1" '.($media->consent_required ? 'checked' : '').'> Consent required</label><label class="check"><input form="media-metadata-form-'.$media->id.'" type="hidden" name="consent_confirmed" value="0"><input form="media-metadata-form-'.$media->id.'" type="checkbox" name="consent_confirmed" value="1" '.($media->consent_confirmed ? 'checked' : '').'> Consent confirmed</label><div class="admin-field"><label for="media-consent-'.$media->id.'">Consent reference</label><input id="media-consent-'.$media->id.'" form="media-metadata-form-'.$media->id.'" name="consent_reference" value="'.e($media->consent_reference ?? '').'" maxlength="255"></div></details>'
                    .'<button class="btn btn-primary btn-sm" type="submit" form="media-metadata-form-'.$media->id.'">Save details</button>'
                    .$this->mediaReplacementPanel($media).$delete.'</div></details></div></article>';
            })
            ->implode('');

        $filters = '<form class="media-library-toolbar" method="GET" action="'.e(route('admin.media')).'">'
            .'<div class="admin-field"><label for="media-search">Search</label><input id="media-search" name="q" value="'.e($search).'" placeholder="Filename, alt text or caption"></div>'
            .'<div class="admin-field"><label for="media-type">Type</label><select id="media-type" name="type"><option value="all">All files</option><option value="images" '.($type === 'images' ? 'selected' : '').'>Images</option><option value="documents" '.($type === 'documents' ? 'selected' : '').'>Documents</option></select></div>'
            .'<div class="admin-field"><label for="media-status">Status</label><select id="media-status" name="status"><option value="all">All statuses</option><option value="public" '.($status === 'public' ? 'selected' : '').'>Public</option><option value="review" '.($status === 'review' ? 'selected' : '').'>In review</option><option value="consent" '.($status === 'consent' ? 'selected' : '').'>Consent needed</option></select></div>'
            .'<div class="admin-field"><label for="media-sort">Order</label><select id="media-sort" name="sort"><option value="newest">Newest</option><option value="name" '.($sort === 'name' ? 'selected' : '').'>Name</option><option value="size" '.($sort === 'size' ? 'selected' : '').'>Largest</option></select></div>'
            .'<button class="btn btn-primary btn-sm" type="submit">Apply</button><a class="btn btn-outline btn-sm" href="'.e(route('admin.media')).'">Reset</a></form>';

        $panelHead = '<div class="panel-head media-library-head"><div>'.$filters.'</div><span class="small muted">'.$mediaRecords->total().' files · '.Media::query()->publiclyVisible()->count().' public</span></div><div class="panel-body">';
        $html = preg_replace_callback(
            '/(<div class="admin-panel" style="margin-top:0">)<div class="panel-head">.*?<\/div><div class="panel-body">/s',
            fn (array $matches): string => $matches[1].$panelHead,
            $html,
            1,
        ) ?? $html;

        $pagination = $mediaRecords->hasPages()
            ? '<nav class="media-pagination" aria-label="Media library pages">'.($mediaRecords->onFirstPage() ? '<span></span>' : '<a class="btn btn-outline btn-sm" href="'.e($mediaRecords->previousPageUrl()).'">← Previous</a>').'<span>Page '.$mediaRecords->currentPage().' of '.$mediaRecords->lastPage().'</span>'.($mediaRecords->hasMorePages() ? '<a class="btn btn-outline btn-sm" href="'.e($mediaRecords->nextPageUrl()).'">Next →</a>' : '<span></span>').'</nav>'
            : '';

        return preg_replace(
            '/<div class="media-grid">.*<\/div><\/div><\/div><\/div><\/main>/s',
            '<div class="media-grid">'.($items ?: '<div class="empty-state"><b>No matching media</b><p class="small muted">Change the filters or upload a new file.</p></div>').'</div>'.$pagination.'</div></div></div></div></main>',
            $html,
            1
        ) ?? $html;
    }

    private function injectAdminUsersData(string $html): string
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $rows = User::query()
            ->with('roles')
            ->latest('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (User $user): string => '<tr><td><input type="checkbox"></td><td>'.$this->userProfileFormOpening($user).'<b><input form="user-profile-form-'.$user->id.'" name="name" value="'.e($user->name).'" required></b><div class="small muted"><input form="user-profile-form-'.$user->id.'" name="email" type="email" value="'.e($user->email).'" required></div></td><td>'.$this->userRoleSelector($user, $roles).'</td><td><span class="status '.($user->is_active ? 'status-published' : 'status-archived').'">'.($user->is_active ? 'Active' : 'Disabled').'</span></td><td>'.e($user->last_login_at?->format('d M Y H:i') ?? 'Never').'</td><td>'.$this->userStatusActions($user).'</td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="6"><b>No users found</b></td></tr>');
        $html = str_replace(
            '<a class="btn btn-primary btn-sm" href="#">＋ Invite user</a>',
            $this->userInviteForm($roles),
            $html
        );

        return $this->replaceRecordCount($html, User::query()->count());
    }

    private function injectAdminRolesData(string $html): string
    {
        $roles = Role::query()
            ->with(['permissions', 'users'])
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        if ($roles->isEmpty()) {
            $replacement = '<div data-tabs-root><div class="admin-panel" style="margin-top:0"><div class="panel-body"><p>No roles have been created yet.</p></div></div></div>';
        } else {
            $tabs = $roles
                ->values()
                ->map(fn (Role $role, int $index): string => '<button'.($index === 0 ? ' class="active"' : '').' data-tab="role-'.e($role->slug).'">'.e($role->name).'</button>')
                ->implode('');

            $panes = $roles
                ->values()
                ->map(function (Role $role, int $index) use ($permissions): string {
                    $assigned = $role->permissions->pluck('slug')->all();
                    $formId = 'role-permissions-form-'.$role->id;

                    $groups = $permissions
                        ->map(function ($groupPermissions, string $group) use ($assigned, $formId): string {
                            $checks = $groupPermissions
                                ->map(fn (Permission $permission): string => '<label class="check"><input form="'.e($formId).'" type="checkbox" name="permission_ids[]" value="'.e((string) $permission->id).'" '.(in_array($permission->slug, $assigned, true) ? 'checked' : '').'> '.e($permission->name).'</label>')
                                ->implode('');

                            return '<div class="permission-group"><h3>'.e(str($group)->headline()).'</h3>'.$checks.'</div>';
                        })
                        ->implode('');

                    $description = $role->description ?: 'Custom access role.';
                    $userCount = $role->users->count();

                    return '<div class="tab-pane'.($index === 0 ? ' active' : '').'" id="role-'.e($role->slug).'"><div class="admin-panel" style="margin-top:0"><div class="panel-head"><div><h2>'.e($role->name).' permissions</h2><div class="small muted">'.e($description).' · '.$userCount.' assigned '.str('user')->plural($userCount).'</div></div><button class="btn btn-primary btn-sm" type="submit" form="'.e($formId).'">Save permissions</button></div><div class="panel-body permission-grid">'.$groups.'</div></div><form id="'.e($formId).'" method="POST" action="'.e(route('admin.roles.permissions', $role)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form></div>';
                })
                ->implode('');

            $replacement = '<div data-tabs-root><div class="admin-tabs">'.$tabs.'</div>'.$panes.'</div>';
        }

        $html = preg_replace(
            '/<div data-tabs-root>.*<\/div><\/div><\/main>/s',
            $replacement.'</div></main>',
            $html,
            1
        ) ?? $html;

        return $this->injectCreatePanel(
            $html,
            '<button class="btn btn-primary btn-sm" data-toast="New role form opened">＋ Create role</button>',
            'role-create-panel',
            '＋ Create role',
            route('admin.roles.store'),
            '<div class="admin-field"><label for="new-role-name">Role name</label><input id="new-role-name" name="name" required maxlength="150"></div><div class="admin-field"><label for="new-role-description">Description</label><textarea id="new-role-description" name="description" maxlength="500"></textarea></div>',
            Role::class,
            'Creates a restricted role with no permissions',
            'Create role',
        );
    }

    private function injectAdminAuditLogData(string $html): string
    {
        $html = str_replace(
            '<a class="btn btn-primary btn-sm" href="#">＋ Export log</a>',
            '<a class="btn btn-primary btn-sm" href="'.e(route('admin.audit-log.export')).'">↓ Export log</a>',
            $html,
        );

        $rows = AuditLog::query()
            ->with('actor')
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->map(fn (AuditLog $log): string => '<tr><td><input type="checkbox" aria-label="Select audit entry '.e((string) $log->id).'"></td><td><b>'.e($this->statusLabel($log->action)).'</b><div class="small muted">'.e($log->description ?? 'No description').'</div></td><td>'.e($log->actor?->name ?? 'System').'</td><td>'.e(class_basename((string) ($log->subject_type ?? 'System'))).'</td><td>'.e($log->created_at?->format('d M Y H:i') ?? 'Pending').'</td><td>'.e($log->ip_address ?? 'local').'</td><td><div class="row-actions"><a class="icon-btn" href="'.e(route('admin.audit-log.show', $log)).'" title="View audit entry" aria-label="View audit entry">◉</a></div></td></tr>')
            ->implode('');

        $html = $this->replaceFirstTableBody($html, $rows ?: '<tr><td colspan="7"><b>No audit entries found</b></td></tr>');

        return $this->replaceRecordCount($html, AuditLog::query()->count());
    }

    private function injectAdminSettingsData(string $html): string
    {
        $settings = SiteSetting::query()
            ->get()
            ->mapWithKeys(fn (SiteSetting $setting) => [
                $setting->group_name.'.'.$setting->setting_key => $setting->value_json['value'] ?? '',
            ]);

        $html = str_replace(
            '<button class="btn btn-primary btn-sm" data-toast="Settings saved">Save settings</button>',
            '<button class="btn btn-primary btn-sm" type="submit" form="settings-form">Save settings</button>',
            $html
        );

        $replacements = [
            '/<div class="admin-field"><label>Official school name<\/label><input value=".*?"><\/div>/s' => '<div class="admin-field"><label>Official school name</label><input form="settings-form" name="school_name" value="'.e((string) ($settings['identity.school_name'] ?? 'St. Charles Borromeo Pre & Primary School')).'" required></div>',
            '/<div class="admin-field"><label>Motto \/ short statement<\/label><input value=".*?"><\/div>/s' => '<div class="admin-field"><label>Motto / short statement</label><input form="settings-form" name="motto" value="'.e((string) ($settings['identity.motto'] ?? 'Learning with purpose')).'"></div>',
            '/<div class="admin-field"><label>Default SEO title<\/label><input value=".*?"><\/div>/s' => '<div class="admin-field"><label>Default SEO title</label><input form="settings-form" name="default_title" value="'.e((string) ($settings['seo.default_title'] ?? 'St. Charles Borromeo Pre & Primary School')).'" required></div>',
            '/<div class="admin-field"><label>Default description<\/label><textarea>.*?<\/textarea><\/div>/s' => '<div class="admin-field"><label>Default description</label><textarea form="settings-form" name="default_description">'.e((string) ($settings['seo.default_description'] ?? 'Learning, school life and admissions information from St. Charles Borromeo.')).'</textarea></div>',
            '/<div class="admin-field"><label>Public email<\/label><input value=".*?"><\/div>/s' => '<div class="admin-field"><label>Public email</label><input form="settings-form" name="primary_email" value="'.e((string) ($settings['contact.primary_email'] ?? '')).'"></div>',
            '/<div class="admin-field"><label>Public phone<\/label><input value=".*?"><\/div>/s' => '<div class="admin-field"><label>Public phone</label><input form="settings-form" name="telephone" value="'.e((string) ($settings['contact.telephone'] ?? '+27 00 000 0000')).'"></div>',
            '/<div class="admin-field"><label>Address<\/label><textarea>.*?<\/textarea><\/div>/s' => '<div class="admin-field"><label>Address</label><textarea form="settings-form" name="address">'.e((string) ($settings['contact.address'] ?? '')).'</textarea></div>',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html, 1) ?? $html;
        }

        $analyticsEnabled = filter_var($settings['analytics.enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $analyticsProvider = (string) ($settings['analytics.provider'] ?? 'none');
        $analyticsFields = '<div class="setting-row"><div><h3>Privacy-conscious analytics</h3><p class="small muted">Analytics loads only after a visitor accepts optional cookies.</p></div><div class="admin-field" style="min-width:290px"><input form="settings-form" type="hidden" name="analytics_enabled" value="0"><label class="check"><input form="settings-form" type="checkbox" name="analytics_enabled" value="1" '.($analyticsEnabled ? 'checked' : '').'> Enable analytics</label><label for="analytics-provider">Provider</label><select id="analytics-provider" form="settings-form" name="analytics_provider"><option value="none" '.($analyticsProvider === 'none' ? 'selected' : '').'>None</option><option value="plausible" '.($analyticsProvider === 'plausible' ? 'selected' : '').'>Plausible Analytics</option></select><label for="analytics-site-id">Website domain</label><input id="analytics-site-id" form="settings-form" name="analytics_site_id" value="'.e((string) ($settings['analytics.site_id'] ?? '')).'" placeholder="school.example.org"></div></div>';
        $html = preg_replace(
            '/<div class="setting-row"><div><h3>Analytics<\/h3><p class="small muted">.*?<\/p><\/div><span class="status status-draft">Disabled<\/span><\/div>/s',
            $analyticsFields,
            $html,
            1,
        ) ?? $html;

        return str_replace(
            '</main>',
            '<form id="settings-form" method="POST" action="'.e(route('admin.settings.update')).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form></main>',
            $html
        );
    }

    private function adminDashboardRows(): string
    {
        $records = collect()
            ->merge(Page::query()->latest('updated_at')->limit(2)->get()->map(fn (Page $page) => [
                'title' => $page->title,
                'type' => 'Page',
                'status' => $page->status,
                'owner' => 'Website',
                'updated' => $page->updated_at?->diffForHumans() ?? 'Pending',
            ]))
            ->merge(Post::query()->latest('updated_at')->limit(2)->get()->map(fn (Post $post) => [
                'title' => $post->title,
                'type' => 'News',
                'status' => $post->status,
                'owner' => $post->category?->name ?? 'News desk',
                'updated' => $post->updated_at?->diffForHumans() ?? 'Pending',
            ]))
            ->merge(Event::query()->latest('updated_at')->limit(2)->get()->map(fn (Event $event) => [
                'title' => $event->title,
                'type' => 'Event',
                'status' => $event->publication_status,
                'owner' => $event->category?->name ?? 'Calendar',
                'updated' => $event->updated_at?->diffForHumans() ?? 'Pending',
            ]))
            ->merge(Download::query()->latest('updated_at')->limit(2)->get()->map(fn (Download $download) => [
                'title' => $download->title,
                'type' => 'Download',
                'status' => $download->status,
                'owner' => $download->category?->name ?? 'Resources',
                'updated' => $download->updated_at?->diffForHumans() ?? 'Pending',
            ]))
            ->sortByDesc('updated')
            ->take(4);

        if ($records->isEmpty()) {
            return '<tr><td colspan="5"><b>No managed content yet</b></td></tr>';
        }

        return $records
            ->map(fn (array $record): string => '<tr><td><b>'.e($record['title']).'</b></td><td>'.e($record['type']).'</td><td><span class="status '.$this->statusClass($record['status']).'">'.e($this->statusLabel($record['status'])).'</span></td><td>'.e($record['owner']).'</td><td>'.e($record['updated']).'</td></tr>')
            ->implode('');
    }

    private function adminActivityRows(): string
    {
        $items = collect();

        if ($post = Post::query()->latest('updated_at')->first()) {
            $items->push(['icon' => '✎', 'title' => 'News story updated', 'detail' => $post->title, 'time' => $post->updated_at?->diffForHumans(null, true) ?? 'now']);
        }

        if ($event = Event::query()->latest('updated_at')->first()) {
            $items->push(['icon' => '✓', 'title' => 'Event updated', 'detail' => $event->title, 'time' => $event->updated_at?->diffForHumans(null, true) ?? 'now']);
        }

        if (Gate::allows('viewAny', AdmissionEnquiry::class) && ($admission = AdmissionEnquiry::query()->latest()->first())) {
            $items->push(['icon' => '✉', 'title' => 'Admission enquiry', 'detail' => $admission->guardian_name.' · '.$admission->intended_level, 'time' => $admission->created_at?->diffForHumans(null, true) ?? 'now']);
        }

        if (Gate::allows('viewAny', ContactMessage::class) && ($message = ContactMessage::query()->latest()->first())) {
            $items->push(['icon' => '✉', 'title' => 'Contact message', 'detail' => $message->full_name.' · '.$message->subject, 'time' => $message->created_at?->diffForHumans(null, true) ?? 'now']);
        }

        return $items
            ->take(4)
            ->map(fn (array $item): string => '<div class="activity-item"><div class="activity-dot">'.e($item['icon']).'</div><div><b class="small">'.e($item['title']).'</b><div class="small muted">'.e($item['detail']).'</div></div><span class="small muted">'.e($item['time']).'</span></div>')
            ->implode('');
    }

    private function admissionStatusActions(AdmissionEnquiry $enquiry): string
    {
        return '<div class="row-actions">'
            .'<a class="icon-btn" href="'.e(route('admin.admissions.show', $enquiry)).'" title="View enquiry" aria-label="View enquiry">◉</a>'
            .$this->statusActionForm(route('admin.admissions.status', $enquiry), 'in_progress', '◉', 'Start follow-up')
            .$this->statusActionForm(route('admin.admissions.status', $enquiry), 'responded', '✎', 'Mark responded')
            .$this->statusActionForm(route('admin.admissions.status', $enquiry), 'closed', '⋮', 'Close enquiry')
            .'</div>';
    }

    private function contactMessageStatusActions(ContactMessage $message): string
    {
        return '<div class="row-actions">'
            .'<a class="icon-btn" href="'.e(route('admin.contact-messages.show', $message)).'" title="View message" aria-label="View message">◉</a>'
            .$this->statusActionForm(route('admin.contact-messages.status', $message), 'assigned', '◉', 'Assign message')
            .$this->statusActionForm(route('admin.contact-messages.status', $message), 'responded', '✎', 'Mark responded')
            .$this->statusActionForm(route('admin.contact-messages.status', $message), 'closed', '⋮', 'Close message')
            .'</div>';
    }

    private function admissionNoteForm(AdmissionEnquiry $enquiry): string
    {
        $latestNote = $enquiry->notes
            ->sortByDesc('id')
            ->first();
        $noteSummary = $latestNote
            ? '<div class="small muted">Latest note: '.e(str($latestNote->note)->limit(80)).'</div>'
            : '';

        return '<form method="POST" action="'.e(route('admin.admissions.notes.store', $enquiry)).'" style="display:flex;gap:6px;margin-top:8px"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="is_sensitive" value="1"><input name="note" placeholder="Internal note" required><button class="icon-btn" type="submit" title="Save note" aria-label="Save note">＋</button></form>'.$noteSummary;
    }

    private function contactNoteForm(ContactMessage $message): string
    {
        $latestNote = $message->notes
            ->sortByDesc('id')
            ->first();
        $noteSummary = $latestNote
            ? '<div class="small muted">Latest note: '.e(str($latestNote->note)->limit(80)).'</div>'
            : '';

        return '<form method="POST" action="'.e(route('admin.contact-messages.notes.store', $message)).'" style="display:flex;gap:6px;margin-top:8px"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="is_sensitive" value="1"><input name="note" placeholder="Internal note" required><button class="icon-btn" type="submit" title="Save note" aria-label="Save note">＋</button></form>'.$noteSummary;
    }

    private function downloadStatusActions(Download $download): string
    {
        if ($download->status !== 'published' && ! $download->media?->storedFileExists()) {
            $statusAction = '<button class="icon-btn" type="button" title="Attach the document before publishing" aria-label="Attach the document before publishing" disabled>!</button>';
        } else {
            $statusAction = $download->status === 'published'
                ? $this->statusActionForm(route('admin.downloads.status', $download), 'archived', '⋮', 'Archive download')
                : $this->statusActionForm(route('admin.downloads.status', $download), 'published', '⋮', 'Publish download');
        }
        $previewUrl = $download->media?->adminPreviewUrl() ?? route('admin.downloads');

        return '<div class="row-actions">'
            .'<a class="icon-btn" href="'.e($previewUrl).'" target="_blank" rel="noopener" title="Preview document" aria-label="Preview document">◉</a>'
            .'<button class="icon-btn" type="submit" form="download-metadata-form-'.$download->id.'" title="Save download" aria-label="Save download">✎</button>'
            .$statusAction
            .'</div>';
    }

    private function downloadMetadataFormOpening(Download $download): string
    {
        return '<form id="download-metadata-form-'.$download->id.'" method="POST" action="'.e(route('admin.downloads.metadata', $download)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form>';
    }

    private function downloadCategorySelect(Download $download, $categories): string
    {
        $options = $this->downloadCategoryOptions($categories, $download->download_category_id);

        return '<select form="download-metadata-form-'.$download->id.'" name="download_category_id" aria-label="'.e($download->title).' category">'.$options.'</select>';
    }

    private function downloadUploadPanel($categories): string
    {
        return '<details id="download-upload-panel" class="admin-panel" style="margin:0 0 20px"><summary class="panel-head" style="cursor:pointer;list-style:none"><h2>Upload document</h2><span class="small muted">PDF, Word, Excel or OpenDocument · up to 20 MB</span></summary><div class="panel-body"><form method="POST" action="'.e(route('admin.downloads.store')).'" enctype="multipart/form-data" class="admin-fields"><input type="hidden" name="_token" value="'.csrf_token().'"><div class="admin-field"><label>Document file</label><input name="file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.odt" data-upload-max-mb="20" required></div><div class="admin-field"><label>Title</label><input name="title" maxlength="255" required></div><div class="admin-field"><label>Description</label><textarea name="description" maxlength="1000" rows="3"></textarea></div><div class="admin-field"><label>Category</label><select name="download_category_id">'.$this->downloadCategoryOptions($categories).'</select></div><div class="admin-field"><label>Version</label><input name="version" maxlength="100"></div><div class="admin-field"><label>Publication date</label><input name="publication_date" type="date"></div><button class="btn btn-primary btn-sm" type="submit">Upload for review</button></form></div></details>';
    }

    private function downloadReplacementPanel(Download $download): string
    {
        return '<details style="margin-top:8px"><summary class="btn btn-outline btn-sm" style="cursor:pointer;list-style:none;width:100%">↻ Replace document</summary><form method="POST" action="'.e(route('admin.downloads.file', $download)).'" enctype="multipart/form-data" class="admin-fields" style="margin-top:8px"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><div class="admin-field"><label>Replacement document</label><input name="file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.odt" data-upload-max-mb="20" required></div><button class="btn btn-primary btn-sm" type="submit">Upload replacement</button></form></details>';
    }

    private function downloadCategoryOptions($categories, ?int $selectedCategoryId = null): string
    {
        return '<option value="">Resource</option>'.$categories
            ->map(fn (DownloadCategory $category): string => '<option value="'.$category->id.'" '.($selectedCategoryId === $category->id ? 'selected' : '').'>'.e($category->name).'</option>')
            ->implode('');
    }

    private function staffVisibilityActions(StaffMember $member): string
    {
        $visibilityAction = $member->is_active
            ? $this->visibilityActionForm(route('admin.staff.visibility', $member), 'inactive', '⋮', 'Mark inactive')
            : $this->visibilityActionForm(route('admin.staff.visibility', $member), 'public', '⋮', 'Make public');

        return '<div class="row-actions">'
            .'<a class="icon-btn" href="/about" title="Preview staff section" aria-label="Preview staff section">◉</a>'
            .'<button class="icon-btn" type="submit" form="staff-metadata-form-'.$member->id.'" title="Save staff profile" aria-label="Save staff profile">✎</button>'
            .$visibilityAction
            .'</div>';
    }

    private function programmeStatusActions(Programme $programme): string
    {
        $statusAction = $programme->status === 'published'
            ? $this->statusActionForm(route('admin.programmes.status', $programme), 'archived', '⋮', 'Archive programme')
            : $this->statusActionForm(route('admin.programmes.status', $programme), 'published', '⋮', 'Publish programme');

        return '<div class="row-actions">'
            .'<a class="icon-btn" href="/academics" title="Preview programme" aria-label="Preview programme">◉</a>'
            .'<button class="icon-btn" type="submit" form="programme-metadata-form-'.$programme->id.'" title="Save programme" aria-label="Save programme">✎</button>'
            .$statusAction
            .'</div>';
    }

    private function staffMetadataFormOpening(StaffMember $member): string
    {
        return '<form id="staff-metadata-form-'.$member->id.'" method="POST" action="'.e(route('admin.staff.metadata', $member)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form>';
    }

    private function programmeMetadataFormOpening(Programme $programme): string
    {
        return '<form id="programme-metadata-form-'.$programme->id.'" method="POST" action="'.e(route('admin.programmes.metadata', $programme)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form>';
    }

    private function departmentSelect(StaffMember $member, $departments): string
    {
        $options = '<option value="">School</option>'.$departments
            ->map(fn (Department $department): string => '<option value="'.$department->id.'" '.($member->department_id === $department->id ? 'selected' : '').'>'.e($department->name).'</option>')
            ->implode('');

        return '<select form="staff-metadata-form-'.$member->id.'" name="department_id" aria-label="'.e($member->name).' department">'.$options.'</select>';
    }

    private function userStatusActions(User $user): string
    {
        $statusAction = $user->is_active
            ? $this->statusActionForm(route('admin.users.status', $user), 'disabled', '⋮', 'Disable user')
            : $this->statusActionForm(route('admin.users.status', $user), 'active', '⋮', 'Enable user');

        return '<div class="row-actions">'
            .'<button class="icon-btn" type="button" title="View user" aria-label="View user">◉</button>'
            .'<button class="icon-btn" type="submit" form="user-profile-form-'.$user->id.'" title="Save profile" aria-label="Save profile">✎</button>'
            .'<button class="icon-btn" type="submit" form="user-roles-form-'.$user->id.'" title="Save roles" aria-label="Save roles">⚿</button>'
            .$statusAction
            .'</div>';
    }

    private function userInviteForm($roles): string
    {
        $roleOptions = $roles
            ->map(fn (Role $role): string => '<option value="'.$role->id.'">'.e($role->name).'</option>')
            ->implode('');

        return '<form class="toolbar" method="POST" action="'.e(route('admin.users.store')).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input name="name" placeholder="Name" required><input name="email" type="email" placeholder="Email" required><input name="password" type="password" placeholder="Temporary password" required><select name="role_ids[]" aria-label="Invite user role">'.$roleOptions.'</select><button class="btn btn-primary btn-sm" type="submit">＋ Invite user</button></form>';
    }

    private function userProfileFormOpening(User $user): string
    {
        return '<form id="user-profile-form-'.$user->id.'" method="POST" action="'.e(route('admin.users.profile', $user)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form>';
    }

    private function userRoleSelector(User $user, $roles): string
    {
        $selectedRoleIds = $user->roles->pluck('id')->all();
        $options = $roles
            ->map(fn (Role $role): string => '<option value="'.$role->id.'" '.(in_array($role->id, $selectedRoleIds, true) ? 'selected' : '').'>'.e($role->name).'</option>')
            ->implode('');

        return '<form id="user-roles-form-'.$user->id.'" method="POST" action="'.e(route('admin.users.roles', $user)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><select name="role_ids[]" multiple aria-label="'.e($user->name).' roles">'.$options.'</select></form>';
    }

    private function mediaPublicationActions(Media $media): string
    {
        $publication = '';
        $fileAvailable = $media->storedFileExists();

        if (! $media->is_protected_asset) {
            $publication = $media->isPubliclyAvailable()
                ? $this->mediaPublicationForm(route('admin.media.publication', $media), 'restrict', '◌', 'Make private')
                : ($fileAvailable
                    ? $this->mediaPublicationForm(route('admin.media.publication', $media), 'approve', '✓', 'Publish')
                    : '');
        }

        $preview = $fileAvailable
            ? '<a class="btn btn-outline btn-sm" href="'.e($media->adminPreviewUrl()).'" target="_blank" rel="noopener">View</a>'
            : '<span class="btn btn-outline btn-sm" aria-disabled="true">Unavailable</span>';

        return '<div class="media-card__actions">'.$preview.$publication.'</div>';
    }

    private function mediaDeleteForm(Media $media): string
    {
        return '<form method="POST" action="'.e(route('admin.media.destroy', $media)).'" class="media-delete-form"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="DELETE"><button class="btn btn-outline btn-sm" type="submit" onclick="return confirm(\'Delete this media file? This cannot be undone.\')">Delete media</button></form>';
    }

    private function mediaMetadataFormOpening(Media $media): string
    {
        return '<form id="media-metadata-form-'.$media->id.'" method="POST" action="'.e(route('admin.media.metadata', $media)).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"></form>';
    }

    private function mediaUploadPanel(): string
    {
        return '<details id="media-upload-panel" class="admin-panel media-upload-panel" style="margin:0 0 20px"><summary class="panel-head"><div><h2>Upload media</h2><span class="small muted">Images become optimized responsive WebP files automatically</span></div><span class="btn btn-primary btn-sm">Choose file</span></summary><div class="panel-body"><form method="POST" action="'.e(route('admin.media.store')).'" enctype="multipart/form-data" class="media-upload-form"><input type="hidden" name="_token" value="'.csrf_token().'"><div class="admin-field"><label for="new-media-file">Image or document</label><input id="new-media-file" name="file" type="file" accept="image/jpeg,image/png,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.odt" data-upload-max-mb="10" data-media-upload-file required><span class="small muted">JPEG, PNG, WebP, PDF, Word, Excel or ODT · maximum 10 MB</span></div><div class="admin-field"><label for="new-media-alt">Alternative text</label><input id="new-media-alt" name="alt_text" maxlength="500" placeholder="Describe what the image shows" data-media-upload-alt><span class="small muted">Required for images; leave blank for documents.</span></div><details class="media-upload-optional"><summary>Optional details and consent</summary><div class="admin-fields"><div class="admin-field"><label for="new-media-caption">Caption</label><input id="new-media-caption" name="caption" maxlength="1000"></div><div class="admin-field"><label for="new-media-credit">Credit</label><input id="new-media-credit" name="credit" maxlength="255"></div><label class="check"><input type="hidden" name="consent_required" value="0"><input name="consent_required" type="checkbox" value="1"> Consent required</label><label class="check"><input type="hidden" name="consent_confirmed" value="0"><input name="consent_confirmed" type="checkbox" value="1"> Consent confirmed</label><div class="admin-field"><label for="new-media-consent">Consent reference</label><input id="new-media-consent" name="consent_reference" maxlength="255"></div></div></details><button class="btn btn-primary btn-sm" type="submit">Upload for review</button></form></div></details>';
    }

    private function mediaReplacementPanel(Media $media): string
    {
        if ($media->is_protected_asset) {
            return '';
        }

        return '<details style="margin-top:8px"><summary class="btn btn-outline btn-sm" style="cursor:pointer;list-style:none;width:100%">↻ Replace file</summary><form method="POST" action="'.e(route('admin.media.replace', $media)).'" enctype="multipart/form-data" class="admin-fields" style="margin-top:8px"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><div class="admin-field"><label>Replacement file</label><input name="file" type="file" accept="image/jpeg,image/png,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.odt" data-upload-max-mb="10" required></div><button class="btn btn-primary btn-sm" type="submit">Upload replacement</button></form></details>';
    }

    private function availableImageMedia()
    {
        return Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->orderBy('publication_restricted')
            ->orderByDesc('updated_at')
            ->get()
            ->filter(fn (Media $media): bool => Gate::allows('view', $media) && $media->storedFileExists())
            ->values();
    }

    private function featuredImagePicker(
        string $formId,
        ?Media $currentMedia,
        $availableMedia,
        string $contentLabel,
    ): string {
        $fieldPrefix = str($formId)->beforeLast('-form')->toString();
        $currentFileAvailable = $currentMedia?->storedFileExists() ?? false;
        $previewUrl = $currentFileAvailable ? $currentMedia->adminPreviewUrl() : '';
        $currentName = $currentMedia
            ? $currentMedia->original_name.($currentFileAvailable ? '' : ' (file unavailable)')
            : 'No featured image selected';
        $currentAlt = $currentMedia?->alt_text ?? '';
        $currentState = $currentMedia ? $this->mediaStateLabel($currentMedia) : 'Optional';
        $emptyLabel = $currentMedia ? 'Preview unavailable' : 'No image selected';
        $options = '<option value="" data-preview="" data-alt="" data-state="Optional">No featured image</option>'
            .$availableMedia->map(function (Media $media) use ($currentMedia): string {
                $state = $this->mediaStateLabel($media);

                return '<option value="'.$media->id.'" data-preview="'.e($media->adminPreviewUrl()).'" data-alt="'.e($media->alt_text ?? '').'" data-state="'.e($state).'" '.($currentMedia?->id === $media->id ? 'selected' : '').'>'.e($media->original_name).' · '.e($state).'</option>';
            })->implode('');

        return '<div class="featured-image-picker" data-featured-image-picker>'
            .'<div class="featured-image-preview"><img src="'.e($previewUrl).'" alt="'.e($currentAlt).'" data-featured-image-preview '.($previewUrl === '' ? 'hidden' : '').'><div class="featured-image-empty" data-featured-image-empty '.($previewUrl !== '' ? 'hidden' : '').'>'.e($emptyLabel).'</div><div><b data-featured-image-name>'.e($currentName).'</b><span class="small muted" data-featured-image-state>'.e($currentState).'</span></div></div>'
            .'<div class="admin-field"><label for="'.$fieldPrefix.'-featured-media">Choose from media library</label><select id="'.$fieldPrefix.'-featured-media" form="'.$formId.'" name="featured_media_id" data-featured-image-select>'.$options.'</select><span class="small muted">Public images can be used on published content. Review images can be attached to drafts.</span></div>'
            .'<details class="featured-image-upload"><summary>Or upload a new image</summary><div class="admin-fields">'
            .'<div class="admin-field"><label for="'.$fieldPrefix.'-featured-file">JPEG, PNG or WebP</label><input id="'.$fieldPrefix.'-featured-file" form="'.$formId.'" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" data-featured-image-file data-upload-max-mb="10"><span class="small muted">Maximum 10 MB. The file is optimized to responsive WebP and attached to this '.e($contentLabel).'.</span></div>'
            .'<div class="admin-field"><label for="'.$fieldPrefix.'-featured-alt">Alternative text</label><input id="'.$fieldPrefix.'-featured-alt" form="'.$formId.'" name="featured_image_alt_text" maxlength="500" placeholder="Describe what the image shows" data-featured-image-alt></div>'
            .'<div class="admin-field"><label for="'.$fieldPrefix.'-featured-caption">Caption (optional)</label><input id="'.$fieldPrefix.'-featured-caption" form="'.$formId.'" name="featured_image_caption" maxlength="1000"></div>'
            .'<div class="admin-field"><label for="'.$fieldPrefix.'-featured-credit">Credit (optional)</label><input id="'.$fieldPrefix.'-featured-credit" form="'.$formId.'" name="featured_image_credit" maxlength="255"></div>'
            .'<label class="check"><input form="'.$formId.'" type="hidden" name="featured_image_consent_required" value="0"><input form="'.$formId.'" name="featured_image_consent_required" type="checkbox" value="1"> Consent is required</label>'
            .'<label class="check"><input form="'.$formId.'" type="hidden" name="featured_image_consent_confirmed" value="0"><input form="'.$formId.'" name="featured_image_consent_confirmed" type="checkbox" value="1"> Consent is confirmed</label>'
            .'<div class="admin-field"><label for="'.$fieldPrefix.'-featured-consent">Consent reference</label><input id="'.$fieldPrefix.'-featured-consent" form="'.$formId.'" name="featured_image_consent_reference" maxlength="255"></div>'
            .'<label class="check"><input form="'.$formId.'" name="featured_image_safeguarding_confirmed" type="checkbox" value="1"> I confirm there is no sensitive identifying information</label>'
            .'<div class="notice small">New uploads are attached immediately but stay private until approved in the <a href="'.e(route('admin.media')).'">media library</a>.</div>'
            .'</div></details></div>';
    }

    private function mediaStateLabel(Media $media): string
    {
        return match (true) {
            ! $media->storedFileExists() => 'File unavailable',
            $media->is_protected_asset => 'Protected',
            $media->consent_required && ! $media->consent_confirmed => 'Consent needed',
            $media->publication_restricted || $media->visibility !== 'public' => 'In review',
            default => 'Public',
        };
    }

    private function featuredImageReviewPanel(?Media $media): string
    {
        if (! $media) {
            return '<div class="admin-panel"><div class="panel-head"><h2>Image review</h2></div><div class="panel-body small muted">No featured image is attached.</div></div>';
        }

        $consent = $media->consent_required
            ? ($media->consent_confirmed ? 'Consent confirmed' : 'Consent still required')
            : 'Consent not required';

        return '<div class="admin-panel"><div class="panel-head"><h2>Image review</h2><span class="status '.$this->statusClass($media->isPubliclyAvailable() ? 'published' : 'review').'">'.e($this->mediaStateLabel($media)).'</span></div><div class="panel-body"><b>'.e($media->original_name).'</b><p class="small muted">'.e($consent).' · '.e($media->alt_text ?? 'Alternative text missing').'</p><a class="btn btn-outline btn-sm" href="'.e(route('admin.media')).'">Open media library</a></div></div>';
    }

    private function pageBlockEditorPanel(PageBlock $block, int $index, int $blockCount, $availableMedia): string
    {
        $formId = 'page-block-form-'.$block->id;
        $summary = e(str(strip_tags((string) ($block->heading ?? $block->body ?? 'Configured content block')))->limit(110));

        return '<details class="admin-panel" style="'.($index === 0 ? 'margin:0' : 'margin-top:10px').'">'
            .'<summary class="panel-head" style="cursor:pointer;list-style:none"><b>↕ '.($index + 1).'. '.e(str($block->block_type)->replace('_', ' ')->headline()).'</b><div class="row-actions"><span class="icon-btn" title="Edit block" aria-label="Edit block">✎</span></div></summary>'
            .'<div class="panel-body admin-fields"><div class="small muted">'.($block->is_enabled ? 'Enabled' : 'Hidden').' · '.$summary.'</div>'
            .'<form id="'.$formId.'" method="POST" action="'.e(route('admin.page-blocks.update', $block)).'">'
            .'<input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH">'
            .$this->pageBlockFields($block, $availableMedia)
            .'<button class="btn btn-primary btn-sm" type="submit">Save block</button></form>'
            .'<div class="toolbar" style="margin-top:12px">'
            .$this->pageBlockOrderForm($block, 'up', '↑', 'Move block up', $index === 0)
            .$this->pageBlockOrderForm($block, 'down', '↓', 'Move block down', $index === $blockCount - 1)
            .$this->pageBlockVisibilityForm(
                route('admin.page-blocks.visibility', $block),
                ! $block->is_enabled,
                $block->is_enabled ? '○' : '✓',
                $block->is_enabled ? 'Hide block' : 'Show block',
            )
            .$this->pageBlockPostActionForm(route('admin.page-blocks.duplicate', $block), 'POST', '⧉', 'Duplicate block')
            .$this->pageBlockPostActionForm(route('admin.page-blocks.destroy', $block), 'DELETE', '×', 'Delete block', true)
            .'</div></div></details>';
    }

    private function pageBlockCreatePanel(Page $page, $availableMedia): string
    {
        return '<details class="admin-panel" style="margin-top:12px"><summary class="btn btn-outline btn-sm" style="cursor:pointer;list-style:none;width:max-content">＋ Add content block</summary>'
            .'<div class="panel-body admin-fields"><form method="POST" action="'.e(route('admin.page-blocks.store', $page)).'">'
            .'<input type="hidden" name="_token" value="'.csrf_token().'">'
            .$this->pageBlockFields(null, $availableMedia)
            .'<button class="btn btn-primary btn-sm" type="submit">Add block</button></form></div></details>';
    }

    private function pageBlockFields(?PageBlock $block, $availableMedia): string
    {
        $settings = json_encode(
            $block?->settings ?? [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ) ?: '{}';
        $mediaOptions = '<option value="">No image</option>'.$availableMedia
            ->map(fn (Media $media): string => '<option value="'.$media->id.'" '.($block?->media_id === $media->id ? 'selected' : '').'>'.e($media->original_name).'</option>')
            ->implode('');

        return '<div class="admin-field"><label>Block type</label><input name="block_type" list="page-block-types" value="'.e($block?->block_type ?? '').'" maxlength="64" pattern="[a-z0-9_]+" required></div>'
            .'<div class="admin-field"><label>Heading</label><input name="heading" value="'.e($block?->heading ?? '').'" maxlength="255"></div>'
            .'<div class="admin-field"><label>Subheading</label><input name="subheading" value="'.e($block?->subheading ?? '').'" maxlength="255"></div>'
            .'<div class="admin-field"><label>Body</label><textarea name="body" style="min-height:120px">'.e($block?->body ?? '').'</textarea></div>'
            .'<div class="admin-field"><label>Image</label><select name="media_id">'.$mediaOptions.'</select></div>'
            .'<div class="admin-field"><label>Block settings (JSON)</label><textarea name="settings_json" style="min-height:180px;font-family:monospace">'.e($settings).'</textarea></div>'
            .'<div class="admin-field"><label>Visible from</label><input name="visible_from" type="datetime-local" value="'.e($block?->visible_from?->format('Y-m-d\TH:i') ?? '').'"></div>'
            .'<div class="admin-field"><label>Visible until</label><input name="visible_until" type="datetime-local" value="'.e($block?->visible_until?->format('Y-m-d\TH:i') ?? '').'"></div>'
            .'<label class="check"><input type="hidden" name="is_enabled" value="0"><input name="is_enabled" value="1" type="checkbox" '.($block === null || $block->is_enabled ? 'checked' : '').'> Enabled</label>';
    }

    private function pageBlockTypeDatalist(): string
    {
        $types = [
            'hero', 'rich_text', 'image_text', 'call_to_action', 'statistics', 'feature_cards',
            'values', 'staff_list', 'programme_list', 'testimonial', 'latest_news',
            'upcoming_events', 'gallery_preview', 'downloads_list', 'faq_accordion',
            'contact_details', 'map_link', 'video_embed',
        ];

        return '<datalist id="page-block-types">'.collect($types)
            ->map(fn (string $type): string => '<option value="'.$type.'">')
            ->implode('').'</datalist>';
    }

    private function adminEditorFeedback(): string
    {
        $messages = collect();

        if (session('status')) {
            $messages->push((string) session('status'));
        }

        $messages = $messages->merge(session('errors')?->all() ?? []);

        if ($messages->isEmpty()) {
            return '';
        }

        return '<div class="admin-panel" style="margin:0 0 20px"><div class="panel-body">'.$messages
            ->map(fn (string $message): string => '<div class="small">'.e($message).'</div>')
            ->implode('').'</div></div>';
    }

    private function injectCreatePanel(
        string $html,
        string $originalTrigger,
        string $panelId,
        string $label,
        string $action,
        string $fields,
        string $modelClass,
        string $note = 'Creates a private draft in MySQL',
        string $submitLabel = 'Create draft',
    ): string {
        if (! Gate::allows('create', $modelClass)) {
            return str_replace($originalTrigger, '<span class="small muted">Creation not permitted</span>', $html);
        }

        $trigger = '<button class="btn btn-primary btn-sm" type="button" data-toggle-details="'.e($panelId).'">'.e($label).'</button>';
        $panel = $this->adminEditorFeedback().'<details id="'.e($panelId).'" class="admin-panel admin-create-panel"><summary class="panel-head"><h2>'.e($label).'</h2><span class="small muted">'.e($note).'</span></summary><div class="panel-body"><form method="POST" action="'.e($action).'" class="admin-fields"><input type="hidden" name="_token" value="'.csrf_token().'">'.$fields.'<button class="btn btn-primary btn-sm" type="submit">'.e($submitLabel).'</button></form></div></details>';

        $html = str_replace($originalTrigger, $trigger, $html);

        return preg_replace('/<div class="admin-panel">/', $panel.'<div class="admin-panel" style="margin-top:0">', $html, 1) ?? $html;
    }

    private function publicPreviewAction(bool $isPublished, string $url, string $title): string
    {
        if (! $isPublished) {
            return '<button class="icon-btn" type="button" title="Publish before viewing" aria-label="'.e($title).' is not publicly visible" disabled>◉</button>';
        }

        return '<a class="icon-btn" href="'.e($url).'" title="View '.e($title).'" aria-label="View '.e($title).'">◉</a>';
    }

    private function statusActionForm(string $action, string $status, string $icon, string $label): string
    {
        return '<form method="POST" action="'.e($action).'" style="display:inline"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="status" value="'.e($status).'"><button class="icon-btn" type="submit" title="'.e($label).'" aria-label="'.e($label).'">'.$icon.'</button></form>';
    }

    private function visibilityActionForm(string $action, string $visibility, string $icon, string $label): string
    {
        return '<form method="POST" action="'.e($action).'" style="display:inline"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="visibility" value="'.e($visibility).'"><button class="icon-btn" type="submit" title="'.e($label).'" aria-label="'.e($label).'">'.$icon.'</button></form>';
    }

    private function pageBlockVisibilityForm(string $action, bool $enabled, string $icon, string $label): string
    {
        return '<form method="POST" action="'.e($action).'" style="display:inline"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="is_enabled" value="'.($enabled ? '1' : '0').'"><button class="icon-btn" type="submit" title="'.e($label).'" aria-label="'.e($label).'">'.$icon.'</button></form>';
    }

    private function pageBlockOrderForm(PageBlock $block, string $direction, string $icon, string $label, bool $disabled): string
    {
        return '<form method="POST" action="'.e(route('admin.page-blocks.order', $block)).'" style="display:inline"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="direction" value="'.e($direction).'"><button class="icon-btn" type="submit" title="'.e($label).'" aria-label="'.e($label).'" '.($disabled ? 'disabled' : '').'>'.$icon.'</button></form>';
    }

    private function pageBlockPostActionForm(string $action, string $method, string $icon, string $label, bool $confirm = false): string
    {
        return '<form method="POST" action="'.e($action).'" style="display:inline"><input type="hidden" name="_token" value="'.csrf_token().'">'.($method === 'POST' ? '' : '<input type="hidden" name="_method" value="'.e($method).'">').'<button class="icon-btn" type="submit" title="'.e($label).'" aria-label="'.e($label).'"'.($confirm ? ' onclick="return confirm(\'Delete this page block?\')"' : '').'>'.$icon.'</button></form>';
    }

    private function mediaPublicationForm(string $action, string $publicationAction, string $icon, string $label): string
    {
        return '<form method="POST" action="'.e($action).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="action" value="'.e($publicationAction).'"><button class="btn btn-outline btn-sm" type="submit"><span aria-hidden="true">'.$icon.'</span> '.e($label).'</button></form>';
    }

    private function statusClass(string $status): string
    {
        return match ($status) {
            'published', 'responded', 'completed' => 'status-published',
            'review', 'in_progress', 'assigned' => 'status-review',
            'new' => 'status-new',
            'archived', 'closed', 'cancelled', 'spam' => 'status-archived',
            default => 'status-draft',
        };
    }

    private function statusLabel(string $status): string
    {
        return str($status)->replace('_', ' ')->title()->toString();
    }

    private function pageStatusOptions(string $currentStatus): string
    {
        return collect([
            'published' => 'Published',
            'draft' => 'Draft',
            'review' => 'Pending review',
            'archived' => 'Archived',
        ])
            ->map(fn (string $label, string $value): string => '<option value="'.e($value).'" '.($currentStatus === $value ? 'selected' : '').'>'.e($label).'</option>')
            ->implode('');
    }

    private function publicationStatusOptions(string $currentStatus, bool $includeScheduled = false): string
    {
        $statuses = [
            'published' => 'Published',
            'draft' => 'Draft',
            'review' => 'Pending review',
        ];

        if ($includeScheduled) {
            $statuses['scheduled'] = 'Scheduled';
        }

        $statuses['archived'] = 'Archived';

        return collect($statuses)
            ->map(fn (string $label, string $value): string => '<option value="'.e($value).'" '.($currentStatus === $value ? 'selected' : '').'>'.e($label).'</option>')
            ->implode('');
    }

    private function postCategoryOptions(?int $selectedCategoryId): string
    {
        return PostCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (PostCategory $category): string => '<option value="'.e((string) $category->id).'" '.($selectedCategoryId === $category->id ? 'selected' : '').'>'.e($category->name).'</option>')
            ->prepend('<option value="">Uncategorised</option>')
            ->implode('');
    }

    private function eventCategoryOptions(?int $selectedCategoryId): string
    {
        return EventCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (EventCategory $category): string => '<option value="'.e((string) $category->id).'" '.($selectedCategoryId === $category->id ? 'selected' : '').'>'.e($category->name).'</option>')
            ->prepend('<option value="">Uncategorised</option>')
            ->implode('');
    }

    private function replaceFirstTableBody(string $html, string $rows): string
    {
        return preg_replace('/<tbody>.*?<\/tbody>/s', '<tbody>'.$rows.'</tbody>', $html, 1) ?? $html;
    }

    private function replaceRecordCount(string $html, int $count): string
    {
        return str_replace(
            'Showing 1–5 of 24 records',
            'Showing '.min($count, 12).' of '.$count.' records',
            $html
        );
    }

    private function pagePath(Page $page): string
    {
        return $page->slug === 'home' ? '/' : '/'.$page->slug;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024).' KB';
        }

        return $bytes.' B';
    }

    private function messageTopic(string $subject): string
    {
        $subject = str($subject)->lower();

        return match (true) {
            $subject->contains('admission') => 'Admissions',
            $subject->contains('calendar') || $subject->contains('event') => 'Calendar',
            $subject->contains('visit') => 'School visit',
            default => 'General',
        };
    }
}
