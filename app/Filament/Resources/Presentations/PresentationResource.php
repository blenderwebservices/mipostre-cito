<?php

namespace App\Filament\Resources\Presentations;

use App\Filament\Resources\Presentations\Pages\CreatePresentation;
use App\Filament\Resources\Presentations\Pages\EditPresentation;
use App\Filament\Resources\Presentations\Pages\ListPresentations;
use App\Filament\Resources\Presentations\Schemas\PresentationForm;
use App\Filament\Resources\Presentations\Tables\PresentationsTable;
use App\Models\Presentation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PresentationResource extends Resource
{
    protected static ?string $model = Presentation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PresentationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PresentationsTable::configure($table);
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
            'index' => ListPresentations::route('/'),
            'create' => CreatePresentation::route('/create'),
            'edit' => EditPresentation::route('/{record}/edit'),
        ];
    }
}
