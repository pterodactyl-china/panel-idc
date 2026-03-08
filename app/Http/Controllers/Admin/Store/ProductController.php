<?php

namespace Pterodactyl\Http\Controllers\Admin\Store;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\Product;
use Pterodactyl\Models\Location;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Egg;
use Pterodactyl\Services\Store\PointsCalculator;
use Pterodactyl\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function __construct(
        private readonly AlertsMessageBag $alert,
        private readonly PointsCalculator $calculator,
    ) {
    }

    public function index(): View
    {
        return view('admin.store.products.index', [
            'products'    => Product::query()->orderBy('sort_order')->orderBy('price')->paginate(50),
            'cpu_rate'    => $this->calculator->getCpuRatePerCore(),
            'memory_rate' => $this->calculator->getMemoryRatePerGb(),
            'disk_rate'   => $this->calculator->getDiskRatePerGb(),
        ]);
    }

    public function create(): View
    {
        return view('admin.store.products.new', [
            'product'      => null,
            'locations'    => Location::all(),
            'nodes'        => Node::all(),
            'eggs'         => Egg::with('nest')->orderBy('name')->get(),
            'cpu_rate'     => $this->calculator->getCpuRatePerCore(),
            'memory_rate'  => $this->calculator->getMemoryRatePerGb(),
            'disk_rate'    => $this->calculator->getDiskRatePerGb(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        Product::create($data);
        $this->alert->success('商品已创建。')->flash();
        return redirect()->route('admin.store.products');
    }

    public function edit(int $id): View
    {
        return view('admin.store.products.new', [
            'product'      => Product::findOrFail($id),
            'locations'    => Location::all(),
            'nodes'        => Node::all(),
            'eggs'         => Egg::with('nest')->orderBy('name')->get(),
            'cpu_rate'     => $this->calculator->getCpuRatePerCore(),
            'memory_rate'  => $this->calculator->getMemoryRatePerGb(),
            'disk_rate'    => $this->calculator->getDiskRatePerGb(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $this->validateProduct($request);
        Product::findOrFail($id)->update($data);
        $this->alert->success('商品已更新。')->flash();
        return redirect()->route('admin.store.products');
    }

    public function destroy(int $id): RedirectResponse
    {
        Product::findOrFail($id)->delete();
        $this->alert->success('商品已删除。')->flash();
        return redirect()->route('admin.store.products');
    }

    private function validateProduct(Request $request): array
    {
        $isServer = $request->input('type') === 'server';

        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'description' => 'nullable|string|max:1000',
            'type'        => 'required|string|in:points,server_days,server,custom',
            'value'       => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'currency'    => 'required|string|max:8',
            'is_active'   => 'sometimes|boolean',
            'sort_order'  => 'nullable|integer|min:0',
            // Server package fields
            'location_id'        => 'nullable|integer|exists:locations,id',
            'node_id'            => 'nullable|integer|exists:nodes,id',
            'egg_id'             => 'nullable|integer|exists:eggs,id',
            'cpu'                => 'nullable|integer|min:0',
            'memory'             => 'nullable|integer|min:0',
            'disk'               => 'nullable|integer|min:0',
            'databases'          => 'nullable|integer|min:0',
            'backups'            => 'nullable|integer|min:0',
            'allocations'        => 'nullable|integer|min:0',
            // Per-product daily-points rates
            'points_cpu_rate'    => 'nullable|integer|min:0',
            'points_memory_rate' => 'nullable|integer|min:0',
            'points_disk_rate'   => 'nullable|integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Clear server-only fields when type is not 'server'
        if (!$isServer) {
            foreach (['location_id','node_id','egg_id','cpu','memory','disk','databases','backups','allocations',
                      'points_cpu_rate','points_memory_rate','points_disk_rate'] as $f) {
                $data[$f] = null;
            }
        }

        return $data;
    }
}
