<?php

namespace OpenPublicMedia\RoiSolutions\Rest\PagedResults;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use OpenPublicMedia\RoiSolutions\Rest\Client;
use OpenPublicMedia\RoiSolutions\Rest\Traits\HasLinks;

/**
 * @implements IteratorAggregate<int, mixed>
 */
abstract class PagedResultsBase implements IteratorAggregate, Countable, PagedResultsInterface
{
    use HasLinks;

    protected int $page;
    protected int $limit;
    protected int $totalPages;
    protected int $totalRecords;

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
    final public function __construct(
        protected readonly Client $client,
        protected readonly string $endpoint,
        protected array $query
    ) {
        $this->update();
    }

    public function update(): void
    {
        $json = $this->client->get($this->endpoint, ['query' => $this->query]);
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

    public function count(): int
    {
        return count($this->items);
    }

  /**
   * @return \ArrayIterator<int, mixed>
   */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
