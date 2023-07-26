<footer>
    @if(env('DISP_APP_VER'))
        <div class="pb-1 text-xs text-gray-800 dark:text-gray-400 flex justify-center items-center">
            <p>Ver.{{ env('APP_VER') }}</p>
        </div>
    @endif
    <div class="pb-3 text-xs text-gray-800 dark:text-gray-400 flex justify-center items-center">
        <p>&copy;&nbsp;{{ config('euros.CopyRightYear') }}&nbsp;{{ config('euros.CopyRightName') }}</p>
    </div>
</footer>
