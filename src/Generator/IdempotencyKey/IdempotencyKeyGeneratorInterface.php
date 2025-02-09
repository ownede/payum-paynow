<?php

namespace Ksolutions\PayumPaynow\Generator\IdempotencyKey;

interface IdempotencyKeyGeneratorInterface
{
    public function generate(string $prefix = ''): string;
}