<?php

namespace OpenPublicMedia\RoiSolutions\Rest\SearchResults;

interface SearchResultsInterface
{
    /**
     * Process search results items (e.g. to create specific instances).
     *
     * @param array<int, mixed> $items
     *
     * @return array<int, mixed>
     */
    public static function buildItems(array $items): array;
}
