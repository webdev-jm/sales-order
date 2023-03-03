<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')

        {{-- <li class="nav-item">
            <a href="" class="nav-link" id="btn-changelog">
                <i class="fas fa-clipboard-list text-warning"></i>
                <span class="navbar-badge animation__shake"><i class="fa fa-asterisk text-danger"></i></span>
            </a>
        </li> --}}

        @if(!empty(auth()->user()) && auth()->user()->hasRole('superadmin'))
        <li class="nav-item">
            <a href="#" class="nav-link" id="btn-online-users">
                <i class="far fa-user"></i>
                <span class="navbar-badge"><i class="fa fa-circle text-success"></i></span>
            </a>
        </li>
        @endif
        
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
