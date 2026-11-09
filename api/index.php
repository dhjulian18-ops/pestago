<?php
// Ensure /tmp directories exist for Laravel storage on Vercel
$dirs = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions'
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Secret route to run migrations and seeders on Aiven DB from browser
if (isset($_GET['migrate']) && $_GET['migrate'] === 'pestago2026') {
    require __DIR__ . '/backend/vendor/autoload.php';
    $app = require_once __DIR__ . '/backend/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "<h2>Running Migrations...</h2><pre>";
    \Illuminate\Support\Facades\Artisan::call('migrate --force');
    echo \Illuminate\Support\Facades\Artisan::output();
    
    echo "</pre><h2>Running Database Seeders...</h2><pre>";
    \Illuminate\Support\Facades\Artisan::call('db:seed --force');
    echo \Illuminate\Support\Facades\Artisan::output();
    echo "</pre><h3>Database setup completed! <a href='/'>Go to PestaGo</a></h3>";
    exit;
}

// Forward to backend entry point
require __DIR__ . '/backend/public/index.php';
