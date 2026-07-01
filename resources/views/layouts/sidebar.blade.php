<nav class="sidebar">

    <!-- 上ロゴ -->
    <div class="logo"></div>

    <ul class="sidebar-menu">

      <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
         <a href="{{ route('dashboard') }}" title="打刻">
            <span class="icon">⏱</span>
            <span class="text">打刻</span>
         </a>
      </li>

      <li class="{{ request()->routeIs('shifts.shift') ? 'active' : '' }}">
         <a href="{{ route('shifts.shift') }}" title="シフト一覧">
            <span class="icon">📅</span>
            <span class="text">シフト一覧</span>
         </a>
      </li>

      <li class="disabled">
         <a href="#" title="勤務表">
            <span class="icon">📋</span>
            <span class="text">勤務表</span>
         </a>
      </li>

      <li class="{{ request()->routeIs('attendance.history') ? 'active' : '' }}">
         <a href="{{ route('attendance.history') }}" title="打刻履歴">
            <span class="icon">🕒</span>
            <span class="text">打刻履歴</span>
         </a>
      </li>

    </ul>


</nav>
