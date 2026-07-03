@section('css')
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
@endsection

<div class="container">

    <h2> 新規登録 </h2>

    <form action="/register" method="POST">
        @csrf

        <p>
            名前：<br>
            <input type="text" name="user_name">
        </p>

        <p>
            メール：<br>
            <input type="email" name="email">
        </p>

        <p>
            パスワード：<br>
            <input type="password" name="password">
        </p>

        <button type="submit">登録</button>
    </form>

    <p>
        <a href="/login">ログインはこちら</a>
    </p>

</div>

@include('layouts.footer')
