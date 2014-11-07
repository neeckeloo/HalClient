<?php
namespace HalClient;

use HalClient\Exception;
use Zend\Http\Client as HttpClient;
use Zend\Uri\Uri;

class Client
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param  string $url
     * @return self
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = (string) $url;
        return $this;
    }

    /**
     * @param  string|Uri $uri
     * @param  bool $encapsulate
     * @return Resource
     */
    public function get($uri, $encapsulate = true)
    {
        $httpClient = $this->getHttpClient();
        $httpClient->setMethod('GET');

        $response = $this->doRequest($uri);
        $resource = $this->buildResourceFromResponse($response);

        if ($encapsulate && $resource->hasPagination()) {
            $resource = new ResourcePaginator($resource);
        }

        return $resource;
    }

    /**
     * @param  \Zend\Http\Response $response
     * @return Resource
     * @throws Exception\RuntimeException
     */
    protected function buildResourceFromResponse($response)
    {
        $json = $response->getContent();

        $data = @json_decode($json, true);
        if (null === $data) {
            throw new Exception\RuntimeException('Invalid JSON Format');
        }

        if (isset($data['title'], $data['status'], $data['detail'])) {
            throw new Exception\RuntimeException($data['detail'], $data['status']);
        }

        return Resource::create($this, $data);
    }

    /**
     * @param  string|Uri $uri
     * @return \Zend\Http\Response
     */
    protected function doRequest($uri)
    {
        if (!$uri instanceof Uri) {
            $uri = new Uri($uri);
        }

        if ($uri->isValidRelative()) {
            $uri = $this->baseUrl . $uri->toString();
        } else {
            $uri = $uri->toString();
        }

        $httpClient = $this->getHttpClient();
        $httpClient->setUri($uri);

        return $httpClient->send();
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient()
    {
        if (!$this->httpClient) {
            $this->setHttpClient(new HttpClient());
        }

        return $this->httpClient;
    }

    /**
     * @param  HttpClient $client
     * @return self
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;

        $this->httpClient->setHeaders([
            'Accept' => 'application/json',
        ]);

        return $this;
    }
}