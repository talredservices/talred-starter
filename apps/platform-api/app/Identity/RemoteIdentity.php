<?php

declare(strict_types=1);

namespace App\Identity;

use Zolta\Http\Authorization\Identity;

final class RemoteIdentity extends Identity
{
    protected static array $permissionPaths = ['identity.permissions.*'];
}
