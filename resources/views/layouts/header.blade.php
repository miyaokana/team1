<header class="header">

    <div class="header-left">
        <a href="{{ route('notices.index') }}" class="notice-link">
            お知らせ

        @php
        $noticeCount = \App\Models\Notice::where(function ($query) {

            $query->where('user_id', auth()->id())
                ->orWhereNull('user_id');

        })
        ->where('is_read', false)
        ->count();
        @endphp

        <span class="count">
            {{ $noticeCount }}
        </span>

        </a>
    </div>

    <div class="user-menu">

        <button class="user-btn" onclick="toggleMenu()">

            @auth
                {{ Auth::user()->user_name ?? Auth::user()->email }}
            @else
                ゲスト
            @endauth

        </button>

        @auth
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
        @endauth

    </div>

</header>

<script>
function toggleMenu() {
    const menu = document.getElementById('userDropdown');

    if (!menu) return;

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