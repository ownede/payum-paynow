<?php
namespace Ksolutions\PayumPaynow;

use Paynow\Client;

class Api extends Client
{
    protected array $additionalOptions = [];

    public function getAdditionalOptions(): array
    {
        return $this->additionalOptions;
    }

    public function setAdditionalOptions(array $additionalOptions): void
    {
        $this->additionalOptions = $additionalOptions;
    }
}
