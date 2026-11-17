<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Services\RagService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class HomePage extends Component
{
    use InteractsWithCart;

    public string $coverageQuery = '';
    public string $coverageAddress = '';
    public bool $isCoverageModalOpen = false;
    public ?string $coverageError = null;
    public ?string $coverageSuccess = null;

    public function openCoverageModal(): void
    {
        $this->coverageError = null;
        $this->coverageSuccess = null;
        $this->isCoverageModalOpen = true;
    }

    public function closeCoverageModal(): void
    {
        $this->isCoverageModalOpen = false;
    }

    public function checkCoverage(RagService $ragService): void
    {
        $this->coverageError = null;
        $this->coverageSuccess = null;

        $input = trim($this->coverageAddress) !== ''
            ? trim($this->coverageAddress . ' ' . $this->coverageQuery)
            : trim($this->coverageQuery);

        $result = $ragService->resolveMexicanCoverage($input);

        if ($result['covered'] && $result['zone']) {
            session()->put('postrecito_zone_id', $result['zone']->id);
            if (trim($this->coverageAddress) !== '') {
                session()->put('postrecito_coverage_address', trim($this->coverageAddress));
            }

            $this->coverageSuccess = $result['message'];
            return;
        }

        $this->coverageError = $result['message'];
    }

    public function render()
    {
        $activeCampaign = Campaign::currentlyActive()->with('coupon')->first();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $favoriteProducts = Product::with(['category', 'variations' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->where('is_favorite', true)
            ->take(4)
            ->get();

        $activeZone = session()->has('postrecito_zone_id')
            ? ShippingZone::find(session('postrecito_zone_id'))
            : null;

        return view('livewire.home-page', [
            'activeCampaign' => $activeCampaign,
            'categories' => $categories,
            'favoriteProducts' => $favoriteProducts,
            'activeZone' => $activeZone,
            'cartSummary' => $this->getCartSummary(),
        ]);
    }
}
