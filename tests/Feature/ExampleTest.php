<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_homepage_renders_school_website_with_credit(): void
    {
        $this->prepareMySqlSchema();

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('St. Charles Borromeo Pre & Primary School', false)
            ->assertSee('Designed by')
            ->assertSee('/assets/images/brand/falconode-credit.png')
            ->assertSee('/assets/prototype/css/app.css');
    }

    public function test_admin_login_renders_branded_panel_with_credit(): void
    {
        $this->prepareMySqlSchema();

        $response = $this->get('/admin');

        $response
            ->assertOk()
            ->assertSee('Administration panel')
            ->assertSee('Designed by')
            ->assertSee('/assets/images/brand/falconode-credit.png')
            ->assertSee('/admin/dashboard');
    }

    public function test_admin_dashboard_links_stay_inside_admin_area(): void
    {
        $this->prepareMySqlSchema();

        $response = $this->get('/admin/dashboard');

        $response
            ->assertOk()
            ->assertSee('href="/admin/events"', false)
            ->assertSee('href="/admin/news"', false)
            ->assertDontSee('href="/events"', false);
    }
}
