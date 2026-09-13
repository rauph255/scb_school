<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Event;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('event_category_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('summary')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('body')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('featuredMedia.id')
                    ->label('Featured media')
                    ->placeholder('-'),
                TextEntry::make('starts_at')
                    ->dateTime(),
                TextEntry::make('ends_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('timezone'),
                TextEntry::make('venue_name')
                    ->placeholder('-'),
                TextEntry::make('venue_address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('map_url')
                    ->placeholder('-'),
                TextEntry::make('registration_url')
                    ->placeholder('-'),
                TextEntry::make('programmeDownload.title')
                    ->label('Programme download')
                    ->placeholder('-'),
                TextEntry::make('event_state'),
                TextEntry::make('publication_status'),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('seo_title')
                    ->placeholder('-'),
                TextEntry::make('seo_description')
                    ->placeholder('-'),
                TextEntry::make('canonical_url')
                    ->placeholder('-'),
                TextEntry::make('og_title')
                    ->placeholder('-'),
                TextEntry::make('og_description')
                    ->placeholder('-'),
                TextEntry::make('og_media_id')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('robots_index')
                    ->boolean(),
                IconEntry::make('robots_follow')
                    ->boolean(),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Event $record): bool => $record->trashed()),
            ]);
    }
}
