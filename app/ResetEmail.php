<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResetEmail extends Model
{
    use SoftDeletes;

     /** @var string */
     protected $table = 'user_reset_emails';

     /** @var array */
     protected $fillable = [
        'user_id',
        'email',
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
