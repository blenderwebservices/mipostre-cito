<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pendiente'),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel()
                    ->required(),
                TextInput::make('delivery_zone')
                    ->required(),
                TextInput::make('delivery_address')
                    ->required(),
                DatePicker::make('scheduled_date')
                    ->required(),
                TextInput::make('scheduled_slot')
                    ->required(),
                TextInput::make('payment_method')
                    ->required()
                    ->default('efectivo'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('source')
                    ->required()
                    ->default('web'),
            ]);
    }
}
