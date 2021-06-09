<?php

namespace Adldap\Query;

use Countable;
use ArrayIterator;
use IteratorAggregate;

class Paginator implements Countable, IteratorAggregate
{
    /**
     * The complete results array.
     *
     * @var array
     */
    protected $results = [];

    /**
     * The total amount of pages.
     *
     * @var int
     */
    protected $pages;

    /**
     * The amount of entries per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * The current page number.
     *
     * @var int
     */
    protected $currentPage;

    /**
     * The current entry offset number.
     *
     * @var int
     */
    protected $currentOffset;

    /**
     * Constructor.
     *
     * @param array $results
     * @param int   $perPage
     * @param int   $currentPage
     * @param int   $pages
     */
    public function __construct(array $results = [], $perPage = 50, $currentPage = 0, $pages = 0)
    {
        $this->setResults($results)
            ->setPerPage($perPage)
            ->setCurrentPage($currentPage)
            ->setPages($pages)
            ->setCurrentOffset(($this->getCurrentPage() * $this->getPerPage()));
    }

    /**
     * Get an iterator for the entries.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $entries = array_slice($this->getResults(), $this->getCurrentOffset(), $this->getPerPage(), true);

        return new ArrayIterator($entries);
    }

    /**
     * Returns the complete results array.
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Returns the total amount of pages
     * in a paginated result.
     *
     * @return int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Returns the total amount of entries
     * allowed per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Returns the current page number.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Returns the current offset number.
     *
     * @return int
     */
    public function getCurrentOffset()
    {
        return $this->currentOffset;
    }

    /**
     * Returns the total amount of results.
     *
     * @return int
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * Sets the results array property.
     *
     * @param array $results
     *
     * @return Paginator
     */
    protected function setResults(array $results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * Sets the total number of pages.
     *
     * @param int $pages
     *
     * @return Paginator
     */
    protected function setPages($pages = 0)
    {
        $this->pages = (int) $pages;

        return $this;
    }

    /**
     * Sets the number of entries per page.
     *
     * @param int $perPage
     *
     * @return Paginator
     */
    protected function setPerPage($perPage = 50)
    {
        $this->perPage = (int) $perPage;

        return $this;
    }

    /**
     * Sets the current page number.
     *
     * @param int $currentPage
     *
     * @return Paginator
     */
    protected function setCurrentPage($currentPage = 0)
    {
        $this->currentPage = (int) $currentPage;

        return $this;
    }

    /**
     * Sets the current offset number.
     *
     * @param int $offset
     *
     * @return Paginator
     */
    protected function setCurrentOffset($offset = 0)
    {
        $this->currentOffset = (int) $offset;

        return $this;
    }
}
