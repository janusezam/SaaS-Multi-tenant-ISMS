<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Coupon Redemptions · {{ $coupon->code }}</h2>
            <a href="{{ route('central.business-control.coupons.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:border-white/15 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                Back to Coupons
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Redemption history for coupon <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $coupon->code }}</span>.
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <div class="overflow-x-auto">
                <table class="min-w-[860px] w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-100 text-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3 text-left">School</th>
                            <th class="px-4 py-3 text-left">Redeemed At</th>
                            <th class="px-4 py-3 text-left">Applied Plan</th>
                            <th class="px-4 py-3 text-left">Billing Cycle</th>
                            <th class="px-4 py-3 text-left">Discount Applied</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800 dark:divide-white/10 dark:text-slate-200">
                        @forelse ($redemptions as $redemption)
                            <tr>
                                <td class="px-4 py-3">{{ $redemption->university?->name ?? $redemption->tenant_id }}</td>
                                <td class="px-4 py-3">{{ $redemption->approved_at?->format('M d, Y h:i A') ?? $redemption->updated_at?->format('M d, Y h:i A') }}</td>
                                <td class="px-4 py-3">{{ strtoupper($redemption->requested_plan) }}</td>
                                <td class="px-4 py-3">{{ strtoupper($redemption->billing_cycle) }}</td>
                                <td class="px-4 py-3">${{ number_format((float) $redemption->discount_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No redemption history for this coupon yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            {{ $redemptions->links() }}
        </div>
    </div>
</x-app-layout>
