<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function index()
    {
        Notice::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        $notices = Notice::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('notices.index', [
            'notices' => $notices,
        ]);
    }
}