<?php

namespace ML\JsonLD\Test;

use PHPUnit\Framework\TestCase;

/**
 * Debug test to check data provider functionality
 */
class DebugDataProviderTest extends TestCase
{
    /**
     * @dataProvider debugProvider
     */
    public function testDebug($arg1, $arg2, $arg3)
    {
        $this->assertIsString($arg1);
        $this->assertIsObject($arg2);
        $this->assertIsObject($arg3);
        echo "Debug test called with: " . $arg1 . "\n";
    }

    public static function debugProvider()
    {
        return [
            ['test1', new \stdClass(), new \stdClass()],
            ['test2', new \stdClass(), new \stdClass()],
        ];
    }
}