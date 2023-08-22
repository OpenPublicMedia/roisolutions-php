<?php

namespace OpenPublicMedia\RoiSolutions\Rest\SearchResults;

use OpenPublicMedia\RoiSolutions\Rest\Resource\Donor;

/**
 * @method \ArrayIterator<int, Donor> getIterator()
 */
class DonorSearchResults extends SearchResultsBase
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
