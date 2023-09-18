<?php

namespace OpenPublicMedia\RoiSolutions\Rest\Traits;

trait HasRoiFamilyId
{
    public function getRoiFamilyId(): string
    {
        return $this->roiFamilyId;
    }
}
