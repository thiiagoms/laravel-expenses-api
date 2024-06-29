<?php

namespace App\Repositories\ORM\User;

use App\Models\User;
use App\Repositories\Contracts\User\UserContract;
use App\Repositories\ORM\BaseRepository;

class UserRepository extends BaseRepository implements UserContract
{
    protected $model = User::class;
}
