<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
        </x-slot>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        @if($errors->any())
            <div class="w-full mb-4">
                <div class="alert alert-danger">
                    <strong>{{$errors->first()}}</strong>
                </div>
            </div>
        @endif

        @php
            $qrCodeUrl = Google2FA::getQRCodeInline(
                config('app.name'),
                Auth::user()->email,
                Auth::user()->google2fa_secret,
            );
        @endphp
        <div>{!! $qrCodeUrl !!}</div>

        <p class="mt-8">2要素認証には、スマホのGoogle Authenticatorアプリが必要です。以下からあなたのデバイスに合わせてインストールしてください。</p>
        <div class="flex items-center gap-4 pb-4">
            <div class="w-40">
                <a
                    href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=ja&gl=US"><img
                        src="{{ asset('images/icons/google-play-badge.png') }}"
                        alt=""></a>
            </div>
            <div class="">
                <a href="https://apps.apple.com/jp/app/google-authenticator/id388497605"><img
                        src="{{ asset('images/icons/Download_on_the_App_Store_Badge_JP_RGB_blk_100317.svg') }}"
                        alt="" class="w-36"></a>
            </div>
        </div>

        <form method="POST" action="{{ route('2fa') }}">
            @csrf
            <div class="my-6">
                <p class="text-sm mb-2">アプリに表示されている文字列を入力してください。30秒ごとに変わります。</p>
                <label for="one_time_password" value="__('ワンタイムパスワード')" />
                <input type="password" id="one_time_password" name="one_time_password"
                       class="block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>

            <div class="flex items-center justify-between mt-4">
                <div class="relative my-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ml-3">ログイン</button>
                </div>
            </div>
        </form>

    </x-auth-card>
</x-guest-layout>
