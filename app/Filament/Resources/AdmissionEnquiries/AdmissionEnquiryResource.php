<?php

namespace App\Filament\Resources\AdmissionEnquiries;

use App\Filament\Resources\AdmissionEnquiries\Pages\EditAdmissionEnquiry;
use App\Filament\Resources\AdmissionEnquiries\Pages\ListAdmissionEnquiries;
use App\Filament\Resources\AdmissionEnquiries\Pages\ViewAdmissionEnquiry;
use App\Filament\Resources\AdmissionEnquiries\Schemas\AdmissionEnquiryForm;
use App\Filament\Resources\AdmissionEnquiries\Schemas\AdmissionEnquiryInfolist;
use App\Filament\Resources\AdmissionEnquiries\Tables\AdmissionEnquiriesTable;
use App\Models\AdmissionEnquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AdmissionEnquiryResource extends Resource
{
    protected static ?string $model = AdmissionEnquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|UnitEnum|null $navigationGroup = 'School Office';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        return (string) AdmissionEnquiry::query()->where('status', 'new')->count();
    }

    public static function form(Schema $schema): Schema
    {
        return AdmissionEnquiryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdmissionEnquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdmissionEnquiriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmissionEnquiries::route('/'),
            'view' => ViewAdmissionEnquiry::route('/{record}'),
            'edit' => EditAdmissionEnquiry::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
