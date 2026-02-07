<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemSetting;

echo "--- SYSTEM SETTINGS ---\n";
foreach (SystemSetting::where('key', 'like', 'shift%')->get() as $setting) {
    echo "{$setting->key}: {$setting->value}\n";
}
echo "--- END ---\n";
