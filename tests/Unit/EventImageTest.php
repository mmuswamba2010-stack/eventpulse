<?php

namespace Tests\Unit;

use App\Support\EventImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required.');
        }

        Storage::fake('public');
    }

    public function test_store_creates_optimized_image_and_thumbnail(): void
    {
        $file = UploadedFile::fake()->image('event.jpg', 2000, 1200);

        $path = EventImage::storeFromUpload($file);

        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists(EventImage::thumbPath($path));

        [$width] = getimagesize(Storage::disk('public')->path($path));
        $this->assertLessThanOrEqual(1200, $width);

        [$thumbWidth] = getimagesize(Storage::disk('public')->path(EventImage::thumbPath($path)));
        $this->assertLessThanOrEqual(640, $thumbWidth);
    }

    public function test_delete_removes_image_and_thumbnail(): void
    {
        $file = UploadedFile::fake()->image('event.jpg', 800, 600);
        $path = EventImage::storeFromUpload($file);
        $thumb = EventImage::thumbPath($path);

        EventImage::delete($path);

        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($thumb);
    }

    public function test_thumb_url_falls_back_to_full_image(): void
    {
        Storage::disk('public')->put('events/legacy.jpg', 'binary');

        $this->assertSame(
            asset('storage/events/legacy.jpg'),
            EventImage::thumbUrl('events/legacy.jpg')
        );
    }
}
