<?php


use PHPUnit\Framework\TestCase;
use ZM\Console\Console;

class ConsoleTest extends TestCase
{
    public function testConsoleLevel4()
    {
        Console::init(4);
        ob_start();
        Console::info('haha');
        $r = ob_get_clean();
        echo $r;
        $this->assertStringContainsString('TestCase:testConsole', $r);
    }

    public function testConsoleLevel2()
    {
        Console::init(2);
        ob_start();
        Console::info('haha');
        $r = ob_get_clean();
        echo $r;
        $this->assertStringContainsString('haha', $r);
    }

    public function testConsoleLevel1()
    {
        Console::init(1);
        ob_start();
        Console::info('haha');
        $r = ob_get_clean();
        echo $r;
        $this->assertEquals('', $r);
    }
}
