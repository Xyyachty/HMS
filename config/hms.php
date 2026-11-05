<?php

return [
    /*
     * Service charge, in percent, on every charge of a stay after the Senior
     * Citizen / PWD discounts. Each stay keeps the rate it was booked at, so a
     * change here applies to new bookings only. 0 turns it off.
     */
    'service_charge_rate' => (float) env('HMS_SERVICE_CHARGE_RATE', 3),
];
