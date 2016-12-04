<?php

namespace Boparaiamrit\Tests\Models;

use Boparaiamrit\Notifynder\Notifynderable;

class User extends \Illuminate\Database\Eloquent\Model
{
    // Never do this
    protected $fillable = ['id'];
    use Notifynderable;
}
