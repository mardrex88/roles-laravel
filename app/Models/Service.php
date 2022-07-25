<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Service extends Model
{
        /**
     * The table  associated with the model.
     *
     * @var string
     */
    protected $table = 'config_services';
  protected $primaryKey ="id";

  public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','name', 'price_km', 'porc_comision','price_minim',
    ];
}