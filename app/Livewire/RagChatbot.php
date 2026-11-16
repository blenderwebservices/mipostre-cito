<?php

namespace App\Livewire;

use App\Services\RagService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RagChatbot extends Component
{
    public bool $isOpen = false;
    public string $userMessage = '';
    public bool $isLoading = false;
    public bool $isLiveAi = false;

    /**
     * @var array<int, array{role: string, content: string, time: string, is_order?: bool, order_number?: string, requires_auth?: bool}>
     */
    public array $messages = [];

    public function mount(RagService $ragService): void
    {
        $this->isLiveAi = $ragService->isLiveAiEnabled();
        $userName = Auth::check() ? Auth::user()->name : 'amigo/a';

        $this->messages[] = [
            'role' => 'assistant',
            'content' => "¡Hola, **{$userName}**! 🍨 Soy **Postrecito Bot**, tu asesor dulce en **Postrecito de Jaan**.\n\n" .
                "Puedo recomendarte sabores, explicarte nuestras presentaciones (desde vasito individual hasta familiares de 1 litro y moldes de pay para fiestas), resolver dudas sobre cobertura de entrega y **ayudarte a hacer tu pedido directamente aquí por chat**.\n\n" .
                "¿Qué se te antoja probar hoy?",
            'time' => now()->format('H:i'),
        ];
    }

    public function toggleChat(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function sendMessage(RagService $ragService): void
    {
        $text = trim($this->userMessage);
        if ($text === '') {
            return;
        }

        // Registrar mensaje de usuario
        $this->messages[] = [
            'role' => 'user',
            'content' => $text,
            'time' => now()->format('H:i'),
        ];

        $this->userMessage = '';
        $this->isLoading = true;

        // Historial para contexto
        $history = array_map(fn ($m) => [
            'role' => $m['role'],
            'content' => $m['content'],
        ], array_slice($this->messages, -8));

        // Procesar mediante RAG Service (Embeddings + Retrieval + Gemini API)
        $result = $ragService->chat($text, Auth::user(), $history);

        $this->isLiveAi = ($result['ai_mode'] ?? 'mocking') === 'live';

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $result['reply'],
            'time' => now()->format('H:i'),
            'is_order' => $result['order_placed'],
            'order_number' => $result['order']?->order_number,
            'requires_auth' => $result['requires_auth'],
        ];

        $this->isLoading = false;

        if ($result['order_placed']) {
            $this->dispatch('orderPlacedFromChat');
        }
    }

    public function askQuickQuestion(string $question): void
    {
        $this->userMessage = $question;
        $this->sendMessage(app(RagService::class));
    }

    public function render()
    {
        return view('livewire.rag-chatbot');
    }
}
