<?php

namespace OpenPublicMedia\RoiSolutions\Rest\PagedResults;

use OpenPublicMedia\RoiSolutions\Rest\Resource\Donor;

/**
 * @method \ArrayIterator<int, Donor> getIterator()
 */
class DonorSearchPagedResults extends PagedResultsBase
{

    /**
     * @param array<int, object> $items
     * @return array<int, Donor>
     */
    public static function buildItems(array $items): array
    {
        $donors = [];
        foreach ($items as $item) {
            $donors[] = Donor::fromJson($item);
        }
        return $donors;
    }
}
