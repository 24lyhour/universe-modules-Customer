<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Customer\Services\CustomerWidgetService;

class CustomerWidgetController extends Controller
{
    public function __construct(
        protected CustomerWidgetService $widgetService
    ) {}

    /**
     * Get widget data for the dashboard.
     */
    public function index(Request $request): Response
    {
        $dateRange = $request->input('date_range', '30d');

        $widgetData = $this->widgetService->getWidgetData($dateRange);

        return Inertia::render('customer::dashboard/customer/Widget', [
            'widgetData' => $widgetData,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Get widget data as JSON (for AJAX updates).
     */
    public function getData(Request $request): JsonResponse
    {
        $dateRange = $request->input('date_range', '30d');

        $widgetData = $this->widgetService->getWidgetData($dateRange);

        return response()->json($widgetData);
    }

    /**
     * Get metrics summary only.
     */
    public function metrics(Request $request): JsonResponse
    {
        $dateRange = $request->input('date_range', '30d');

        $metrics = $this->widgetService->getMetrics($dateRange);

        return response()->json($metrics);
    }

    /**
     * Get growth data for charts.
     */
    public function growth(Request $request): JsonResponse
    {
        $dateRange = $request->input('date_range', '30d');

        $growthData = $this->widgetService->getGrowthData($dateRange);

        return response()->json($growthData);
    }
}
