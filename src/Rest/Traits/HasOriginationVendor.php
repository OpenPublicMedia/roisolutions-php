<?php

namespace OpenPublicMedia\RoiSolutions\Rest\Traits;

trait HasOriginationVendor
{
    public function getOriginationVendor(): string
    {
        return $this->originationVendor;
    }
}
