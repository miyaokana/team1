<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>【管理画面】打刻修正申請一覧</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f3f4f6; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; }
        .btn-approve { background: #28a745; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-reject { background: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .alert { padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>打刻修正 承認待ち一覧</h2>

    @if (session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    @if($requests->isEmpty())
        <p>現在、未処理の修正申請はありません。</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>申請者</th>
                    <th>対象日</th>
                    <th>修正案 (出勤 / 退勤)</th>
                    <th>申請理由</th>
                    <th>対応</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->user->name }}</td>
                        <td>{{ $req->date }}</td>
                        <td>
                            <strong style="color: blue;">{{ $req->requested_punch_in ?? '--:--' }}</strong> 
                            ～ 
                            <strong style="color: blue;">{{ $req->requested_punch_out ?? '--:--' }}</strong>
                        </td>
                        <td>{{ $req->reason }}</td>
                        <td>
                            <form action="{{ route('admin.dakoku.requests.approve', $req->id) }}" method="POST">
                                @csrf
                                <input type="text" name="admin_comment" placeholder="コメント（任意）" style="padding: 5px; margin-bottom: 5px; width: 150px;"><br>
                                
                                <button type="submit" name="action" value="approve" class="btn-approve">承認</button>
                                <button type="submit" name="action" value="reject" class="btn-reject">却下</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

</body>
</html>