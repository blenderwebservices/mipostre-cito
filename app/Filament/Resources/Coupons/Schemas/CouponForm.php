<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('discount_type')
                    ->required()
                    ->default('percentage'),
                TextInput::make('discount_value')
                    ->required()
                    ->numeric(),
                TextInput::make('min_cart_value')
                    ->required()
                    ->numeric()
                    ->default(0),
                DatePicker::make('valid_from'),
                DatePicker::make('valid_until'),
                TextInput::make('usage_limit')
                    ->numeric(),
                TextInput::make('times_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
