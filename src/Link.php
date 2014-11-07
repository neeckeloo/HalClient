<?php
namespace HalClient;

class Link
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $href;

    /**
     * @param string $name
     * @param string $href
     */
    public function __construct($name, $href)
    {
        $this->setName($name);
        $this->setHref($href);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param  string $href
     * @return self
     */
    public function setHref($href)
    {
        $this->href = (string) $href;
        return $this;
    }
}