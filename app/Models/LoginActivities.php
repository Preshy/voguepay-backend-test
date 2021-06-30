<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginActivities extends Model
{
    protected $fillable = ['account_id', 'ipaddress', 'device'];
}
