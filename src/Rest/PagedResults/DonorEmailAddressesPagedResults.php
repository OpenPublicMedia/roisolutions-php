<?php

namespace OpenPublicMedia\RoiSolutions\Rest\PagedResults;

use ArrayIterator;
use OpenPublicMedia\RoiSolutions\Rest\Resource\DonorEmailAddress;

final class DonorEmailAddressesPagedResults extends PagedResultsBase
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

    /**
     * @return \ArrayIterator<int, DonorEmailAddress>
     */
    public function getIterator(): ArrayIterator
    {
        return parent::getIterator();
    }

    /**
     * @return array<int, DonorEmailAddress>
     */
    public function getItems(): array
    {
        return parent::getItems();
    }
}
