<?php
namespace HalClientTest;

use HalClient\Client;
use PHPUnit_Framework_TestCase;
use Zend\Http\Response as HttpResponse;

class ClientTest extends PHPUnit_Framework_TestCase
{
    protected function getPaginatedResources($pages = 3)
    {
        $client = new Client();

        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->setMethods(['send'])
            ->getMock();
        $client->setHttpClient($httpClient);

        for ($i = 0; $i < $pages; $i++) {
            $filePath = realpath(__DIR__ . '/assets/pagination/page' . ($i + 1) . '.json');
            $json = file_get_contents($filePath);

            $response = new HttpResponse();
            $response->setContent($json);

            $httpClient
                ->expects($this->at($i))
                ->method('send')
                ->will($this->returnValue($response));
        }

        return $client->get('/posts');
    }

    public function testGetCountOfPaginatedResources()
    {
        $resourcePaginator = $this->getPaginatedResources(1);

        $this->assertInstanceOf('HalClient\ResourcePaginator', $resourcePaginator);
        $this->assertCount(15, $resourcePaginator);
    }

    public function testIterateThroughPaginatedResources()
    {
        $resourcePaginator = $this->getPaginatedResources();

        $i = 1;
        foreach ($resourcePaginator as $resource) {
            $this->assertInstanceOf('HalClient\Resource', $resource);
            $this->assertEquals($i, $resource->get('id'));
            $this->assertEquals('Post ' . $i, $resource->get('title'));
            $i++;
        }
    }

    public function testGetPaginatedResourcesUsingKeys()
    {
        $resourcePaginator = $this->getPaginatedResources();

        for ($i = 0; $i < count($resourcePaginator); $i++) {
            $resource = $resourcePaginator[$i];
            $this->assertInstanceOf('HalClient\Resource', $resource);

            $id = $i + 1;
            $this->assertEquals($id, $resource->get('id'));
            $this->assertEquals('Post ' . $id, $resource->get('title'));
        }
    }

    public function testPaginatedResourcesExistUsingKeys()
    {
        $resourcePaginator = $this->getPaginatedResources();

        for ($i = 0; $i < count($resourcePaginator); $i++) {
            $this->assertTrue(isset($resourcePaginator[$i]));
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

        $this->assertInstanceOf('HalClient\Resource', $resource);
        $this->assertEquals(1, $resource->get('id'));
        $this->assertEquals('Post 1', $resource->get('title'));

        $comments = $resource->get('comments');
        $this->assertInstanceOf('HalClient\ResourceCollection', $comments);
        $this->assertCount(3, $comments);

        $i = 1;
        foreach ($comments as $comment) {
            $this->assertInstanceOf('HalClient\Resource', $comment);
            $this->assertEquals($i, $comment->get('id'));
            $this->assertEquals('Comment ' . $i, $comment->get('body'));
            $i++;
        }
    }

    /**
     * @expectedException \HalClient\Exception\RuntimeException
     */
    public function testGetApiProblem()
    {
        $client = new Client();

        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->setMethods(['send'])
            ->getMock();
        $client->setHttpClient($httpClient);

        $filePath = realpath(__DIR__ . '/assets/api-problem.json');
        $json = file_get_contents($filePath);

        $response = new HttpResponse();
        $response->setContent($json);

        $httpClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $resource = $client->get('/posts/1');
    }
}
