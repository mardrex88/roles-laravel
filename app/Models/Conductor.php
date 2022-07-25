<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;
use Depsimon\Wallet\HasWallet;

class Conductor extends Authenticatable
{
    use Notifiable, HasMultiAuthApiTokens;
    use HasWallet;

       /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'conductores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname','lastname',  'email', 'password','telefono','direccion','nit', 'created_at','updated_at','uid','estado','id_crm'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
        
}
