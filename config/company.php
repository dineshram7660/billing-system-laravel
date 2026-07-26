<?php

/**
 * Bhavani Engineering's own fixed business details, hardcoded into the
 * legacy bill_print.php template rather than stored per-bill. Kept here
 * as config instead of scattered across print views.
 */
return [
    'name' => 'Bhavani Engineering',
    'gstin' => '24AXDPM6592R1ZN',
    'pan' => 'AXDPM6592R',
    'bank_name' => 'Punjab National Bank',
    'account_no' => '0475050015570',
    'vendor_code' => '2100009680',
    'ifsc_code' => 'PUNB0047520',
    'msme_certificate' => 'UDYAM-GJ-30-0000031',

    // The legacy quotation print template signs off as a different trade
    // name than the invoice/estimate templates — see quotation_print.php.
    'quotation_entity_name' => 'Bhavani Fabricators',

    'cgst_rate' => 9,
    'sgst_rate' => 9,
];
