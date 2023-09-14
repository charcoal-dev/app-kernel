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

namespace Charcoal\Tests\Apps\Objects;

use Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject;
use Charcoal\Buffers\Frames\Bytes20;

class User extends AbstractAppObject
{
    public int $id;
    public string $status;
    public Bytes20 $checksum;
    public string $username;
    public string $email;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public string $country;
    public int $joinedOn;

    /**
     * @return string[]
     */
    public function getRegistryKeys(): array
    {
        return [
            "users_id:" . $this->id,
            "users_username:" . $this->username
        ];
    }

    public function __serialize(): array
    {
        $data = parent::__serialize();
        $this->serializeProps($data,
            "id",
            "status",
            "checksum",
            "username",
            // "email",
            "firstName",
            // "lastName",
            // "country",
            // "joinedOn"
        );

        return $data;
    }
}
