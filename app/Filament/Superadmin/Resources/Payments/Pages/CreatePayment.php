<?php

namespace App\Filament\Superadmin\Resources\Payments\Pages;

use App\Filament\Superadmin\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
