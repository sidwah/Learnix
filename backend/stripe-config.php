<?php
// Stripe Configuration
define('STRIPE_SECRET_KEY', 'sk_test_51R5Ur1K3vzxvGcdqDyhBrd3gCi0j7JX7yhxdJwaid6WmBBGhJ5ESp15DcsthagilA85Cd0O43nLWDdEiB3vdXrFJ00JKkKKYT3'); // Add your full secret key
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51R5Ur1K3vzxvGcdqb05o4mdmWH9rvF7EZR2nZv60uDBk672NElIuVIjw0nqmhcGuBUlW9abCRuph6CGXtu9BrHLJ00oWxXlvj4'); // Add your full publishable key
define('STRIPE_CURRENCY', 'usd');

// Load Stripe PHP SDK (manual method)
require_once __DIR__ . '/../vendor/stripe-php-master/init.php';

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);