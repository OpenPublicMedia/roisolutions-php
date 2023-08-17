<?php

namespace OpenPublicMedia\RoiSolutions\Resource;

use DateTime;

final class Donor
{
    /**
     * @param array<string, string> $links
     */
    public function __construct(
        protected readonly string $roiFamilyId,
        protected readonly string $roiId,
        protected readonly string $originationVendor,
        protected readonly string $accountStatus,
        protected readonly bool $doNotContact,
        protected readonly DateTime $accountAddedDate,
        protected readonly DateTime $modifiedDate,
        protected readonly array $links = [],
        protected readonly ?string $nameFirst = null,
        protected readonly ?string $nameLast = null,
        protected readonly ?string $nameMiddle = null,
        protected readonly ?string $namePrefix = null,
        protected readonly ?string $nameSuffix = null,
        protected readonly ?string $nameFull = null,
        protected readonly ?string $salutation = null,
        protected readonly ?string $addressLine = null
    ) {
    }

    public static function fromJson(object $json): Donor
    {
        $links = [];
        if (property_exists($json, 'links')) {
            foreach ($json->links as $link) {
                $links[$link->rel] = $link->href;
            }
        }

        return new Donor(
            $json->roi_family_id,
            $json->roi_id,
            $json->origination_vendor,
            $json->account_status,
            (bool) $json->do_not_contact,
            (new DateTime($json->account_added_date)),
            (new DateTime($json->modified_date)),
            $links,
            $json->name_first ?? null,
            $json->name_last ?? null,
            $json->name_middle ?? null,
            $json->name_prefix ?? null,
            $json->name_suffix ?? null,
            $json->name_full ?? null,
            $json->salutation ?? null,
            $json->address_line ?? null
        );
    }

    public function getRoiFamilyId(): string
    {
        return $this->roiFamilyId;
    }

    public function getRoiId(): string
    {
        return $this->roiId;
    }

    public function getOriginationVendor(): string
    {
        return $this->originationVendor;
    }

    public function getAccountStatus(): string
    {
        return $this->accountStatus;
    }

    public function getDoNotContact(): bool
    {
        return $this->doNotContact;
    }

    public function getAccountAddedDate(): DateTime
    {
        return $this->accountAddedDate;
    }

    public function getModifiedDate(): DateTime
    {
        return $this->modifiedDate;
    }

    /**
     * @return array<string, string>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function getLink(string $rel): ?string
    {
        return $this->links[$rel] ?? null;
    }

    public function getNameFirst(): ?string
    {
        return $this->nameFirst;
    }

    public function getNameLast(): ?string
    {
        return $this->nameLast;
    }

    public function getNameMiddle(): ?string
    {
        return $this->nameMiddle;
    }

    public function getNamePrefix(): ?string
    {
        return $this->namePrefix;
    }

    public function getNameSuffix(): ?string
    {
        return $this->nameSuffix;
    }

    public function getNameFull(): ?string
    {
        return $this->nameFull;
    }

    public function getSalutation(): ?string
    {
        return $this->salutation;
    }

    public function getAddressLine(): ?string
    {
        return $this->addressLine;
    }
}
