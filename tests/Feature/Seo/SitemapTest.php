<?php

namespace Tests\Feature\Seo;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    
    use RefreshDatabase;
public function test_sitemap_is_public_xml(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8')->assertSee('<urlset', false);
    }

    public function test_robots_points_to_sitemap(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:')->assertSee('/sitemap.xml');
    }
}
