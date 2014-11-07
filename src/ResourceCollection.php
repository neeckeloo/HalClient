<?php
namespace HalClient;

use HalClient\Exception;

class ResourceCollection extends \ArrayIterator implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @param  int $key
     * @return Resource
     */
    protected function getResourceFromKey($key)
    {
        $resource = parent::offsetGet($key);
        if (null === $resource) {
            return null;
        }

        if (!$resource instanceof Resource) {
            $resource = Resource::create($this->client, $resource);
            parent::offsetSet($this->key(), $resource);
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->getResourceFromKey($this->key());
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getResourceFromKey($offset);
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
}