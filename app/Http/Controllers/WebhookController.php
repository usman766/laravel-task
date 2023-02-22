<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Pass the necessary data to the process order method
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = [
                'order_id' => $request->input('order_id'),
                'subtotal' => $request->input('subtotal'),
                'domain' => $request->input('domain'),
                'discount_code' => $request->input('discount_code'),
                'commission_rate' => $request->input('commission_rate'),
                'email' => $request->input('email'),
                'name' => $request->input('name'),
            ];
            $this->orderService->processOrder($data);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }
}
