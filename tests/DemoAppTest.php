<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Tests\Apps;

require_once "loader.php";

use Charcoal\Apps\Kernel\Modules\Objects\ObjectRegistrySource;
use Charcoal\Buffers\Frames\Bytes20P;
use Charcoal\Filesystem\Directory;
use Charcoal\Tests\Apps\Objects\DemoApp;
use Charcoal\Tests\Apps\Objects\User;

/**
 * Class DemoAppTest
 * @package Charcoal\Tests\Apps
 */
class DemoAppTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     * @throws \Charcoal\Apps\Kernel\Exception\AppRegistryObjectNotFound
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     * @throws \Charcoal\Filesystem\Exception\FilesystemException
     */
    public function testSerialize(): void
    {
        $app = new DemoApp(new Directory(__DIR__ . DIRECTORY_SEPARATOR . "DemoApp"));

        // Bootstrap all initialized modules
        $app->bootstrap();

        // Store some objects
        $u1 = new User();
        $u1->id = 1;
        $u1->username = "charcoal";

        $u2 = new User();
        $u2->id = 2;
        $u2->username = "FirstByte";

        $app->users()->objectsRegistry->store($u1);
        $app->users()->objectsRegistry->store($u2);

        // Check module objects registry
        $this->assertCount(4, $app->users()->objectsRegistry->getAllRuntime()); // 2 instances, pointing towards 4 keys
        $this->assertEquals(spl_object_id($u1), spl_object_id($app->users()->objectsRegistry->get("users_id:1")));
        $this->assertEquals(spl_object_id($u1), spl_object_id($app->users()->objectsRegistry->get("users_username:charcoal")));
        $this->assertNotEquals(
            spl_object_id($app->users()->users->findByUsername("charcoal")),
            spl_object_id($app->users()->users->findByUsername("firstByte")) // Case-sensitivity
        );

        $this->assertEquals(
            spl_object_id($app->users()->users->findByUsername("charcoal")),
            spl_object_id($app->users()->users->findById(1))
        );

        // Serialize Application State
        $serialized = serialize($app);
        unset($app);

        /** @var DemoApp $app */
        $app = unserialize($serialized);
        $app->bootstrap();
        $this->assertEquals(spl_object_id($app), spl_object_id($app->users()->app));
        $this->assertEquals(spl_object_id($app), spl_object_id($app->users()->users->module->app));
        $this->assertEquals(spl_object_id($app->users()), spl_object_id($app->users()->users->module->app->modules->get("users")));
        $this->assertCount(0, $app->users()->objectsRegistry->getAllRuntime(), "Objects repositories are created a new");

        // Check a table instance id matches one stored in registry
        $this->assertEquals(
            spl_object_id($app->users()->users->table),
            spl_object_id($app->users()->tables->getFor("primary")[0])
        );
    }

    /**
     * @return void
     */
    public function testObjectSerialize(): void
    {
        $u1 = new User();
        $u1->id = 1;
        $u1->status = "active";
        $u1->checksum = new Bytes20P("tba");
        $u1->username = "charcoal";
        $u1->firstName = "Charcoal";
        $u1->lastName = "FirsByte";
        $u1->metaObjectCachedOn = 123456;
        $u1->metaObjectSource = ObjectRegistrySource::CACHE;

        $user = serialize(clone $u1);
        unset($u1);

        $u2 = unserialize($user);
        $this->assertInstanceOf(User::class, $u2);
        $this->assertTrue(isset($u2->id));
        $this->assertEquals(1, $u2->id);
        $this->assertTrue(isset($u2->username));
        $this->assertEquals("charcoal", $u2->username);
        $this->assertTrue(isset($u2->checksum));
        $this->assertInstanceOf(Bytes20P::class, $u2->checksum);
        $this->assertEquals("\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0tba", $u2->checksum->raw());
        $this->assertTrue(isset($u2->firstName));
        $this->assertEquals("Charcoal", $u2->firstName);
        $this->assertFalse(isset($u2->lastName));

        $this->assertFalse(isset($u2->metaObjectSource));
        $this->assertFalse(isset($u2->metaObjectRuntime));
        $this->assertEquals(123456, $u2->metaObjectCachedOn);
    }
}
