<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠修正</title>
</head>
<body>

<h2>勤怠修正画面</h2>

@if(session('success'))
    <p style="color:blue">{{ session('success') }}</p>
@endif

@if ($errors->any())
    <div style="color:red">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<h3>現在の勤怠</h3>

<table border="1">
<tr>
    <td>出勤</td>
    <td>{{ $attendance->check_in }}</td>
</tr>
<tr>
    <td>退勤</td>
    <td>{{ $attendance->check_out }}</td>
</tr>
</table>

<hr>

<form method="POST" action="/correction">
    @csrf

    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

    <p>
        修正後 出勤<br>
        <input type="datetime-local" name="after_check_in" value="{{ old('after_check_in') }}" required>
    </p>

    <p>
        修正後 退勤<br>
        <input type="datetime-local" name="after_check_out" value="{{ old('after_check_out') }}" required>
    </p>

    <p>
        修正理由<br>
        <textarea name="reason" required>{{ old('reason') }}</textarea>
    </p>

    <button type="submit">申請</button>
</form>

</body>
</html>