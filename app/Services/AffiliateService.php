<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate, string $discount_code): Affiliate
    {

        try {
            // Create a new Affiliate instance
            $affiliate = new Affiliate();
            // Set the properties of the affiliate instance
            $affiliate->discount_code = $discount_code;
            $affiliate->commission_rate = $commissionRate;
            $affiliate->email = $email;
            // Save the affiliate to the database
            $affiliate->save();

            // Return the newly created affiliate instance
            return $affiliate;
        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }
}
