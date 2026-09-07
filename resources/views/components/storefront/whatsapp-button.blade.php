@php
    use App\Services\SiteSettings;

    $number = preg_replace('/\D+/', '', (string) app(SiteSettings::class)->get(
        'whatsapp_number',
        config('shop.whatsapp_number'),
    ));
@endphp

<a
    href="https://wa.me/{{ $number }}"
    target="_blank"
    rel="noopener"
    class="fixed right-5 bottom-5 z-50 inline-flex h-12 w-12 items-center justify-center rounded-full bg-gold text-noir shadow-lg transition hover:bg-gold-soft"
    aria-label="Chat on WhatsApp"
>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
        <path d="M12.04 2C6.58 2 2.15 6.43 2.15 11.89c0 1.96.51 3.79 1.48 5.42L2 22l4.86-1.58a9.84 9.84 0 0 0 5.18 1.47h.01c5.46 0 9.89-4.43 9.89-9.89C21.94 6.43 17.5 2 12.04 2zm5.76 14.07c-.24.68-1.4 1.25-1.93 1.33-.5.07-1.13.1-1.82-.11-.42-.13-.96-.31-1.66-.61-2.92-1.26-4.82-4.21-4.97-4.4-.14-.19-1.2-1.6-1.2-3.05 0-1.45.76-2.16 1.03-2.45.27-.29.59-.36.79-.36h.57c.18 0 .42-.07.66.5.24.58.82 2 .89 2.15.07.15.12.32.02.52-.1.19-.14.32-.28.49-.14.17-.3.38-.43.51-.14.14-.29.29-.12.56.16.27.73 1.2 1.56 1.94 1.07.96 1.97 1.26 2.25 1.4.28.14.44.12.6-.07.17-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.27.1 1.7.8 1.99.95.29.14.48.22.55.34.07.12.07.7-.17 1.38z"/>
    </svg>
</a>
