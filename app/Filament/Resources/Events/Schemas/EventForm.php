<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(190),
                Textarea::make('summary')
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('body')
                    ->rows(10)
                    ->columnSpanFull(),
                Select::make('featured_media_id')
                    ->relationship(
                        name: 'featuredMedia',
                        titleAttribute: 'original_name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->where('mime_type', 'like', 'image/%')
                            ->publiclyVisible(),
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('Approved public images only. Upload and approve new files in the media library first.'),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at'),
                TextInput::make('timezone')
                    ->required()
                    ->default(config('app.timezone')),
                TextInput::make('venue_name')
                    ->maxLength(255),
                Textarea::make('venue_address')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('map_url')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('registration_url')
                    ->url()
                    ->maxLength(2048),
                Select::make('programme_download_id')
                    ->relationship('programmeDownload', 'title')
                    ->searchable()
                    ->preload(),
                Select::make('event_state')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'postponed' => 'Postponed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ])
                    ->required()
                    ->default('scheduled'),
                Select::make('publication_status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'In review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('published_at'),
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
