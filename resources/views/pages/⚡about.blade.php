<?php

use App\Models\Page;
use App\Services\SiteSettings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront')] #[Title('About')] class extends Component {
    public function page(): ?Page
    {
        return Page::query()->published()->where('slug', 'about')->first();
    }
}; ?>

<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
    <p class="text-xs tracking-[0.25em] text-gold uppercase">About</p>
    <h1 class="mt-3 font-display text-4xl text-ivory">{{ $this->page()?->title ?? 'Our story' }}</h1>
    <div class="mt-8 space-y-4 text-base leading-relaxed text-muted">
        @if ($this->page()?->body)
            {!! nl2br(e($this->page()->body)) !!}
        @else
            <p>{{ app(SiteSettings::class)->get('about_teaser') }}</p>
        @endif
    </div>
</div>
