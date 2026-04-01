@php
    $canSubmitUpgradeRequest = auth()->user()?->role === 'university_admin';
@endphp

<div id="tenant-upgrade-modal" class="fixed inset-0 z-[70] hidden" aria-hidden="true">
    <button type="button" data-upgrade-close class="absolute inset-0 bg-slate-950/70"></button>
    <div class="relative mx-auto mt-16 w-full max-w-lg rounded-2xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-amber-200">Upgrade Request</p>
                <h3 class="mt-1 text-xl font-semibold text-slate-100">Pro Subscription</h3>
            </div>
            <button type="button" data-upgrade-close class="rounded-lg border border-white/15 px-2 py-1 text-xs text-slate-300 hover:bg-white/10">Close</button>
        </div>

        <p class="mt-3 text-sm text-slate-300">Request central approval for Pro. Final billing is managed by the central business control module.</p>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <button type="button" data-cycle-option="monthly" class="upgrade-cycle-btn rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-100">Monthly</button>
            <button type="button" data-cycle-option="yearly" class="upgrade-cycle-btn rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-100">Yearly</button>
        </div>

        <div class="mt-4">
            <label for="upgrade-coupon" class="mb-2 block text-sm text-slate-300">Promo Code (optional)</label>
            <div class="flex gap-2">
                <input id="upgrade-coupon" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" placeholder="Enter coupon code" />
                <button type="button" data-apply-coupon class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-3 py-2 text-sm text-cyan-100 hover:bg-cyan-500/30">Apply</button>
            </div>
            <p class="mt-1 text-xs text-rose-300" data-upgrade-error></p>
        </div>

        <div class="mt-4 rounded-xl border border-white/10 bg-slate-950/60 p-4 text-sm">
            <div class="flex items-center justify-between text-slate-300">
                <span>Base price</span>
                <span data-price-base>$0.00</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-slate-300">
                <span>Discount</span>
                <span data-price-discount>$0.00</span>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-white/10 pt-3 text-slate-100">
                <span class="font-medium">Final price</span>
                <span class="text-lg font-semibold" data-price-final>$0.00</span>
            </div>
            <p class="mt-2 text-xs text-slate-400" data-yearly-savings></p>
            <p class="mt-2 text-xs text-amber-200" data-upgrade-pending></p>
        </div>

        @if ($canSubmitUpgradeRequest)
            <button type="button" data-submit-upgrade class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-emerald-300/30 bg-emerald-500/20 px-4 py-2.5 text-sm font-medium text-emerald-100 hover:bg-emerald-500/30">
                Submit Upgrade Request
            </button>
        @else
            <p class="mt-4 text-xs text-slate-400">Only university admins can submit upgrade requests.</p>
        @endif
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
            billingCycle: 'monthly',
            couponCode: '',
            pending: false,
        };

        var errorNode = modal.querySelector('[data-upgrade-error]');
        var pendingNode = modal.querySelector('[data-upgrade-pending]');
        var baseNode = modal.querySelector('[data-price-base]');
        var discountNode = modal.querySelector('[data-price-discount]');
        var finalNode = modal.querySelector('[data-price-final]');
        var savingsNode = modal.querySelector('[data-yearly-savings]');
        var submitButton = modal.querySelector('[data-submit-upgrade]');
        var couponInput = modal.querySelector('#upgrade-coupon');

        function formatCurrency(value) {
            var amount = Number(value || 0);
            return '$' + amount.toFixed(2);
        }

        function setError(message) {
            if (errorNode) {
                errorNode.textContent = message || '';
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
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            refreshQuote();
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        }

        function syncCycleButtons() {
            modal.querySelectorAll('.upgrade-cycle-btn').forEach(function (button) {
                var isActive = button.getAttribute('data-cycle-option') === state.billingCycle;
                button.classList.toggle('border-cyan-300/40', isActive);
                button.classList.toggle('bg-cyan-500/20', isActive);
            });
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

                if (state.billingCycle === 'yearly' && Number(plan.yearly_discount_percent || 0) > 0) {
                    savingsNode.textContent = 'Yearly savings: ' + Number(plan.yearly_discount_percent).toFixed(2) + '%';
                } else {
                    savingsNode.textContent = '';
                }
            }).catch(function (errorPayload) {
                var message = errorPayload?.errors?.coupon_code?.[0] || errorPayload?.message || 'Unable to compute pricing right now.';
                setError(message);
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
                syncCycleButtons();
                refreshQuote();
            });
        });

        modal.querySelector('[data-apply-coupon]')?.addEventListener('click', function () {
            state.couponCode = couponInput?.value?.trim() || '';
            refreshQuote();
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
