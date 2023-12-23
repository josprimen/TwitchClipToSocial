<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UrlCanal extends Model
{

    use SoftDeletes;

    protected $dates = ['created_at', 'deleted_at'];

    protected $table = 'url_canales';


    /**
     * The attributes that are mass assignable.
     *
     */
    protected $fillable = [
        'url',
        'nombre_canal',
        'ultima_consulta',
        'body_canal',
    ];

    /**************************************************************************/
    /*--------------------------- RELACIONES ---------------------------------*/
    /**************************************************************************/

    /**************************************************************************/
    /*--------------------------- SCOPES -------------------------------------*/
    /**************************************************************************/

    /**************************************************************************/
    /*--------------------------- ATRIBUTOS ---------------------------------*/
    /**************************************************************************/

    public function getCreatedAtFormateadaAttribute()
    {
        return is_null($this->created_at) ? '' : $this->created_at->format('H:i d/m/Y');
    }

    public function getDeletedAtFormateadaAttribute()
    {
        return is_null($this->deleted_at) ? '' : $this->deleted_at->format('H:i d/m/Y');
    }

    /**************************************************************************/
    /*--------------------------- MÉTODOS ---------------------------------*/
    /**************************************************************************/

    /**************************************************************************/
    /*--------------------------- SPATIE ---------------------------------*/
    /**************************************************************************/



}
