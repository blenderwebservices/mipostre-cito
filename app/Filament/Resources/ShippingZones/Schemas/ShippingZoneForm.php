<?php

namespace App\Filament\Resources\ShippingZones\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShippingZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('zip_code')
                    ->label('Código Postal')
                    ->required()
                    ->maxLength(10),
                TextInput::make('neighborhood')
                    ->label('Colonia')
                    ->required(),
                TextInput::make('zone_name')
                    ->label('Zona (ej. Zona Centro)'),
                TextInput::make('delivery_cost')
                    ->label('Costo de Envío')
                    ->required()
                    ->numeric()
                    ->default(35)
                    ->prefix('$'),
                TextInput::make('min_order_for_free_shipping')
                    ->label('Mínimo para Envío Gratis')
                    ->required()
                    ->numeric()
                    ->default(200)
                    ->prefix('$'),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
