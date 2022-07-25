<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Notificacion extends Model
{
        /**
     * The table  associated with the model.
     *
     * @var string
     */
    protected $table = 'notificacion_order';
  protected $primaryKey ="id";

  public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','id_driver', 'status', 'id_order'
    ];

    public function driver()
  {
      return $this->belongsTo('App\Driver','id_driver');
  }
}