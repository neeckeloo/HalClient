<?php
namespace HalClient;

use HalClient\Exception;

class ResourcePaginator implements \Iterator, \Countable, \ArrayAccess
{
    /**
     * @var int
     */
    protected $index = 0;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected $embeddedKey;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param  int|null $index
     * @return bool
     */
    protected function isMissingResources($index = null)
    {
        if ($this->count() == 0) {
            return false;
        }

        $currentItemCount = count($this->items);
        if (null === $index) {
            $index = $currentItemCount;
        }

        if (!empty($this->items) && $index < $currentItemCount) {
            return false;
        }

        return true;
    }

    /**
     * @param  int|null $index
     * @return void
     */
    protected function loadMissingResources($index = null)
    {
        $currentItemCount = count($this->items);
        if (empty($index) && $currentItemCount < $this->count()) {
            $index = $currentItemCount;
        }

        if ($currentItemCount == 0) {
            $this->loadCurrentResources();
            return;
        }

        while ($index >= $currentItemCount
            && $this->isMissingResources()
            && $this->resource->hasLink('next')
        ) {
            $this->resource->refresh('next');
            $this->loadCurrentResources();
        }
    }

    /**
     * @return void
     */
    protected function loadCurrentResources()
    {
        $resources = $this->resource->get($this->getEmbeddedKey());
        foreach ($resources as $resource) {
            $this->items[] = $resource;
        }
    }

    /**
     * @return string
     */
    protected function getEmbeddedKey()
    {
        if (!$this->embeddedKey) {
            $embedded = $this->resource->getEmbedded();
            $keys = array_keys($embedded);
            $this->embeddedKey = $keys[0];
        }

        return $this->embeddedKey;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->items[$this->index];
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if ($this->isMissingResources()) {
            $this->loadMissingResources();
        }

        return isset($this->items[$this->index]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        if ($this->isMissingResources($offset)) {
            $this->loadMissingResources($offset);
        }

        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if ($this->isMissingResources($offset)) {
            $this->loadMissingResources($offset);
        }

        return $this->items[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception\RuntimeException('Operation not allowed');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new Exception\RuntimeException('Operation not allowed');
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->resource->get('total_items');
    }
}