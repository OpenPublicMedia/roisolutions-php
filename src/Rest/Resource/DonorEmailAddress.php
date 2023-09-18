<?php

namespace OpenPublicMedia\RoiSolutions\Rest\Resource;

use OpenPublicMedia\RoiSolutions\Rest\Traits\HasLinks;
use OpenPublicMedia\RoiSolutions\Rest\Traits\HasRoiFamilyId;
use OpenPublicMedia\RoiSolutions\Rest\Traits\HasRoiId;

final class DonorEmailAddress
{
    use HasLinks, HasRoiFamilyId, HasRoiId;

    /**
     * @param array<string, string> $links
     */
    public function __construct(
        protected readonly string $roiFamilyId,
        protected readonly string $roiId,
        protected readonly string $emailId,
        protected readonly string $emailAddress,
        protected readonly string $emailType,
        protected readonly string $contactStatus,
        protected readonly bool $emailBounced,
        protected readonly array $links,
    ) {
    }

    public static function fromJson(object $json): DonorEmailAddress
    {
        return new DonorEmailAddress(
            $json->roi_family_id,
            $json->roi_id,
            $json->email_id,
            $json->email_address,
            $json->email_type,
            $json->contact_status,
            $json->email_bounced === 'Y',
            self::fromLinksJson($json->links),
        );
    }

    public function getEmailId(): string
    {
        return $this->emailId;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getEmailType(): string
    {
        return $this->emailType;
    }

    public function getContactStatus(): string
    {
        return $this->contactStatus;
    }

    public function isEmailBounced(): bool
    {
        return $this->emailBounced;
    }
}
