<?php

return [
    'bypass_payment_for_publish' => (bool) env('BYPASS_PAYMENT_FOR_PUBLISH', false),
    'flower_price_cents' => (int) env('MEMORIAL_FLOWER_PRICE_CENTS', 500),
];
