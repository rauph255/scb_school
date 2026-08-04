<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class PrototypePageController extends Controller
{
    /**
     * Render the approved static prototype through Laravel while the Blade and
     * Filament implementations are being built out screen by screen.
     */
    public function __invoke(string $screen = 'index'): Response
    {
        $map = [
            'home' => 'index.html',
            'about' => 'about.html',
            'academics' => 'academics.html',
            'admissions' => 'admissions.html',
            'news' => 'news.html',
            'news-detail' => 'news-detail.html',
            'events' => 'events.html',
            'event-detail' => 'event-detail.html',
            'gallery' => 'gallery.html',
            'downloads' => 'downloads.html',
            'contact' => 'contact.html',
            'faq' => 'faq.html',
            'privacy' => 'privacy.html',
            'admin-login' => 'admin/login.html',
            'admin-dashboard' => 'admin/dashboard.html',
            'admin-pages' => 'admin/pages.html',
            'admin-page-editor' => 'admin/page-editor.html',
            'admin-news' => 'admin/news.html',
            'admin-news-editor' => 'admin/news-editor.html',
            'admin-events' => 'admin/events.html',
            'admin-event-editor' => 'admin/event-editor.html',
            'admin-gallery' => 'admin/gallery.html',
            'admin-gallery-editor' => 'admin/gallery-editor.html',
            'admin-downloads' => 'admin/downloads.html',
            'admin-staff' => 'admin/staff.html',
            'admin-programmes' => 'admin/programmes.html',
            'admin-admissions' => 'admin/admissions.html',
            'admin-contact-messages' => 'admin/contact-messages.html',
            'admin-media' => 'admin/media.html',
            'admin-users' => 'admin/users.html',
            'admin-roles' => 'admin/roles.html',
            'admin-settings' => 'admin/settings.html',
            'admin-audit-log' => 'admin/audit-log.html',
        ];

        abort_unless(isset($map[$screen]), 404);

        $path = base_path('reference/scb_uiux_prototype/'.$map[$screen]);

        abort_unless(File::exists($path), 404);

        $html = File::get($path);

        return response($this->prepareHtml($html), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function prepareHtml(string $html): string
    {
        $isAdmin = str_contains($html, '../assets/');

        $html = $this->rewriteAssetUrls($html);
        $html = $this->rewritePageLinks($html, $isAdmin);
        $html = str_replace('</head>', $this->creditStyles().'</head>', $html);

        if (str_contains($html, 'class="admin-login"')) {
            return str_replace('</form></div></div></body>', '</form>'.$this->creditMarkup('login').'</div></div></body>', $html);
        }

        if ($isAdmin) {
            return str_replace('</main></div><div class="toast">', $this->creditMarkup('admin').'</main></div><div class="toast">', $html);
        }

        return str_replace('</div></footer>', $this->creditMarkup('public').'</div></footer>', $html);
    }

    private function rewriteAssetUrls(string $html): string
    {
        return preg_replace(
            '/(href|src)="(?:\.\.\/)?assets\//',
            '$1="/assets/prototype/',
            $html
        );
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
            'admin/login.html' => '/admin',
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
            'login.html' => '/admin',
        ];

        $links = $isAdmin ? $adminLinks : $publicLinks;

        return str_replace(array_keys($links), array_values($links), $html);
    }

    private function creditStyles(): string
    {
        return <<<'HTML'
<style>
.falconode-credit{display:flex;align-items:center;justify-content:center;gap:10px;font-size:.68rem;font-weight:900;letter-spacing:.055em;text-transform:uppercase}
.falconode-credit span{white-space:nowrap}
.falconode-credit__logo{display:flex;align-items:center;justify-content:center;background:#fff;border:1px solid rgba(231,224,216,.95);border-radius:7px;padding:5px 9px;box-shadow:0 8px 18px rgba(61,45,63,.08)}
.falconode-credit img{width:126px;height:auto;max-height:31px;object-fit:contain}
.falconode-credit--public{margin-top:26px;padding:18px 0 0;border-top:1px solid rgba(255,255,255,.14);color:rgba(255,255,255,.78)}
.falconode-credit--admin{margin:28px 30px 0;padding:14px 16px;background:#fff;border:1px solid #E5DFD9;border-radius:11px;color:#6F6670;box-shadow:0 5px 15px rgba(61,45,63,.04)}
.falconode-credit--login{width:min(440px,100%);margin:16px auto 0;padding:12px 14px;background:#fff;border:1px solid #E5DFD9;border-radius:11px;color:#6F6670;box-shadow:0 8px 24px rgba(61,45,63,.08)}
@media(max-width:640px){.falconode-credit{flex-wrap:wrap}.falconode-credit--admin{margin:22px 14px 0}.falconode-credit img{width:112px}}
</style>
HTML;
    }

    private function creditMarkup(string $placement): string
    {
        return <<<HTML
<div class="falconode-credit falconode-credit--{$placement}" aria-label="Design credit">
    <span>Designed by</span>
    <span class="falconode-credit__logo"><img src="/assets/images/brand/falconode-credit.png" alt="Falconode Integrated Solutions"></span>
</div>
HTML;
    }
}
