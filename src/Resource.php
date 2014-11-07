<?php
namespace HalClient;

use HalClient\Exception;

class Resource implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var array
     */
    protected $embedded = [];

    /**
     * @param array $properties
     * @param array $links
     * @param array $embedded
     */
    public function __construct($properties = [], $links = [], $embedded = [])
    {
        $this->properties = $properties;
        $this->embedded   = $embedded;

        $this->buildLinks($links);
    }

    /**
     * @param array $links
     */
    protected function buildLinks(array $links)
    {
        foreach ($links as $name => $data) {
            if (!is_string($name) || empty($data['href'])) {
                continue;
            }

            $this->links[$name] = new Link($name, $data['href']);
        }
    }

    /**
     * @param Client $client
     * @param array $data
     */
    public static function create(Client $client, array $data)
    {
        $links    = isset($data['_links']) ? $data['_links'] : [];
        $embedded = isset($data['_embedded']) ? $data['_embedded'] : [];

        unset($data['_links'], $data['_embedded']);

        $resource = new self($data, $links, $embedded);
        $resource->setClient($client);

        return $resource;
    }

    /**
     * @param  string $name
     * @throws \RuntimeException
     */
    public function refresh($name = 'self')
    {
        $link = $this->getLink($name);
        if (!$link) {
            throw new Exception\RuntimeException(sprintf('Link "%s" is invalid', $name));
        }

        $resource = $this->getResourceFromLink($link);

        $this->properties = $resource->getProperties();
        $this->embedded   = $resource->getEmbedded();
        $this->links      = $resource->getLinks();
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return mixed
     */
    public function getProperty($name)
    {
        if (!array_key_exists($name, $this->properties)) {
            return null;
        }

        return $this->properties[$name];
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * @return array
     */
    public function getEmbedded()
    {
        return $this->embedded;
    }

    /**
     * @param  string $name
     * @return self
     */
    protected function getEmbeddedValue($name)
    {
        if (!$this->hasEmbeddedValue($name)) {
            return null;
        }

        $embeddedValue = $this->embedded[$name];
        if (!$embeddedValue instanceof Resource && is_array($embeddedValue)) {
            if (is_int(key($embeddedValue)) || empty($embeddedValue)) {
                $embeddedValue = new ResourceCollection($embeddedValue);
            } else {
                $embeddedValue = self::create($embeddedValue);
            }

            if ($embeddedValue instanceof ClientAwareInterface) {
                $embeddedValue->setClient($this->client);
            }

            $this->embedded[$name] = $embeddedValue;
        }

        return $this->embedded[$name];
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function hasEmbeddedValue($name)
    {
        return isset($this->embedded[$name]);
    }

    /**
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return Link|null
     */
    public function getLink($name)
    {
        if (!array_key_exists($name, $this->links)) {
            return null;
        }

        return $this->links[$name];
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function hasLink($name)
    {
        return isset($this->links[$name]);
    }

    /**
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        $property = $this->getProperty($name);
        if ($property) {
            return $property;
        }

        $link = $this->getLink($name);
        if ($link) {
            return $this->getResourceFromLink($link);
        }

        return $this->getEmbeddedValue($name);
    }

    /**
     * @param  Link $link
     * @return Resource
     */
    protected function getResourceFromLink(Link $link)
    {
        $name = $link->getName();
        if (!$this->hasEmbeddedValue($name)) {
            $this->embedded[$name] = $this->client->get($link->getHref(), false);
        }

        return $this->embedded[$name];
    }

    /**
     * @return bool
     */
    public function hasPagination()
    {
        if (!$this->hasLink('first') || !$this->hasLink('last')) {
            return false;
        }

        if (!$this->hasProperty('page_count')
            || !$this->hasProperty('page_size')
            || !$this->hasProperty('total_items')
        ) {
            return false;
        }

        return true;
    }
}