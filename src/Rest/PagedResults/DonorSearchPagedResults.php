<?php

namespace OpenPublicMedia\RoiSolutions\Rest\PagedResults;

use ArrayIterator;
use OpenPublicMedia\RoiSolutions\Rest\Resource\Donor;

final class DonorSearchPagedResults extends PagedResultsBase
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

    /**
     * @return \ArrayIterator<int, Donor>
     */
    public function getIterator(): ArrayIterator
    {
        return parent::getIterator();
    }

    /**
     * @return array<int, Donor>
     */
    public function getItems(): array
    {
        return parent::getItems();
    }
}
