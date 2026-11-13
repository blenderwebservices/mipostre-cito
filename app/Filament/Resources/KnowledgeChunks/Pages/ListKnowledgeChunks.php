<?php

namespace App\Filament\Resources\KnowledgeChunks\Pages;

use App\Filament\Resources\KnowledgeChunks\KnowledgeChunkResource;
use App\Services\RagService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListKnowledgeChunks extends ListRecords
{
    protected static string $resource = KnowledgeChunkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('reindexRag')
                ->label('Reindexar Embeddings RAG')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function (RagService $ragService) {
                    $count = $ragService->indexAll(force: true);

                    Notification::make()
                        ->title('Embeddings RAG actualizados')
                        ->body("Se indexaron con éxito {$count} fragmentos de conocimiento con Gemini.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
