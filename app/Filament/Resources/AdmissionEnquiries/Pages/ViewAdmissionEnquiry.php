<?php

namespace App\Filament\Resources\AdmissionEnquiries\Pages;

use App\Filament\Resources\AdmissionEnquiries\AdmissionEnquiryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdmissionEnquiry extends ViewRecord
{
    protected static string $resource = AdmissionEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
