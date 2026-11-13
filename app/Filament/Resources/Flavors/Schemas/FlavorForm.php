<?php

namespace App\Filament\Resources\Flavors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FlavorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('icon')
                    ->required()
                    ->default('fa-solid fa-ice-cream'),
                TextInput::make('bg_color')
                    ->required()
                    ->default('bg-pink-100'),
                TextInput::make('text_color')
                    ->required()
                    ->default('text-pink-600'),
                Toggle::make('is_favorite')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
