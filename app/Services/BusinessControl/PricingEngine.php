<?php

namespace App\Services\BusinessControl;

use App\Models\Plan;
use App\Models\PromotionCampaign;
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

        $campaign = $this->resolveCampaign($planCode);
        $campaignDiscountAmount = $this->campaignDiscountAmount($campaign, $basePrice);
        $couponDiscountAmount = 0.0;

        $discountAmount = round($campaignDiscountAmount + $couponDiscountAmount, 2);
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
            'campaign_discount_amount' => round($campaignDiscountAmount, 2),
            'coupon_discount_amount' => round($couponDiscountAmount, 2),
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'campaign' => $campaign === null ? null : [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'discount_type' => $campaign->discount_type,
                'discount_value' => (float) $campaign->discount_value,
            ],
            'coupon' => null,
            'coupon_blocked_by_campaign' => false,
        ];
    }

    private function resolveCampaign(string $planCode): ?PromotionCampaign
    {
        return PromotionCampaign::query()
            ->active()
            ->withinWindow()
            ->forPlan($planCode)
            ->orderByDesc('discount_value')
            ->orderByDesc('id')
            ->first();
    }

    private function campaignDiscountAmount(?PromotionCampaign $campaign, float $basePrice): float
    {
        if ($campaign === null) {
            return 0.0;
        }

        $rawDiscount = $campaign->discount_type === 'percent'
            ? ($basePrice * ((float) $campaign->discount_value / 100))
            : (float) $campaign->discount_value;

        return round(min($basePrice, max(0, $rawDiscount)), 2);
    }
}
