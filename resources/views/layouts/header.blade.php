<header class="header">

    <div class="header-left">
        <a href="{{ route('notices.index') }}" class="notice-link">
            お知らせ

            <span class="count">
                {{ isset($notices) ? $notices->count() : 0 }}
            </span>
        </a>
    </div>

    <div class="user-menu">

        <button class="user-btn" onclick="toggleMenu()">
            {{ Auth::user()->user_name ?? Auth::user()->email }}
        </button>

        <div id="userDropdown" class="dropdown-menu">

            <a href="#">
                メール通知設定
            </a>

            <a href="#">
                パスワード変更
            </a>

            <a href="{{ route('logout') }}">
                ログアウト
            </a>

        </div>

    </div>

</header>

<script>
function toggleMenu() {
    const menu = document.getElementById('userDropdown');

    if (menu.style.display === 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-menu')) {
        const menu = document.getElementById('userDropdown');
        if(menu){
            menu.style.display = 'none';
        }
    }
});
</script>