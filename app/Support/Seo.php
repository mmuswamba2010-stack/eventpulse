<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Str;

class Seo
{
    public static function siteName(): string
    {
        return (string) config('app.name', 'Event Pulse');
    }

    public static function formatTitle(?string $pageTitle = null): string
    {
        $site = self::siteName();

        return filled($pageTitle) ? "{$pageTitle} | {$site}" : $site;
    }

    /**
     * @return array{title: string, description: string, url: string, image: string, type: string}
     */
    public static function defaults(): array
    {
        return [
            'title' => self::formatTitle(),
            'description' => __('SEO default description'),
            'url' => url('/'),
            'image' => self::defaultImage(),
            'type' => 'website',
        ];
    }

    /**
     * @return array{title: string, description: string, url: string, image: string, type: string}
     */
    public static function forHome(): array
    {
        return [
            'title' => self::formatTitle(__('SEO home title')),
            'description' => __('SEO home description'),
            'url' => route('events.index'),
            'image' => self::defaultImage(),
            'type' => 'website',
        ];
    }

    /**
     * @return array{title: string, description: string, url: string, image: string, type: string}
     */
    public static function forEvent(Event $event): array
    {
        $pageTitle = __('SEO event title', [
            'title' => $event->title,
            'date' => $event->event_date->translatedFormat('d/m/Y'),
            'location' => $event->location,
        ]);

        $description = Str::squish(strip_tags((string) $event->description));

        if ($description === '') {
            $description = __('SEO event description fallback', [
                'title' => $event->title,
                'date' => $event->event_date->translatedFormat('d F Y'),
                'location' => $event->location,
            ]);
        }

        return [
            'title' => self::formatTitle($pageTitle),
            'description' => Str::limit($description, 160),
            'url' => route('events.show', $event->slug),
            'image' => self::eventImage($event),
            'type' => 'website',
        ];
    }

    public static function defaultImage(): string
    {
        return asset('images/brand/mark.svg');
    }

    public static function eventImage(Event $event): string
    {
        if ($event->image_path) {
            return EventImage::url($event->image_path) ?? asset('storage/'.$event->image_path);
        }

        return self::defaultImage();
    }

    public static function absoluteUrl(string $url): string
    {
        return Str::startsWith($url, ['http://', 'https://'])
            ? $url
            : url($url);
    }
}
