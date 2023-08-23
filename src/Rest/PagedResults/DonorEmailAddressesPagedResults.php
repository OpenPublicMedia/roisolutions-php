<?php

namespace OpenPublicMedia\RoiSolutions\Rest\PagedResults;

use OpenPublicMedia\RoiSolutions\Rest\Resource\DonorEmailAddress;

/**
 * @method \ArrayIterator<int, DonorEmailAddress> getIterator()
 */
class DonorEmailAddressesPagedResults extends PagedResultsBase
{

    /**
     * @param array<int, object> $items
     * @return array<int, DonorEmailAddress>
     */
    public static function buildItems(array $items): array
    {
        $donors = [];
        foreach ($items as $item) {
            $donors[] = DonorEmailAddress::fromJson($item);
        }
        return $donors;
    }
}
