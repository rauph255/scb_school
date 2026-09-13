<?php

namespace App\Filament\Resources\AdmissionEnquiries\Pages;

use App\Filament\Resources\AdmissionEnquiries\AdmissionEnquiryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmissionEnquiry extends CreateRecord
{
    protected static string $resource = AdmissionEnquiryResource::class;
}
