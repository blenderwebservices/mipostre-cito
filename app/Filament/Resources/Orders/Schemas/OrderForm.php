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
                TextInput::make('order_number')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('address_id')
                    ->relationship('address', 'id'),
                Select::make('coupon_id')
                    ->relationship('coupon', 'id'),
                Select::make('shipping_zone_id')
                    ->relationship('shippingZone', 'id'),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel(),
                TextInput::make('delivery_address_snapshot')
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                DatePicker::make('delivery_date')
                    ->required(),
                TextInput::make('delivery_time_slot')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_method')
                    ->required()
                    ->default('efectivo'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('source')
                    ->required()
                    ->default('web'),
            ]);
    }
}
