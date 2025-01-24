<?php
declare(strict_types=1);

require_once __DIR__ . "/TestApp.php";

/**
 * Class AppKernelTest
 */
class AppKernelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testBasicConstruct(): void
    {
        $startedOn = microtime(true);
        $testApp = new TestApp(new \Charcoal\Filesystem\Directory(__DIR__));
        $testApp->lifecycle->startedOn = $startedOn;
        $testApp->bootstrap();
        $this->assertEquals(0, $testApp->errors->count());
    }
}