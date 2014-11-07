<?php
namespace HalClient;

class Endpoint
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param  string $url
     * @return self
     */
    public function setBaseUrl($url)
    {
        $this->client->setBaseUrl($url);
        return $this;
    }

    /**
     * @return Resource
     */
    public function countries()
    {
        return $this->client->get('/countries');
    }
}