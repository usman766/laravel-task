<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Merchant;
use App\Models\Order;

class OrderService
{
    protected  $affiliateService;
    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal: float, domain: string, discount_code: string, email: string, name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        try {
            // Check if the order_id already exists
            if ($this->orderExists($data['order_id'])) {
                return;
            }

            // Check if the email is associated with an existing affiliate
            $affiliateId = $this->getAffiliateId($data['email']);
            if (!$affiliateId) {
                // Create a new affiliate
                $affiliateId = $this->createAffiliate($data['discount_code'], $data['commission_rate'],$data['email'] );
            }

            // Calculate commission based on order data
            $commission = $this->calculateCommission($data['subtotal'], $data['domain'], $data['discount_code']);

            // Log the commission information
            $this->logCommission($affiliateId, $data['order_id'], $commission);
        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }

/**
 * Check if an order with the given ID already exists.
 *
 * @param string $orderId
 * @return bool
 */
    private function orderExists(string $orderId): bool
    {try {
        $order = Order::where('id', $orderId)->first();
        return $order !== null;
    } catch (\Exception $e) {
        errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
    }
    }

/**
 * Get the ID of the affiliate associated with the given email address.
 *
 * @param string $email
 * @return int|null The affiliate ID or null if not found
 */
    private function getAffiliateId(string $email): ?int
    {
        try {
            $affiliate = Affiliate::where('email', $email)->pluck('id')->first();
            return $affiliate ?? null;

        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }

/**
 * Create a new affiliate with the given email and name.
 *
 * @param string $email
 * @param string $name
 * @return int The ID of the new affiliate
 */
    private function createAffiliate( $discount_code,  $commission_rate,$email ): int
    {
        try {
            $affiliate = new Affiliate();
            $affiliate->email = $email;
            $affiliate->discount_code = $discount_code;
            $affiliate->commission_rate = $commission_rate;
            $affiliate->save();
            return $affiliate->id;

        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }

/**
 * Calculate the commission amount based on the subtotal price, merchant domain, and discount code.
 *
 * @param float $subtotal
 * @param string $merchantDomain
 * @param string $discountCode
 * @return float The commission amount
 */
    private function calculateCommission(float $subtotal, string $merchantDomain, string $discountCode): float
    {
        try {
            $commissionRate = 0.1; // 10% commission rate
            $commissionAmount = $subtotal * $commissionRate;
            return $commissionAmount;

        } catch (\Exception$e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }

/**
 * Log the commission information for the given affiliate and order.
 *
 * @param int $affiliateId
 * @param string $orderId
 * @param float $commission
 * @return void
 */
    private function logCommission(int $affiliateId, string $orderId, float $commission)
    {
        try {
            Commission::create([
                'affiliate_id' => $affiliateId,
                'order_id' => $orderId,
                'commission' => $commission,
            ]);
        } catch (\Exception$e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }
}
