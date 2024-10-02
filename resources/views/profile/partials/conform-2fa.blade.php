<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-300">
            {{ __('Conform Authenticate') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>
    @php
        $qrCodeUrl = Google2FA::getQRCodeInline(
            config('app.name'),
            Auth::user()->email,
            Auth::user()->google2fa_secret,
        );
    @endphp
    <div>{!! $qrCodeUrl !!}</div>
</section>
