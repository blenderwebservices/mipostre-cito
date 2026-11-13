<?php

namespace App\Filament\Widgets;

use App\Models\KnowledgeChunk;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', 'cancelado')->sum('total');
        $chatbotOrders = Order::where('source', 'chatbot')->count();
        $totalProducts = Product::where('is_available', true)->count();
        $indexedChunks = KnowledgeChunk::whereNotNull('embedding')->count();

        return [
            Stat::make('Ventas Totales', '$' . number_format($totalRevenue, 2) . ' MXN')
                ->description("{$totalOrders} pedidos recibidos")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pedidos vía Chatbot RAG', (string) $chatbotOrders)
                ->description('Conversiones automáticas por IA')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary'),

            Stat::make('Catálogo Activo', "{$totalProducts} productos")
                ->description("{$indexedChunks} fragmentos RAG indexados")
                ->descriptionIcon('heroicon-m-cake')
                ->color('warning'),
        ];
    }
}
