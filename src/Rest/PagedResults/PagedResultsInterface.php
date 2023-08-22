<?php

namespace OpenPublicMedia\RoiSolutions\Rest\PagedResults;

interface PagedResultsInterface
{
    /**
     * Process paged results items (e.g. to create specific instances).
     *
     * @param array<int, mixed> $items
     *
     * @return array<int, mixed>
     */
    public static function buildItems(array $items): array;
}
