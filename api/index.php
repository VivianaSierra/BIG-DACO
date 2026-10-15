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
};

register_shutdown_function(static function () use ($report): void {
    $error = error_get_last();

    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        $report('PHP FATAL: '.$error['message'].' in '.$error['file'].':'.$error['line']);
    }
});

try {
    require __DIR__.'/../public/index.php';
} catch (Throwable $e) {
    http_response_code(500);
    $report('BOOT ERROR: '.get_class($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
}
