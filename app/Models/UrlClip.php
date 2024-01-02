<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UrlClip extends Model
{

    use SoftDeletes;

    protected $dates = ['created_at', 'deleted_at'];

    protected $table = 'url_clips';


    /**
     * The attributes that are mass assignable.
     *
     */
    protected $fillable = [
        'url',
        'titulo_clip',
        'url_thumbnail',
        'body_clip',
        'obtenido_video',
        'id_url_canal',
    ];

    /**************************************************************************/
    /*--------------------------- RELACIONES ---------------------------------*/
    /**************************************************************************/

    public function canal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UrlCanal::class, 'id', 'id_url_canal');
    }

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
