<?php

namespace App\Filament\Resources\Flavors;

use App\Filament\Resources\Flavors\Pages\CreateFlavor;
use App\Filament\Resources\Flavors\Pages\EditFlavor;
use App\Filament\Resources\Flavors\Pages\ListFlavors;
use App\Filament\Resources\Flavors\Schemas\FlavorForm;
use App\Filament\Resources\Flavors\Tables\FlavorsTable;
use App\Models\Flavor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FlavorResource extends Resource
{
    protected static ?string $model = Flavor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FlavorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlavorsTable::configure($table);
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
            'index' => ListFlavors::route('/'),
            'create' => CreateFlavor::route('/create'),
            'edit' => EditFlavor::route('/{record}/edit'),
        ];
    }
}
