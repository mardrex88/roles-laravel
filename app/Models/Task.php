<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Task extends Model
{
        /**
     * The table  associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';
  protected $primaryKey ="id";

  public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','ride_id', 'latitude', 'longitude','address','name_contact','phone_contact','detalles',
    ];
}