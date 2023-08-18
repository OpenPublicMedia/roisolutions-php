<?php

namespace OpenPublicMedia\RoiSolutions\Rest\SearchResults;

use OpenPublicMedia\RoiSolutions\Rest\Client;
use OpenPublicMedia\RoiSolutions\Rest\Traits\HasLinks;

abstract class SearchResultsBase implements SearchResultsInterface
{
    use HasLinks;

    protected int $page;
    protected int $limit;
    protected int $totalPages;
    protected int $totalRecords;
    protected int $lastItemIndex = -1;

    /**
     * @var array<string, string>
     */
    protected array $links = [];

    /**
     * @var array<int, mixed>
     */
    protected array $items = [];

    /**
     * @param array<string, string|int> $query
     */
    final public function __construct(protected readonly Client $client, protected array $query)
    {
        $this->update();
    }

    public function update(): void
    {
        $json = $this->client->get('donors', ['query' => $this->query]);
        $this->page = $json->page;
        $this->limit = $json->limit;
        $this->totalPages = $json->total_pages;
        $this->totalRecords = $json->total_records;
        $this->links = self::fromLinksJson($json->links);
        $this->items = $this->buildItems($json->items);
    }

    public function hasNextPage(): bool
    {
        return !empty($this->getLink('next'));
    }

    public function getNextPage(): void
    {
        if ($this->hasNextPage()) {
            $this->query['page']++;
            $this->update();
        }
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotalRecords(): int
    {
        return $this->totalRecords;
    }

    /**
     * @return array<int, mixed>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getItem(int $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    public function getNextItem(): mixed
    {
        $this->lastItemIndex++;
        return $this->getItem($this->lastItemIndex);
    }

    public function getLastItemIndex(): int
    {
        return max($this->lastItemIndex, 0);
    }
}
