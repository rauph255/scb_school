<?php

namespace App\Filament\Widgets;

use App\Models\AdmissionEnquiry;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SchoolOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Published pages', Page::query()->published()->count())
                ->description('Public Blade content')
                ->icon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Published news', Post::query()->published()->count())
                ->description('Visible school stories')
                ->icon('heroicon-m-newspaper')
                ->color('success'),
            Stat::make('Upcoming events', Event::query()->published()->where('starts_at', '>=', now())->count())
                ->description('Calendar items')
                ->icon('heroicon-m-calendar-days')
                ->color('info'),
            Stat::make('Open enquiries', AdmissionEnquiry::query()->where('status', 'new')->count())
                ->description('Admissions inbox')
                ->icon('heroicon-m-inbox')
                ->color('danger'),
            Stat::make('Contact messages', ContactMessage::query()->where('status', 'new')->count())
                ->description('Office inbox')
                ->icon('heroicon-m-envelope')
                ->color('gray'),
            Stat::make('Public media', Media::query()->publiclyVisible()->count())
                ->description('Consent-filtered assets')
                ->icon('heroicon-m-photo')
                ->color('warning'),
        ];
    }
}
