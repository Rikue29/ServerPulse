<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Alerts</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @livewireStyles
</head>
<body>
    <h1>Test Alerts Page</h1>
    @livewire('alerts-table')

    @livewireScripts
</body>
</html>
