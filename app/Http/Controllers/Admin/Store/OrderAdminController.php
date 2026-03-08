<?php

namespace Pterodactyl\Http\Controllers\Admin\Store;

use Illuminate\View\View;
use Pterodactyl\Models\Order;
use Pterodactyl\Http\Controllers\Controller;

class OrderAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.store.orders.index', [
            'orders' => Order::query()
                ->with('user', 'product')
                ->orderByDesc('created_at')
                ->paginate(50),
        ]);
    }
}
