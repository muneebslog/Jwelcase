<?php

namespace App\Providers;

use App\Services\Cart\Cart;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureWritableTempDirectory();
        $this->app->singleton(Cart::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Point PHP temporary files at a project-owned directory.
     *
     * Livewire TemporaryUploadedFile calls tmpfile(); on some Windows setups
     * the system temp directory is not writable by the PHP process, which
     * makes uploads fail with stream_get_meta_data(false).
     */
    protected function configureWritableTempDirectory(): void
    {
        $directory = storage_path('framework/tmp');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        putenv('TMP='.$directory);
        putenv('TEMP='.$directory);
        putenv('TMPDIR='.$directory);
        $_ENV['TMP'] = $directory;
        $_ENV['TEMP'] = $directory;
        $_ENV['TMPDIR'] = $directory;
        $_SERVER['TMP'] = $directory;
        $_SERVER['TEMP'] = $directory;
        $_SERVER['TMPDIR'] = $directory;
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
