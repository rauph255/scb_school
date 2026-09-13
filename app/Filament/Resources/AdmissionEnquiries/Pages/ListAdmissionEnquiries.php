<?php

namespace App\Filament\Resources\AdmissionEnquiries\Pages;

use App\Filament\Resources\AdmissionEnquiries\AdmissionEnquiryResource;
use Filament\Resources\Pages\ListRecords;

class ListAdmissionEnquiries extends ListRecords
{
    protected static string $resource = AdmissionEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
