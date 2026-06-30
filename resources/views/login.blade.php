@include('layouts.header')

<!-- フォーム専用CSS -->
<link rel="stylesheet" href="{{ asset('css/form.css') }}">

<div class="container">

    <h2>ログイン</h2>

<<<<<<< HEAD
    <button type="submit"> ログイン </button>
</form>
=======
    @if(session('error'))
        <p style="color:red;">
            {{ session('error') }}
        </p>
    @endif
>>>>>>> 3a888079d7ae08c9ed8f0a5a1b75e55425bbb7c2

    <form action="/login" method="POST">
        @csrf

        <p>
            メール：<br>
            <input type="email" name="email" placeholder="メール">
        </p>

        <p>
            パスワード：<br>
            <input type="password" name="password" placeholder="パスワード">
        </p>

        <button type="submit">ログイン</button>
    </form>


</div>

@include('layouts.footer')