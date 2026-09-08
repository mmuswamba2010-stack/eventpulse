<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventImage
{
    public static function storeFromUpload(UploadedFile $file): string
    {
        if (! extension_loaded('gd')) {
            return $file->store('events', 'public');
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory('events');

        $path = 'events/'.Str::uuid()->toString().'.jpg';
        $source = self::loadImage($file);

        if ($source === null) {
            return $file->store('events', 'public');
        }

        $maxWidth = (int) config('eventpulse.event_image_max_width', 1200);
        $thumbWidth = (int) config('eventpulse.event_image_thumb_width', 640);
        $quality = (int) config('eventpulse.event_image_quality', 82);
        $thumbQuality = (int) config('eventpulse.event_image_thumb_quality', 78);

        $full = self::resize($source, $maxWidth);
        self::saveJpeg($full, $disk->path($path), $quality);

        $thumbPath = self::thumbPath($path);
        $thumb = self::resize($full, $thumbWidth);
        self::saveJpeg($thumb, $disk->path($thumbPath), $thumbQuality);

        imagedestroy($source);
        if ($full !== $source) {
            imagedestroy($full);
        }
        imagedestroy($thumb);

        return $path;
    }

    public static function thumbPath(string $path): string
    {
        $info = pathinfo($path);

        return ($info['dirname'] ?? 'events').'/'.($info['filename'] ?? 'image').'_thumb.'.($info['extension'] ?? 'jpg');
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('public');
        $disk->delete($path);

        $thumb = self::thumbPath($path);
        if ($disk->exists($thumb)) {
            $disk->delete($thumb);
        }
    }

    public static function url(?string $path): ?string
    {
        return $path ? asset('storage/'.$path) : null;
    }

    public static function thumbUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $thumb = self::thumbPath($path);

        if (Storage::disk('public')->exists($thumb)) {
            return asset('storage/'.$thumb);
        }

        return self::url($path);
    }

    /**
     * @return \GdImage|resource|null
     */
    private static function loadImage(UploadedFile $file): mixed
    {
        $path = $file->getRealPath();

        if (! $path) {
            return null;
        }

        $mime = $file->getMimeType() ?: mime_content_type($path);

        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => @imagecreatefrompng($path) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            'image/gif' => @imagecreatefromgif($path) ?: null,
            default => null,
        };
    }

    /**
     * @param  \GdImage|resource  $image
     * @return \GdImage|resource
     */
    private static function resize(mixed $image, int $maxWidth): mixed
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth) {
            return $image;
        }

        $newHeight = (int) round($height * ($maxWidth / $width));
        $resized = imagecreatetruecolor($maxWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);

        return $resized;
    }

    /**
     * @param  \GdImage|resource  $image
     */
    private static function saveJpeg(mixed $image, string $absolutePath, int $quality): void
    {
        imageinterlace($image, true);
        imagejpeg($image, $absolutePath, max(1, min(100, $quality)));
    }
}
