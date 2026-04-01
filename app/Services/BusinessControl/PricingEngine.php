<?php

namespace App\Services\BusinessControl;

use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Validation\ValidationException;

class PricingEngine
{
    /**
     * @return array<string, mixed>
     */
    public function quote(string $planCode, string $billingCycle, ?string $couponCode = null): array
    {
        $plan = Plan::query()
            ->active()
            ->where('code', $planCode)
            ->first();

        if ($plan === null) {
            throw ValidationException::withMessages([
                'plan' => 'Selected plan is not available.',
            ]);
        }

        if (! in_array($billingCycle, ['monthly', 'yearly'], true)) {
            throw ValidationException::withMessages([
                'billing_cycle' => 'Invalid billing cycle selected.',
            ]);
        }

        $basePrice = $billingCycle === 'yearly'
            ? (float) $plan->yearly_price
            : (float) $plan->monthly_price;

        $coupon = $this->resolveCoupon($couponCode, $planCode);
        $discountAmount = $this->discountAmount($coupon, $basePrice);
        $finalPrice = max(0, round($basePrice - $discountAmount, 2));

        return [
            'plan' => [
                'id' => $plan->id,
                'code' => $plan->code,
                'name' => $plan->name,
                'monthly_price' => (float) $plan->monthly_price,
                'yearly_price' => (float) $plan->yearly_price,
                'yearly_discount_percent' => (float) $plan->yearly_discount_percent,
            ],
            'billing_cycle' => $billingCycle,
            'base_price' => round($basePrice, 2),
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'coupon' => $coupon === null ? null : [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float) $coupon->discount_value,
            ],
        ];
    }

    public function validateCouponCode(string $planCode, string $couponCode): Coupon
    {
        $coupon = Coupon::query()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($couponCode))])
            ->first();

        if ($coupon === null || ! $coupon->isAvailableFor($planCode)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Coupon is invalid, expired, or not applicable to this plan.',
            ]);
        }

        return $coupon;
    }

    private function resolveCoupon(?string $couponCode, string $planCode): ?Coupon
    {
        $normalizedCode = trim((string) $couponCode);

        if ($normalizedCode === '') {
            return null;
        }

        return $this->validateCouponCode($planCode, $normalizedCode);
    }

    private function discountAmount(?Coupon $coupon, float $basePrice): float
    {
        if ($coupon === null) {
            return 0.0;
        }

        $rawDiscount = $coupon->discount_type === 'percent'
            ? ($basePrice * ((float) $coupon->discount_value / 100))
            : (float) $coupon->discount_value;

        return round(min($basePrice, max(0, $rawDiscount)), 2);
    }
}
