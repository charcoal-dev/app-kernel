<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Unit;

use Charcoal\App\Kernel\Support\JsonHelper;
use Charcoal\Filesystem\Path\DirectoryPath;
use PHPUnit\Framework\TestCase;

final class JsonHelperTest extends TestCase
{
    private DirectoryPath $fixturesDir;

    /**
     * @return void
     * @throws \Charcoal\Filesystem\Exceptions\InvalidPathException
     * @throws \Charcoal\Filesystem\Exceptions\PathTypeException
     */
    protected function setUp(): void
    {
        $this->fixturesDir = (new DirectoryPath(dirname(__FILE__, 2)))
            ->join("/Fixtures/Stubs/json")
            ->isDirectory();
    }

    /**
     * @return void
     * @throws \Charcoal\Filesystem\Exceptions\FilesystemException
     * @throws \JsonException
     */
    public function testJsonDecodeImportsMergesObjectsCollectsBaggageAndResolvesNestedImports(): void
    {
        $result = JsonHelper::jsonDecodeImports($this->fixturesDir, "root");

        $this->assertIsArray($result);

        // simple key preserved
        $this->assertArrayHasKey("a", $result);
        $this->assertSame(1, $result["a"]);

        // cfg: merged object imports and nested imports + local overrides
        $this->assertArrayHasKey("cfg", $result);
        $this->assertIsArray($result["cfg"]);
        $this->assertSame("bar", $result["cfg"]["foo"]);
        $this->assertSame(2, $result["cfg"]["p"]);
        $this->assertSame(1, $result["cfg"]["c"]);
        $this->assertTrue($result["cfg"]["z"]);

        // scalars: baggage array
        $this->assertArrayHasKey("scalars", $result);
        $this->assertSame(["hello", 123], $result["scalars"]);

        // listMerge: baggage array from list import
        $this->assertArrayHasKey("listMerge", $result);
        $this->assertSame([1, 2, 3], $result["listMerge"]);
    }
}