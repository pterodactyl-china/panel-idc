<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Product;

class StoreController extends ClientApiController
{
    /**
     * Return a list of active products in the store.
     */
    public function index(): JsonResponse
    {
        $products = Product::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return new JsonResponse([
            'products' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'type' => $p->type,
                'value' => $p->value,
                'price' => $p->price,
                'currency' => $p->currency,
            ]),
        ]);
    }
}
