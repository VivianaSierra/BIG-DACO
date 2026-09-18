<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$user = new App\Models\User();
$user->email = 'sergioalarcon310@gmail.com';
$user->password = Hash::make('123456');
$user->name = 'Sergio';
$user->save();

echo "Usuario creado exitosamente:\n";
$createdUser = App\Models\User::where('email', 'sergioalarcon310@gmail.com')->first();
echo json_encode($createdUser, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
?>
