<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('post_category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(190),
                Textarea::make('excerpt')
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('body')
                    ->required()
                    ->rows(12)
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
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'In review',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->required()
                    ->default('draft'),
                Toggle::make('is_featured')
                    ->default(false)
                    ->required(),
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
