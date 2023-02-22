<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        try {

            // Create a new User object
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->type = User::TYPE_MERCHANT;
            $user->password = $data['api_key'];

            // Save the User object to the database
            $user->save();

            // Create a new Merchant object
            $merchant = new Merchant();
            $merchant->domain = $data['domain'];
            $merchant->user_id = $user->id;
            $merchant->display_name = $data['name'];

            // Save the Merchant object to the database
            $merchant->save();

            // Return the newly created Merchant object
            return $merchant;
        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        try {
            $validator = Validator::make($data, [
                'domain' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'api_key' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first());
            }

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['api_key'];
            $user->save();

            $merchant = $user->merchant;
            $merchant->name = $data['name'];
            $merchant->domain = $data['domain'];
            $merchant->save();
        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        try {
            $user = User::whereEmail($email)->first();

            if ($user && $user->type === User::TYPE_MERCHANT) {
                return $user->merchant;
            }

            return null;

        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }

    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        try {
            $orders = Order::where([
                ['affiliate_id', $affiliate->id],
                ['payout_status', Order::STATUS_UNPAID]])
                ->get();

            foreach ($orders as $order) {
                PayoutOrderJob::dispatch($order, $affiliate);
                $order->payout_status = Order::STATUS_PAID;
                $order->save();
            }
        } catch (\Exception $e) {
            errorLogs(__METHOD__, $e->getLine(), $e->getMessage());
        }
    }
}
