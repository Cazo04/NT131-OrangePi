<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="lib/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="lib/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="lib/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/jquery/jquery-3.7.1.min.js"></script>
    <title>@yield('title', 'Default Title')</title>
</head>
<body class="d-flex flex-column" style="min-height: 100vh">
    @include('layouts.header')

    <main class="bg-secondary flex-grow-1 d-flex flex-column">
        @yield('main')
    </main>

    <script src="/js/app.js"></script>
</body>
</html>
