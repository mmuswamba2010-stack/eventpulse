<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_includes_seo_meta_tags(): void
    {
        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:description"', false)
            ->assertSee('rel="canonical"', false);
    }

    public function test_event_page_includes_event_specific_seo(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => 'Concert SEO Test',
            'slug' => 'concert-seo-test',
            'description' => 'Un super concert pour tester le référencement.',
            'location' => 'Kinshasa, Gombe',
            'category' => 'music',
            'event_date' => now()->addMonth(),
            'capacity' => 100,
            'price' => 5000,
            'status' => 'published',
            'is_paid' => true,
        ]);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('Concert SEO Test', false)
            ->assertSee('Kinshasa, Gombe', false)
            ->assertSee(route('events.show', $event->slug), false)
            ->assertSee('property="og:image"', false);
    }

    public function test_sitemap_lists_published_events(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => 'Sitemap Event',
            'slug' => 'sitemap-event',
            'description' => 'Test',
            'location' => 'Kinshasa',
            'category' => 'tech',
            'event_date' => now()->addWeek(),
            'capacity' => 50,
            'price' => 0,
            'status' => 'published',
            'is_paid' => true,
        ]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee(route('events.index'), false)
            ->assertSee(route('events.show', $event->slug), false);
    }

    public function test_robots_txt_references_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Sitemap: '.url('/sitemap.xml'), false)
            ->assertSee('Disallow: /admin', false);
    }
}
