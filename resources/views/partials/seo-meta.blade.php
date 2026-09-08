@php
    use App\Support\Seo;

    $meta = array_merge(Seo::defaults(), array_filter([
        'title' => $seoTitle ?? null,
        'description' => $seoDescription ?? null,
        'url' => isset($seoUrl) ? Seo::absoluteUrl($seoUrl) : null,
        'image' => isset($seoImage) ? Seo::absoluteUrl($seoImage) : null,
        'type' => $seoType ?? null,
    ], fn ($value) => filled($value)));

    $meta['url'] = Seo::absoluteUrl($meta['url']);
    $meta['image'] = Seo::absoluteUrl($meta['image']);
@endphp

<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ $meta['description'] }}">
@if ($googleVerification = config('eventpulse.google_site_verification'))
<meta name="google-site-verification" content="{{ $googleVerification }}">
@endif
<link rel="canonical" href="{{ $meta['url'] }}">

<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta property="og:type" content="{{ $meta['type'] }}">
<meta property="og:site_name" content="{{ Seo::siteName() }}">
<meta property="og:title" content="{{ $meta['title'] }}">
<meta property="og:description" content="{{ $meta['description'] }}">
<meta property="og:url" content="{{ $meta['url'] }}">
<meta property="og:image" content="{{ $meta['image'] }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $meta['title'] }}">
<meta name="twitter:description" content="{{ $meta['description'] }}">
<meta name="twitter:image" content="{{ $meta['image'] }}">
