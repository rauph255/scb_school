<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(190),
                Select::make('page_type')
                    ->options([
                        'standard' => 'Standard',
                        'homepage' => 'Homepage',
                        'policy' => 'Policy',
                        'landing' => 'Landing',
                    ])
                    ->required()
                    ->default('standard'),
                Select::make('template_key')
                    ->options([
                        'standard' => 'Standard',
                        'home' => 'Home',
                        'about' => 'About',
                        'academics' => 'Academics',
                        'admissions' => 'Admissions',
                        'contact' => 'Contact',
                    ])
                    ->required()
                    ->default('standard'),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'In review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->required()
                    ->default('draft'),
                Textarea::make('excerpt')
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('featured_media_id')
                    ->relationship('featuredMedia', 'original_name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('published_at'),
                DateTimePicker::make('scheduled_at'),
                TextInput::make('seo_title')
                    ->maxLength(255),
                TextInput::make('seo_description')
                    ->maxLength(320),
                TextInput::make('canonical_url')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('og_title')
                    ->maxLength(255),
                TextInput::make('og_description')
                    ->maxLength(320),
                Toggle::make('robots_index')
                    ->default(true)
                    ->required(),
                Toggle::make('robots_follow')
                    ->default(true)
                    ->required(),
            ]);
    }
}
