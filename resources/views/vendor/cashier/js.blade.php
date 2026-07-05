<?php

$seller = array_filter([
    'pwAuth' => (int) config('cashier.retain_key'),
]);

if (config('cashier.client_side_token')) {
    $seller['token'] = config('cashier.client_side_token');
} elseif (config('cashier.seller_id')) {
    $seller['seller'] = (int) config('cashier.seller_id');
}

$canInitialize = isset($seller['token']) || isset($seller['seller']);

if ($canInitialize && isset($seller['pwAuth']) && Auth::check() && $customer = Auth::user()->customer) {
    $seller['pwCustomer'] = ['id' => $customer->paddle_id];
}

$nonce = $nonce ?? '';
?>

@if ($canInitialize)
<script @if ($nonce) nonce="{{ $nonce }}" @endif>
(function () {
    const seller = @json($seller);
    const sandbox = @json((bool) config('cashier.sandbox'));

    function bootPaddle() {
        if (typeof Paddle === 'undefined') {
            return false;
        }

        if (sandbox) {
            Paddle.Environment.set('sandbox');
        }

        Paddle.Initialize(seller);
        window.__paddleReady = true;
        window.dispatchEvent(new CustomEvent('paddle:ready'));

        return true;
    }

    if (bootPaddle()) {
        return;
    }

    let script = document.querySelector('script[data-paddle-js]');

    if (! script) {
        script = document.createElement('script');
        script.src = 'https://cdn.paddle.com/paddle/v2/paddle.js';
        script.dataset.paddleJs = '1';
        script.async = true;
        document.head.appendChild(script);
    }

    script.addEventListener('load', bootPaddle, { once: true });
})();
</script>
@endif
