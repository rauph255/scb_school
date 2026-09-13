<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference_code')
                    ->disabled(),
                TextInput::make('full_name')
                    ->disabled(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->disabled(),
                TextInput::make('telephone')
                    ->tel()
                    ->disabled(),
                TextInput::make('subject')
                    ->disabled(),
                Textarea::make('message')
                    ->rows(5)
                    ->disabled()
                    ->columnSpanFull(),
                Toggle::make('consent_confirmed')
                    ->disabled(),
                Select::make('status')
                    ->options([
                        'new' => 'New',
                        'in_progress' => 'In progress',
                        'responded' => 'Responded',
                        'closed' => 'Closed',
                    ])
                    ->required()
                    ->default('new'),
                Select::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('responded_at'),
                DateTimePicker::make('closed_at'),
                TextInput::make('source_ip_hash')
                    ->disabled(),
                TextInput::make('user_agent_hash')
                    ->disabled(),
            ]);
    }
}
