<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = $app->make('db');
$db->table('users')->where('username', 'admin1')->update(['email' => 'admin1@ukkkp.test']);
$db->table('users')->whereIn('username', ['superadmin_test', 'admin1'])->update([
    'password' => $app->make('hash')->make('123'),
    'isDeleted' => 0,
    'isRequestingPassword' => 0,
]);
foreach ($db->table('users')->get(['id', 'username', 'email', 'privLevel', 'isDeleted', 'isRequestingPassword']) as $u) {
    echo json_encode($u), PHP_EOL;
}