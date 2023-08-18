<?php

namespace OpenPublicMedia\RoiSolutions\Rest\Traits;

trait HasLinks
{

    /**
     * @return array<string, string>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function getLink(string $rel): ?string
    {
        return $this->links[$rel] ?? null;
    }

    /**
     * @param array<int, object> $json
     *   Array of link objects with `rel` and `href` properties.
     *
     * @return array<string, string>
     *   Array of link HREFs keyed by link REL.
     */
    public static function fromLinksJson(array $json): array
    {
        $links = [];
        foreach ($json as $link) {
            if (!property_exists($link, 'rel') || !property_exists($link, 'href')) {
                throw new \RuntimeException("Link objects must contain `rel` and `href` properties.");
            }
            $links[$link->rel] = $link->href;
        }
        return $links;
    }
}
