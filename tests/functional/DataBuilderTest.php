<?php

class DataBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideComponents
     */
    public function testBuild(Array $components, $nodeId, Array $expected)
    {
        $this->assertSame($expected, \Helpers\DataBuilder::build($components, $nodeId)->toArray());
    }

    /**
     * @return array
     */
    public function provideComponents()
    {
        $files = glob(__DIR__ . '/../data/*.json');

        return array_map(function($file) {
            $json = file_get_contents($file);
            $contents = json_decode($json, true);
            return $contents;
        }, $files);
    }


}