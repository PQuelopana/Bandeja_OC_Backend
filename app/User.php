<?php

namespace App;

use App\Base\Model;

class User extends Model
{
    protected $table = 'usuarios';
    
    protected $hidden = [
        'varpass'
    ];

}