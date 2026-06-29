<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function index(Request $request){
        //
        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthInput);

        //
        $startOfMonth = $currentMonth->copy->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        //
        $dates = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()){
            $dates[] = $date->copy();
        }

        //
        $users = User::with(['shifts' => function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
        }])->get();

        return view('shifts.index', compact('users', 'dates', 'currentMonth'));
    }
}
