<?php

$storagePath = sys_get_temp_dir().'/big-daco';
$storageDirectories = [
    'app/private',
    'app/public',
    'framework/cache/data',
    'framework/sessions',
    'framework/testing',
    'framework/views',
    'logs',
];

foreach ($storageDirectories as $directory) {
    $path = $storagePath.'/'.$directory;

    if (! is_dir($path) && ! mkdir($path, 0775, true) && ! is_dir($path)) {
        throw new RuntimeException("Unable to create Laravel storage directory: {$path}");
    }
}

$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

// bootstrap/cache es de solo lectura en Vercel; los manifiestos se generan en /tmp.
foreach (['APP_PACKAGES_CACHE' => 'packages.php', 'APP_SERVICES_CACHE' => 'services.php'] as $name => $file) {
    $_ENV[$name] = $_SERVER[$name] = $storagePath.'/'.$file;
    putenv($name.'='.$storagePath.'/'.$file);
}

$report = static function (string $message): void {
    error_log($message);

    if (isset($_GET['diag'])) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }
};

register_shutdown_function(static function () use ($report): void {
    $error = error_get_last();

    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        $report('PHP FATAL: '.$error['message'].' in '.$error['file'].':'.$error['line']);
    }
});

if (isset($_GET['diag'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    foreach (['APP_KEY', 'APP_ENV', 'DB_CONNECTION', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'SESSION_DRIVER'] as $name) {
        echo $name.': '.(getenv($name) === false || getenv($name) === '' ? 'MISSING' : 'set')."\n";
    }
    echo 'APP_KEY length: '.strlen((string) getenv('APP_KEY'))."\n";
    echo 'PHP: '.PHP_VERSION."\n";
    require __DIR__.'/../vendor/autoload.php';
    $app = require __DIR__.'/../bootstrap/app.php';
    try {
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        echo "BOOTSTRAP OK\n";
        try {
            $app->make('db')->connection()->getPdo();
            echo "DB OK\n";
        } catch (Throwable $e) {
            echo 'DB: '.get_class($e).': '.substr($e->getMessage(), 0, 300)."\n";
        }
    } catch (Throwable $e) {
        echo 'ROOT: '.get_class($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine()."\n";
    }
    exit;
}

try {
    require __DIR__.'/../public/index.php';
} catch (Throwable $e) {
    http_response_code(500);
    $report('BOOT ERROR: '.get_class($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
}
