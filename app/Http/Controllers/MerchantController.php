<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;

use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    protected $merchantService;
    public function __construct(
        MerchantService $merchantService
    ) {
        $this->merchantService = $merchantService;
    }

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        try {
            $from = $request->input('from');
            $to = $request->input('to');

            $orders = Order::whereBetween('created_at', [$from, $to])->get();
            $orderCount = $orders->count();
            $revenue = $orders->sum('subtotal');
            $commissions = $orders->whereNotNull('affiliate_id')->where('payout_status', Order::STATUS_UNPAID)->sum(function ($order) {
                return $order->subtotal * $order->affiliate->commission_rate / 100;
            });

            return response()->json([
                'count' => $orderCount,
                'commission_owed' => $commissions,
                'revenue' => $revenue,
            ]);

        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }
}
