<?php

use App\Services\SiteSettings;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Settings')] class extends Component {
    public string $shop_name = '';

    public string $whatsapp_number = '';

    public string $currency = 'PKR';

    public string $address = '';

    public string $instagram = '';

    public string $hero_headline = '';

    public string $hero_subline = '';

    public string $hero_cta_label = '';

    public string $about_teaser = '';

    public function mount(SiteSettings $settings): void
    {
        $this->shop_name = (string) $settings->get('shop_name', config('shop.name'));
        $this->whatsapp_number = (string) $settings->get('whatsapp_number', config('shop.whatsapp_number'));
        $this->currency = (string) $settings->get('currency', config('shop.currency'));
        $this->address = (string) $settings->get('address', '');
        $this->instagram = (string) $settings->get('instagram', '');
        $this->hero_headline = (string) $settings->get('hero_headline', '');
        $this->hero_subline = (string) $settings->get('hero_subline', '');
        $this->hero_cta_label = (string) $settings->get('hero_cta_label', '');
        $this->about_teaser = (string) $settings->get('about_teaser', '');
    }

    public function save(SiteSettings $settings): void
    {
        $validated = $this->validate([
            'shop_name' => ['required', 'string', 'max:120'],
            'whatsapp_number' => ['required', 'string', 'max:30'],
            'currency' => ['required', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'hero_headline' => ['nullable', 'string', 'max:255'],
            'hero_subline' => ['nullable', 'string', 'max:500'],
            'hero_cta_label' => ['nullable', 'string', 'max:120'],
            'about_teaser' => ['nullable', 'string', 'max:1000'],
        ]);

        $settings->setMany($validated);

        Flux::toast(variant: 'success', text: 'Settings saved.');
    }
}; ?>

<div class="mx-auto max-w-2xl space-y-6">
    <flux:heading size="xl">Store settings</flux:heading>

    <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:field>
            <flux:label>Shop name</flux:label>
            <flux:input wire:model="shop_name" />
            <flux:error name="shop_name" />
        </flux:field>
        <flux:field>
            <flux:label>WhatsApp number</flux:label>
            <flux:input wire:model="whatsapp_number" placeholder="923001234567" />
            <flux:description>Digits only with country code, no +</flux:description>
            <flux:error name="whatsapp_number" />
        </flux:field>
        <flux:field>
            <flux:label>Currency</flux:label>
            <flux:input wire:model="currency" />
        </flux:field>
        <flux:field>
            <flux:label>Address</flux:label>
            <flux:input wire:model="address" />
        </flux:field>
        <flux:field>
            <flux:label>Instagram URL</flux:label>
            <flux:input wire:model="instagram" />
            <flux:error name="instagram" />
        </flux:field>
        <flux:field>
            <flux:label>Hero headline</flux:label>
            <flux:input wire:model="hero_headline" />
        </flux:field>
        <flux:field>
            <flux:label>Hero subline</flux:label>
            <flux:textarea wire:model="hero_subline" rows="2" />
        </flux:field>
        <flux:field>
            <flux:label>Hero CTA label</flux:label>
            <flux:input wire:model="hero_cta_label" />
        </flux:field>
        <flux:field>
            <flux:label>About teaser</flux:label>
            <flux:textarea wire:model="about_teaser" rows="3" />
        </flux:field>
        <flux:button type="submit" variant="primary">Save settings</flux:button>
    </form>
</div>
