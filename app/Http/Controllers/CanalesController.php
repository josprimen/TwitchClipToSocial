<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UrlCanal;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Sunra\PhpSimple\HtmlDomParser;
use Symfony\Component\DomCrawler\Crawler;

class CanalesController extends Controller
{

    public static function routes()
    {

        Route::group([ 'prefix' => 'canales', 'as' => 'canales.' ], function () {

                Route::post('guardar', [ CanalesController::class, 'guardar' ])->name('guardar');

        });
    }


    /**
     * Metodo principal
     */
    public function guardar(Request $request)
    {
        try {
//            dd($request->all(), explode('/', $request->url_canal)[3]);

            if ($request->has('url_canal')){
                DB::beginTransaction();

                $canal = new UrlCanal();
                $canal->url = $request->url_canal;
                $canal->nombre_canal = explode('/', $request->url_canal)[3];
                $canal->save();

                DB::commit();
                \request()->session()->flash('success', 'Canal añadido con éxito');
                return redirect()->route('dashboard');

            }


        }catch (\Exception $e){
            dd($e);
        }
    }



}
