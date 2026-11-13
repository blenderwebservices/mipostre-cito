<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('flavor_id')
                    ->relationship('flavor', 'name')
                    ->required(),
                Select::make('presentation_id')
                    ->relationship('presentation', 'name')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('original_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(50),
                FileUpload::make('image_url')
                    ->image(),
                TextInput::make('badge'),
                Toggle::make('is_available')
                    ->required(),
                Toggle::make('is_promo')
                    ->required(),
                Toggle::make('is_returnable_container')
                    ->required(),
            ]);
    }
}
