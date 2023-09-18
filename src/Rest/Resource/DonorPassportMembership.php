<?php

namespace OpenPublicMedia\RoiSolutions\Rest\Resource;

use OpenPublicMedia\RoiSolutions\Rest\Traits\HasLinks;
use OpenPublicMedia\RoiSolutions\Rest\Traits\HasOriginationVendor;
use OpenPublicMedia\RoiSolutions\Rest\Traits\HasRoiFamilyId;

final class DonorPassportMembership
{
    use HasLinks, HasOriginationVendor, HasRoiFamilyId;

    /**
     * @param array<string, string> $links
     */
    public function __construct(
        protected readonly string $roiFamilyId,
        protected readonly string $membershipId,
        protected readonly string $originationVendor,
        protected readonly array $links,
    ) {
    }

    public static function fromJson(object $json): DonorPassportMembership
    {
        return new DonorPassportMembership(
            $json->roi_family_id,
            $json->membership_id,
            $json->origination_vendor,
            self::fromLinksJson($json->links),
        );
    }

    public function getMembershipId(): string
    {
        return $this->membershipId;
    }
}
