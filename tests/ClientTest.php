<?php
namespace HalClientTest;

use HalClient\Client;
use HalClient\Resource;
use HalClient\ResourceCollection;
use HalClient\ResourcePaginator;
use PHPUnit_Framework_TestCase;
use Zend\Http\Response as HttpResponse;

class ClientTest extends PHPUnit_Framework_TestCase
{
    public function testGetPaginatedResources()
    {
        $client = new Client();

        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->setMethods(['send'])
            ->getMock();
        $client->setHttpClient($httpClient);

        for ($i = 0; $i < 3; $i++) {
            $filePath = realpath(__DIR__ . '/assets/pagination/page' . ($i + 1) . '.json');
            $json = file_get_contents($filePath);

            $response = new HttpResponse();
            $response->setContent($json);

            $httpClient
                ->expects($this->at($i))
                ->method('send')
                ->will($this->returnValue($response));
        }

        $resourcePaginator = $client->get('/posts');

        $this->assertInstanceOf(ResourcePaginator::class, $resourcePaginator);

        for ($i = 0; $i < count($resourcePaginator); $i++) {
            $resource = $resourcePaginator[$i];
            $this->assertInstanceOf(Resource::class, $resource);

            $id = $i + 1;
            $this->assertEquals($id, $resource->get('id'));
            $this->assertEquals('Post ' . $id, $resource->get('title'));
        }

        $i = 1;
        foreach ($resourcePaginator as $resource) {
            $this->assertInstanceOf(Resource::class, $resource);
            $this->assertEquals($i, $resource->get('id'));
            $this->assertEquals('Post ' . $i, $resource->get('title'));
            $i++;
        }
    }

    public function testGetResourceWithEmbeddedCollection()
    {
        $client = new Client();

        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->setMethods(['send'])
            ->getMock();
        $client->setHttpClient($httpClient);

        $filePath = realpath(__DIR__ . '/assets/item.json');
        $json = file_get_contents($filePath);

        $response = new HttpResponse();
        $response->setContent($json);

        $httpClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $resource = $client->get('/posts/1');

        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertEquals(1, $resource->get('id'));
        $this->assertEquals('Post 1', $resource->get('title'));

        $comments = $resource->get('comments');
        $this->assertInstanceOf(ResourceCollection::class, $comments);
        $this->assertCount(3, $comments);

        $i = 1;
        foreach ($comments as $comment) {
            $this->assertInstanceOf(Resource::class, $comment);
            $this->assertEquals($i, $comment->get('id'));
            $this->assertEquals('Comment ' . $i, $comment->get('body'));
            $i++;
        }
    }
}