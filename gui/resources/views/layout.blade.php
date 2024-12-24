<!DOCTYPE html>
<html lang="@yield('lang', 'en')">
<head>
    @include('partials.head')
</head>
<body class="content-background min-h-screen flex flex-col">
    @if(Auth::check())
        @include('partials.navbar')
    @endif

    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')
</body>
</html>