<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Product;
use Pterodactyl\Models\Location;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\PaymentSetting;

class StoreController extends ClientApiController
{
    /**
     * Return a list of active products in the store, along with enabled payment methods.
     */
    public function index(): JsonResponse
    {
        $products = Product::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return new JsonResponse([
            'products'        => $products->map(fn ($p) => $this->transformProduct($p)),
            'payment_methods' => PaymentSetting::enabledMethods(),
        ]);
    }

    private function transformProduct(Product $p): array
    {
        $data = [
            'id'          => $p->id,
            'name'        => $p->name,
            'description' => $p->description,
            'type'        => $p->type,
            'value'       => $p->value,
            'price'       => $p->price,
            'currency'    => $p->currency,
        ];

        // For server packages, include resource configuration.
        if ($p->type === 'server') {
            $location = $p->location_id ? Location::find($p->location_id) : null;
            $node     = $p->node_id     ? Node::find($p->node_id) : null;

            $data['server_config'] = [
                'location'    => $location ? ['id' => $location->id, 'short' => $location->short, 'long' => $location->long] : null,
                'node'        => $node     ? ['id' => $node->id, 'name' => $node->name] : null,
                'cpu'         => $p->cpu,
                'memory'      => $p->memory,
                'disk'        => $p->disk,
                'databases'   => $p->databases,
                'backups'     => $p->backups,
                'allocations' => $p->allocations,
            ];
        }

        return $data;
    }
}
