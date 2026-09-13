<?php

namespace App\Filament\Resources\AdmissionEnquiries\Schemas;

use App\Models\AdmissionEnquiry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdmissionEnquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reference_code'),
                TextEntry::make('guardian_name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('telephone'),
                TextEntry::make('intended_level')
                    ->placeholder('-'),
                TextEntry::make('intended_term')
                    ->placeholder('-'),
                TextEntry::make('intended_year')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('preferred_contact_method')
                    ->placeholder('-'),
                TextEntry::make('message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('consent_confirmed')
                    ->boolean(),
                TextEntry::make('status'),
                TextEntry::make('assigned_to')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('source_ip_hash')
                    ->placeholder('-'),
                TextEntry::make('user_agent_hash')
                    ->placeholder('-'),
                TextEntry::make('responded_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('closed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (AdmissionEnquiry $record): bool => $record->trashed()),
            ]);
    }
}
