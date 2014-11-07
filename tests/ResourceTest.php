<?php
namespace HalClientTest;

use HalClient\Resource;
use PHPUnit_Framework_TestCase;

class ResourceTest extends PHPUnit_Framework_TestCase
{
    public function testinstantiateResource()
    {
        $properties = [
            'prop_1' => 'value_1',
            'prop_2' => 'value_2',
            'prop_3' => 'value_3',
        ];
        $links = [
            'self' => [
                'href' => 'http://localhost/items',
            ],
        ];
        $embedded = [
            'item_1' => 'value_1',
            'item_2' => 'value_2',
            'item_3' => 'value_3',
        ];
        $resource = new Resource($properties, $links, $embedded);

        foreach ($properties as $name => $value) {
            $this->assertEquals($value, $resource->getProperty($name));
        }

        $selfLink = $resource->getLink('self');
        $this->assertInstanceOf('HalClient\Link', $selfLink);
        $this->assertEquals('self', $selfLink->getName());
        $this->assertEquals($links['self']['href'], $selfLink->getHref());

        foreach ($embedded as $name => $value) {
            $this->assertEquals($value, $resource->get($name));
        }
    }
}