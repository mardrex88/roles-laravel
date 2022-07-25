<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Documentos extends Model
{
     /**
     * The table  associated with the model.
     *
     * @var string
     */
    protected $table = 'documentos';
  protected $primaryKey ="id";

  public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'key','img','driver_id','type_user' ,'date_venc','created_at','updated_at',
    ];


}
