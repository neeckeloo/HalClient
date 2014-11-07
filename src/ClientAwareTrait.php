<?php
namespace HalClient;

trait ClientAwareTrait
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param  Client $client
     * @return self
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }
}
