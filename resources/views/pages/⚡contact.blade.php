<?php

use App\Models\Page;
use App\Services\SiteSettings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront')] #[Title('Contact')] class extends Component {
    public function page(): ?Page
    {
        return Page::query()->published()->where('slug', 'contact')->first();
    }

    public function settings(): SiteSettings
    {
        return app(SiteSettings::class);
    }

    public function whatsappUrl(): string
    {
        $number = preg_replace('/\D+/', '', (string) $this->settings()->get(
            'whatsapp_number',
            config('shop.whatsapp_number'),
        ));

        return 'https://wa.me/'.$number;
    }
}; ?>

<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
    <p class="text-xs tracking-[0.25em] text-gold uppercase">Contact</p>
    <h1 class="mt-3 font-display text-4xl text-ivory">{{ $this->page()?->title ?? 'Get in touch' }}</h1>

    <div class="mt-8 space-y-4 text-base leading-relaxed text-muted">
        @if ($this->page()?->body)
            {!! nl2br(e($this->page()->body)) !!}
        @endif
    </div>

    <div class="mt-10 space-y-3 border border-line bg-noir-surface p-6 text-sm">
        <p class="text-ivory">{{ $this->settings()->get('address', 'By appointment') }}</p>
        <a href="{{ $this->whatsappUrl() }}" target="_blank" rel="noopener" class="inline-flex border border-gold px-6 py-3 tracking-widest text-gold uppercase transition hover:bg-gold hover:text-noir">
            Message on WhatsApp
        </a>
    </div>
</div>
