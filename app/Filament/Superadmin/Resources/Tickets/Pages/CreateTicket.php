<?php

namespace App\Filament\Superadmin\Resources\Tickets\Pages;

use App\Filament\Superadmin\Resources\Tickets\TicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;
}
