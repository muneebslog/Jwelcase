<?php

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component {
    public Page $page;

    public function mount(Page $page): void
    {
        abort_unless($page->is_published, 404);
        $this->page = $page;
    }

    public function title(): string
    {
        return $this->page->seo_title ?: $this->page->title;
    }
}; ?>

<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
    <h1 class="font-display text-4xl text-ivory">{{ $page->title }}</h1>
    <div class="mt-8 space-y-4 text-base leading-relaxed text-muted">
        {!! nl2br(e($page->body)) !!}
    </div>
</div>
