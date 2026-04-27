<?php

namespace Modules\Customer\Http\Middleware;

use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;

class DashboardMiddlewareHandle
{
    protected static bool $registered = false;

    public function handle(Request $request, Closure $next)
    {
        if ($request->is('dashboard', 'dashboard/*')) {
            $this->registerMenuItems();
        }

        return $next($request);
    }

    protected function registerMenuItems(): void
    {
        if (static::$registered) {
            return;
        }

        MenuService::addMenuItem(
            menu: 'primary',
            id: 'customer',
            title: __('Customer'),
            url: route('customer.customers.index'),
            icon: 'Users',
            order: 40,
            permissions: 'customers.view_any',
            route: 'customer.*'
        );

        MenuService::addSubmenuItem('primary', 'customer', __('Customers'), route('customer.customers.index'), 10, 'customers.view_any', 'customer.customers.*', 'Users');

        static::$registered = true;
    }
}
