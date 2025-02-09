<?php

namespace Ksolutions\PayumPaynow\Enum;

enum Currencies: string
{
    //  [PLN, EUR, USD, GBP, CZK]
    case PLN = 'PLN';
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';
    case CZK = 'CZK';

    public static function isAllowed(string $currency): bool
    {
        return null !== self::tryFrom($currency);
    }
}