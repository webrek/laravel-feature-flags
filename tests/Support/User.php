<?php

namespace Webrek\FeatureFlags\Tests\Support;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property string $plan
 */
class User extends Authenticatable
{
    protected $guarded = [];

    public $timestamps = false;
}
