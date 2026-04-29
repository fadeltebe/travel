<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;

Illuminate\Support\Facades\DB::table('domains')->where('tenant_id', 'travela')->delete();
Illuminate\Support\Facades\DB::table('tenants')->where('id', 'travela')->delete();

$tenant = \App\Models\Tenant::create(['id' => 'travela']);
$tenant->domains()->create(['domain' => 'travela.travel.test']);

echo "Tenant travela created!\n";
