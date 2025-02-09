<?php

namespace Ksolutions\PayumPaynow\Generator\IdempotencyKey;

class UniqIdIdempotencyKeyGenerator implements IdempotencyKeyGeneratorInterface
{
    public function generate(string $prefix = ''): string
    {
        $key = uniqid($prefix);

        return substr($key, 0, 45);
    }
}