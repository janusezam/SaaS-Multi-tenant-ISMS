@php
    $canSubmitUpgradeRequest = auth()->user()?->role === 'university_admin';
@endphp

<div id="tenant-upgrade-modal" class="fixed inset-0 z-[70] hidden" aria-hidden="true">
    <button type="button" data-upgrade-close class="absolute inset-0 bg-slate-950/70"></button>
    <div class="relative mx-auto mt-14 w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-amber-700 dark:text-amber-200">Upgrade Request</p>
                <h3 class="mt-1 text-xl font-semibold text-slate-900 dark:text-slate-100">Pro Subscription</h3>
            </div>
            <button type="button" data-upgrade-close class="rounded-lg border border-slate-300 px-2 py-1 text-xs text-slate-700 hover:bg-slate-100 dark:border-white/15 dark:text-slate-300 dark:hover:bg-white/10">Close</button>
        </div>

        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Request central approval for Pro. No payment is collected here. Pricing and coupon checks are validated from central business control.</p>

        <div class="mt-4 grid grid-cols-3 gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm dark:border-white/10 dark:bg-slate-950/40">
            <div>
                <p class="text-[11px] uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Plan</p>
                <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100" data-upgrade-summary-plan>PRO</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Billing Cycle</p>
                <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100" data-upgrade-summary-cycle>MONTHLY</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Price</p>
                <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100" data-upgrade-summary-price>$0.00</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <button type="button" data-cycle-option="monthly" class="upgrade-cycle-btn rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-white/15 dark:bg-white/5 dark:text-slate-100">Monthly</button>
            <button type="button" data-cycle-option="yearly" class="upgrade-cycle-btn rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-white/15 dark:bg-white/5 dark:text-slate-100">Yearly</button>
        </div>

        <div class="mt-4">
            <label for="upgrade-coupon" class="mb-2 block text-sm text-slate-700 dark:text-slate-300">Promo Code (optional)</label>
            <div class="flex gap-2">
                <input id="upgrade-coupon" type="text" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" placeholder="Enter coupon code" />
                <button type="button" data-apply-coupon class="rounded-xl border border-cyan-300 bg-cyan-100 px-3 py-2 text-sm text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">Apply</button>
            </div>
            <p class="mt-1 hidden text-xs text-emerald-700 dark:text-emerald-200" data-upgrade-coupon-success></p>
            <p class="mt-1 hidden text-xs text-rose-700 dark:text-rose-300" data-upgrade-error></p>
        </div>

        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-white/10 dark:bg-slate-950/60">
            <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                <span>Base price</span>
                <span data-price-base>$0.00</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-slate-700 dark:text-slate-300">
                <span>Discount</span>
                <span data-price-discount>$0.00</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3 text-slate-900 dark:border-white/10 dark:text-slate-100">
                <span class="font-medium">Final price</span>
                <span class="text-lg font-semibold" data-price-final>$0.00</span>
            </div>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400" data-yearly-savings></p>
            <p class="mt-2 text-xs text-amber-700 dark:text-amber-200" data-upgrade-pending></p>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" data-upgrade-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-white/15 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
            @if ($canSubmitUpgradeRequest)
                <button type="button" data-submit-upgrade class="inline-flex items-center justify-center rounded-xl border border-emerald-300 bg-emerald-100 px-4 py-2.5 text-sm font-medium text-emerald-800 hover:bg-emerald-200 dark:border-emerald-300/30 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">
                    Submit upgrade request
                </button>
            @else
                <span class="inline-flex items-center rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm text-slate-500 dark:border-white/15 dark:bg-slate-800 dark:text-slate-400">Only university admins can submit upgrade requests.</span>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('tenant-upgrade-modal');
        if (!modal) {
            return;
        }

        var previewUrl = @json(route('tenant.subscription.preview'));
        var submitUrl = @json(route('tenant.subscription.upgrade-requests.store'));
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        var state = {
            plan: 'pro',
            billingCycle: window.__ismsSubscriptionBillingCycle === 'yearly' ? 'yearly' : 'monthly',
            couponCode: '',
            pending: false,
        };

        var errorNode = modal.querySelector('[data-upgrade-error]');
        var successNode = modal.querySelector('[data-upgrade-coupon-success]');
        var pendingNode = modal.querySelector('[data-upgrade-pending]');
        var baseNode = modal.querySelector('[data-price-base]');
        var discountNode = modal.querySelector('[data-price-discount]');
        var finalNode = modal.querySelector('[data-price-final]');
        var savingsNode = modal.querySelector('[data-yearly-savings]');
        var summaryCycleNode = modal.querySelector('[data-upgrade-summary-cycle]');
        var summaryPriceNode = modal.querySelector('[data-upgrade-summary-price]');
        var submitButton = modal.querySelector('[data-submit-upgrade]');
        var couponInput = modal.querySelector('#upgrade-coupon');
        var couponValidationTimer = null;

        function formatCurrency(value) {
            var amount = Number(value || 0);
            return '$' + amount.toFixed(2);
        }

        function setError(message) {
            if (errorNode) {
                errorNode.textContent = message || '';
                errorNode.classList.toggle('hidden', !message);
            }

            if (message && successNode) {
                successNode.textContent = '';
                successNode.classList.add('hidden');
            }
        }

        function setCouponSuccess(message) {
            if (successNode) {
                successNode.textContent = message || '';
                successNode.classList.toggle('hidden', !message);
            }

            if (message && errorNode) {
                errorNode.textContent = '';
                errorNode.classList.add('hidden');
            }
        }

        function setPending(message) {
            if (pendingNode) {
                pendingNode.textContent = message || '';
            }

            if (submitButton) {
                submitButton.disabled = state.pending;
                submitButton.classList.toggle('opacity-60', state.pending);
                submitButton.classList.toggle('cursor-not-allowed', state.pending);
            }
        }

        function openModal() {
            state.billingCycle = window.__ismsSubscriptionBillingCycle === 'yearly' ? 'yearly' : 'monthly';
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            syncCycleButtons();
            refreshQuote();
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        }

        function syncCycleButtons() {
            modal.querySelectorAll('.upgrade-cycle-btn').forEach(function (button) {
                var isActive = button.getAttribute('data-cycle-option') === state.billingCycle;
                button.classList.toggle('border-cyan-300', isActive);
                button.classList.toggle('bg-cyan-100', isActive);
                button.classList.toggle('text-cyan-800', isActive);
                button.classList.toggle('dark:border-cyan-300/40', isActive);
                button.classList.toggle('dark:bg-cyan-500/20', isActive);
                button.classList.toggle('dark:text-cyan-100', isActive);
            });

            if (summaryCycleNode) {
                summaryCycleNode.textContent = state.billingCycle.toUpperCase();
            }
        }

        function refreshQuote() {
            var params = new URLSearchParams({
                plan: state.plan,
                billing_cycle: state.billingCycle,
                coupon_code: state.couponCode,
            });

            fetch(previewUrl + '?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw data;
                    }

                    return data;
                });
            }).then(function (payload) {
                var quote = payload.quote || {};
                var plan = quote.plan || {};

                state.pending = !!payload.pending;
                setPending(state.pending ? 'An upgrade request is already pending central approval.' : '');
                setError('');

                baseNode.textContent = formatCurrency(quote.base_price);
                discountNode.textContent = formatCurrency(quote.discount_amount);
                finalNode.textContent = formatCurrency(quote.final_price);
                if (summaryPriceNode) {
                    summaryPriceNode.textContent = formatCurrency(quote.final_price);
                }

                if (state.couponCode !== '' && quote.coupon) {
                    var discountType = String(quote.coupon.discount_type || '').toLowerCase();
                    var discountValue = Number(quote.coupon.discount_value || 0);
                    var discountDescription = discountType === 'percent'
                        ? discountValue.toFixed(2) + '% off'
                        : formatCurrency(discountValue) + ' off';
                    setCouponSuccess('Promo code applied: ' + quote.coupon.code + ' (' + discountDescription + ')');
                } else if (state.couponCode === '') {
                    setCouponSuccess('');
                }

                if (state.billingCycle === 'yearly' && Number(plan.yearly_discount_percent || 0) > 0) {
                    savingsNode.textContent = 'Yearly savings: ' + Number(plan.yearly_discount_percent).toFixed(2) + '%';
                } else {
                    savingsNode.textContent = '';
                }
            }).catch(function (errorPayload) {
                var message = errorPayload?.errors?.coupon_code?.[0] || errorPayload?.message || 'Unable to compute pricing right now.';
                setError(message);
                setCouponSuccess('');
            });
        }

        modal.querySelectorAll('[data-upgrade-trigger]').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal();
            });
        });

        document.querySelectorAll('[data-upgrade-trigger]').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal();
            });
        });

        modal.querySelectorAll('[data-upgrade-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal();
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        modal.querySelectorAll('[data-cycle-option]').forEach(function (button) {
            button.addEventListener('click', function () {
                state.billingCycle = button.getAttribute('data-cycle-option') || 'monthly';
                window.__ismsSubscriptionBillingCycle = state.billingCycle;
                syncCycleButtons();
                refreshQuote();
            });
        });

        modal.querySelector('[data-apply-coupon]')?.addEventListener('click', function () {
            state.couponCode = couponInput?.value?.trim() || '';
            refreshQuote();
        });

        couponInput?.addEventListener('input', function () {
            state.couponCode = couponInput.value.trim();

            if (couponValidationTimer) {
                clearTimeout(couponValidationTimer);
            }

            couponValidationTimer = setTimeout(function () {
                refreshQuote();
            }, 350);
        });

        submitButton?.addEventListener('click', function () {
            if (state.pending) {
                return;
            }

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    requested_plan: state.plan,
                    billing_cycle: state.billingCycle,
                    coupon_code: state.couponCode,
                }),
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw data;
                    }

                    return data;
                });
            }).then(function () {
                state.pending = true;
                setPending('Upgrade request submitted and now pending central approval.');
                setError('');
                setCouponSuccess('');
                window.dispatchEvent(new CustomEvent('isms:subscription-upgrade-submitted'));
                closeModal();
            }).catch(function (errorPayload) {
                var message = errorPayload?.errors?.request?.[0]
                    || errorPayload?.errors?.requested_plan?.[0]
                    || errorPayload?.errors?.role?.[0]
                    || errorPayload?.message
                    || 'Unable to submit upgrade request.';
                setError(message);
            });
        });

        syncCycleButtons();

        if (window.__ismsOpenUpgradeModalOnLoad) {
            openModal();
        }
    })();
</script>
