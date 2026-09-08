<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public function __construct(
        public ?string $seoTitle = null,
        public ?string $seoDescription = null,
        public ?string $seoUrl = null,
        public ?string $seoImage = null,
        public ?string $seoType = null,
    ) {}

    public function render(): View
    {
        return view('layouts.app');
    }
}
