<?php

return [
    'fixed_price_cents' => (int) env('MEMORIAL_FIXED_PRICE_CENTS', 69900),
    'bypass_payment_for_publish' => (bool) env('BYPASS_PAYMENT_FOR_PUBLISH', false),
];
