<?php

namespace App\Console\Commands;

use App\Services\RagService;
use Illuminate\Console\Command;

class IndexKnowledgeBaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rag:index {--force : Reindex chunks that already have embeddings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all catalog and service knowledge chunks using Gemini embeddings';

    /**
     * Execute the console command.
     */
    public function handle(RagService $ragService): int
    {
        $this->info('Iniciando indexación de embeddings RAG...');
        $force = (bool) $this->option('force');

        $count = $ragService->indexAll($force);

        $this->info("¡Completado con éxito! Se indexaron {$count} fragmentos de conocimiento.");

        return self::SUCCESS;
    }
}

