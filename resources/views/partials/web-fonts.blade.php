@php
    $fontUrl = 'https://fonts.bunny.net/css?family=space-grotesk:500,600,700|inter:400,500,600,700&display=swap';
@endphp
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="preload" as="style" href="{{ $fontUrl }}">
<link href="{{ $fontUrl }}" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link href="{{ $fontUrl }}" rel="stylesheet"></noscript>
