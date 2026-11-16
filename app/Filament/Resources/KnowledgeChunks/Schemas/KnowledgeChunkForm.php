<?php

namespace App\Filament\Resources\KnowledgeChunks\Schemas;

use App\Models\KnowledgeChunk;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class KnowledgeChunkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('source_type')
                    ->label('Tipo de Fuente')
                    ->options([
                        'product' => 'Producto',
                        'category' => 'Categoría',
                        'campaign' => 'Campaña',
                        'coupon' => 'Cupón',
                        'shipping_zone' => 'Zona de Envío',
                        'service_policy' => 'Política / Servicio',
                    ])
                    ->required(),
                TextInput::make('source_id')
                    ->label('ID de Origen')
                    ->numeric(),
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->label('Contenido para RAG')
                    ->rows(6)
                    ->required()
                    ->columnSpanFull(),
                KeyValue::make('metadata')
                    ->label('Metadatos')
                    ->keyLabel('Clave')
                    ->valueLabel('Valor')
                    ->addActionLabel('Agregar metadato')
                    ->columnSpanFull(),
                Placeholder::make('embedding_status')
                    ->label('Vector Embedding')
                    ->content(fn (?KnowledgeChunk $record): string => $record && is_array($record->embedding) && count($record->embedding) > 0
                        ? 'Indexado (' . count($record->embedding) . ' dimensiones)'
                        : 'Sin indexar (usa el botón "Reindexar Embeddings RAG")'),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
