<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemSetting;

echo "Cleaning up invalid settings...\n";
$deleted = SystemSetting::where('key', 'like', '%{$s}%')->delete();
echo "Deleted $deleted records.\n";
