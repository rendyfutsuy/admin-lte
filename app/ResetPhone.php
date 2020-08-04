<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResetPhone extends Model
{
    use SoftDeletes;

     /** @var string */
     protected $table = 'user_reset_phones';

     /** @var array */
     protected $fillable = [
        'user_id',
        'phone',
        'activation_code',
        'expires_at',
     ];
 
     /**
      * The attributes that should be cast to native types.
      *
      * @var array
      */
     protected $casts = [
         'expires_at' => 'datetime',
     ];
}
