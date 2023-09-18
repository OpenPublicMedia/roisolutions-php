<?php

namespace OpenPublicMedia\RoiSolutions\Rest\Traits;

trait HasRoiId
{
    public function getRoiId(): string
    {
        return $this->roiId;
    }
}
