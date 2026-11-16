<?php

namespace App\Filament\Resources\KnowledgeChunks\Tables;

use App\Models\KnowledgeChunk;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KnowledgeChunksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_type')
                    ->label('Fuente')
                    ->badge()
                    ->searchable(),
                TextColumn::make('source_id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('embedding')
                    ->label('Embedding')
                    ->getStateUsing(fn (KnowledgeChunk $record): string => is_array($record->embedding) && count($record->embedding) > 0
                        ? count($record->embedding) . ' dims'
                        : 'Pendiente')
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, 'dims') ? 'success' : 'warning'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
