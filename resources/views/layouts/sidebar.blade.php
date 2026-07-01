<!-- resources/views/layouts/sidebar.blade.php -->
 <nav class="sidebar">
    <div class="sidebar-title">勤怠メニュー</div>
    <ul class="sidebar-menu">
        <!-- 実装済み -->
         <li class="{{ request() -> routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">打刻</a>
         </li>

         <!-- 未実装(リンク先がまだない。あとから追加予定) -->
          <li class="{{ request()->routeIs('shifts.shift') ? 'active' : '' }}">
            <a href="{{ route('shifts.shift') }}">シフト一覧</a>
         </li>
         
          <li class="disabled">
            <a href="#">勤務表</a>
         </li>

         <li class="{{ request() -> routeIs('attendance.history') ? 'active' : '' }}">
            <a href="{{ route('attendance.history') }}">打刻履歴</a>
         </li>
    </ul>
 </nav>