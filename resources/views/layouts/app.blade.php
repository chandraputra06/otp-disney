<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disney OTP Orinimo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
     <link rel="shortcut icon" href="{{ asset('asset/logo-orinimo.png') }}">
</head>
<body class="min-h-screen bg-gray-100">
    @yield('content')
</body>
</html>