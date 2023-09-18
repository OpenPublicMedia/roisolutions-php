<?php

namespace OpenPublicMedia\RoiSolutions\Rest\PagedResults;

use ArrayIterator;
use OpenPublicMedia\RoiSolutions\Rest\Resource\DonorPassportMembership;

final class DonorPassportMembershipsPagedResults extends PagedResultsBase
{

    /**
     * @param array<int, object> $items
     * @return array<int, DonorPassportMembership>
     */
    public static function buildItems(array $items): array
    {
        $donors = [];
        foreach ($items as $item) {
            $donors[] = DonorPassportMembership::fromJson($item);
        }
        return $donors;
    }

    /**
     * @return \ArrayIterator<int, DonorPassportMembership>
     */
    public function getIterator(): ArrayIterator
    {
        return parent::getIterator();
    }

    /**
     * @return array<int, DonorPassportMembership>
     */
    public function getItems(): array
    {
        return parent::getItems();
    }
}
