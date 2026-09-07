<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title ?? null])
        @fluxAppearance
    </head>
    <body class="storefront min-h-screen antialiased" data-theme="noir-gold">
        <x-storefront.header />

        <main>
            {{ $slot }}
        </main>

        <x-storefront.footer />
        <x-storefront.whatsapp-button />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
