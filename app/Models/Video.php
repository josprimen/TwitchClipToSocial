<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'videos';


    /**
     * The attributes that are mass assignable.
     *
     */
    protected $fillable = [
        'url',
        'subido',
        'descargado_video',
        'id_url_clip',

    ];

    /**************************************************************************/
    /*--------------------------- RELACIONES ---------------------------------*/
    /**************************************************************************/

    public function clip(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UrlClip::class, 'id', 'id_url_clip');
    }

    /**************************************************************************/
    /*--------------------------- SCOPES -------------------------------------*/
    /**************************************************************************/

    /**************************************************************************/
    /*--------------------------- ATRIBUTOS ---------------------------------*/
    /**************************************************************************/

    /**************************************************************************/
    /*--------------------------- MÉTODOS ---------------------------------*/
    /**************************************************************************/

    /**************************************************************************/
    /*--------------------------- SPATIE ---------------------------------*/
    /**************************************************************************/



}
