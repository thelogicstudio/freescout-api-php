<?php

declare(strict_types=1);

namespace FreeScout\Api\Entity;

use FreeScout\Api\Exception\InvalidArgumentException;
use FreeScout\Api\Http\Hal\HalLink;
use FreeScout\Api\Http\Hal\HalLinks;
use FreeScout\Api\Http\Hal\HalPageMetadata;

class PagedCollection extends Collection
{
    /**
     * The link name for the templated page URI.
     */
    public const REL_PAGE = 'page';

    /**
     * The variable name for the page number in the templated URI.
     */
    public const PAGE_VARIABLE = 'page';

    /**
     * @var HalPageMetadata
     */
    private $pageMetadata;

    /**
     * @var HalLinks
     */
    private $links;

    /**
     * @var callable
     */
    private $loader;

	private $uri;

    public function __construct(array $items, HalPageMetadata $pageMetadata, HalLinks $links, callable $loader, ?string $uri)
    {
        parent::__construct($items);

        $this->pageMetadata = $pageMetadata;
        $this->links = $links;
        $this->loader = $loader;
		$this->uri = $uri;
    }

    public function getPageNumber(): int
    {
        return $this->pageMetadata->getPageNumber();
    }

    public function getPageSize(): int
    {
        return $this->pageMetadata->getPageSize();
    }

    public function getPageElementCount(): int
    {
        return count($this);
    }

    public function getTotalElementCount(): int
    {
        return $this->pageMetadata->getTotalElementCount();
    }

    public function getTotalPageCount(): int
    {
        return $this->pageMetadata->getTotalPageCount();
    }

    public function getPage(int $number): self
    {
		$query = parse_url($this->uri);
		parse_str($query['query'] ?? '', $queryParams);
		$queryParams = array_merge($queryParams, [
			self::PAGE_VARIABLE => $number,
		]);
        return $this->loadPage($query['path'].'?'.http_build_query($queryParams));
    }

    public function getNextPage(): self
    {
		if($this->pageMetadata->getPageNumber() < $this->pageMetadata->getTotalPageCount()) {
			return $this->getPage($this->pageMetadata->getPageNumber() + 1);
		}
        throw new InvalidArgumentException('Next page does not exist');
    }

    public function getPreviousPage(): self
    {
		if($this->pageMetadata->getPageNumber() > 1) {
			return $this->getPage($this->pageMetadata->getPageNumber() - 1);
		}
		throw new InvalidArgumentException('Previous page does not exist');
    }

    public function getFirstPage(): self
    {
		return $this->getPage(1);
    }

    public function getLastPage(): self
    {
		return $this->getPage($this->pageMetadata->getTotalPageCount());
    }

    private function loadPage(string $uri): self
    {
        return call_user_func($this->loader, $uri);
    }
}
