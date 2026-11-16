<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\KnowledgeChunk;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::whereIn('status', ['pending', 'preparing'])->count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $chatbotOrders = Order::where('source', 'chatbot')->count();
        $activeCampaigns = Campaign::currentlyActive()->count();
        $totalProducts = Product::where('is_active', true)->count();
        $indexedChunks = KnowledgeChunk::whereNotNull('embedding')->count();

        return [
            Stat::make('Ventas Totales', '$' . number_format($totalRevenue, 2) . ' MXN')
                ->description("{$totalOrders} pedidos ({$pendingOrders} por atender)")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pedidos vía Chatbot RAG', (string) $chatbotOrders)
                ->description("{$indexedChunks} fragmentos RAG indexados")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary'),

            Stat::make('Catálogo y Campañas', "{$totalProducts} sabores activos")
                ->description("{$activeCampaigns} campaña(s) de temporada hoy")
                ->descriptionIcon('heroicon-m-cake')
                ->color('warning'),
        ];
    }
}
