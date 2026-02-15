<?php

namespace App\Filament\Superadmin\Resources\Customers\Pages;

use App\Filament\Superadmin\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
