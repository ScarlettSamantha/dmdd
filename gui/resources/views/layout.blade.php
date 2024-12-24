<!DOCTYPE html>
<html lang="@yield('lang', 'en')">
<head>
    @include('partials.head')
</head>
<body class="bg-gray-100">
    @if(Auth::check())
        @include('partials.navbar')
    @endif

    @yield('content')

    <!-- Footer -->
    <footer class="container mx-auto mt-8 p-4 bg-base-100 shadow-md rounded-lg">
        @include('partials.footer')
    </footer>
</body>
</html>