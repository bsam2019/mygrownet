<?php

namespace Tests\Unit;

use Tests\TestCase;

class SimpleMemoryTest extends TestCase
{
    public function test_basic_assertion()
    {
        $this->assertTrue(true);
    }

    public function test_memory_usage()
    {
        $memoryBefore = memory_get_usage();
        
        // Do something simple
        $array = range(1, 1000);
        $sum = array_sum($array);
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertLessThan(1024 * 1024, $memoryUsed); // Less than 1MB
        $this->assertEquals(500500, $sum);
    }
}