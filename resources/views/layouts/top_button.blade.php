<script type="module">
    //参考：https://qiita.com/yuzuyuzu0830/items/1e0ff0bbad7500d8fdd6
    $(function(){
        var pagescroll = $('#top_scroll');

        // 50px スクロールをすると、ボタンが表示される
        $(window).scroll(function () {
            if ($(this).scrollTop() > 150) {
                pagescroll.fadeIn();
            } else {
                // 50px以上スクロールされていない時は、ボタンを表示しない
                pagescroll.fadeOut();
            }
        });
        // ボタンを押すと、0.8秒かけてトップに戻る
        pagescroll.click(function () {
            $('body, html').animate({ scrollTop: 0 }, 500);
            return false;
        });
    });
</script>
<div>
    <button id="top_scroll" type="button" class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-3 focus:outline-none focus:ring-blue-300 rounded-lg text-xl p-2.5 text-center fixed items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 bottom-7 right-7 w-12"><i class="fas fa-arrow-up"></i></button>
</div>
