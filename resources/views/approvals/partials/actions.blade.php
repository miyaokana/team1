<!-- 承認・差し戻しの操作。 $typeと$reqを受け取る -->
 @if ($req->status === 'pending')
    <form action="{{ route('approvals.update', ['type' => $type, 'id' => $req->id]) }}" method="POST"
        class="approval-form">
        @csrf
        <input type="hidden" name="status" class="status-input">
        <input type="hidden" name="admin_comment" class="comment-input">
        <button type="button" onclick="submitApproval(this, 'approved')">承認</button>
        <button type="button" onclick="submitApproval(this, 'rejected')">差し戻し</button>
    </form>

@else
    {{ $req->admin_comment ?: '-' }}
@endif