<?php

namespace TheAngels\PayerMax;

/**
 * Enum untuk lingkungan PayerMax.
 * Menyediakan URL dasar untuk setiap lingkungan.
 */
enum PayerMaxEnvironment: string
{
    case UAT = 'https://pay-gate-uat.payermax.com';
    case PRODUCTION = 'https://pay-gate.payermax.com';
}