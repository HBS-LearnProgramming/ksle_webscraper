<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>KLSE Web Scraper</title>

        <!-- Fonts -->
        @vite(['resources/js/app.js', 'resources/css/app.css'])
     
       @inertiaHead
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">

        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
           @inertia
        </div>
    </body>
</html>
