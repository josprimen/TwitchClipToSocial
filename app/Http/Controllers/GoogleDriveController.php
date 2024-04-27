<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemCreateRequest;
use App\Http\Requests\ItemUpdateRequest;
use App\Models\User;
use DB;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Input;
use Redirect;
use Session;
use Storage;

class GoogleDriveController extends Controller
{

    /**
     * Rutas utilizadas por el controlador
     *
     * @return void
     */
    public static function routes()
    {

        Route::group(['prefix' => 'drive-video', 'as' => 'drive-video.'], static function () {

            Route::get('/', [self::class, 'index'])->name('index');
            Route::get('test', [self::class, 'test'])->name('test');
            Route::get('upload', [self::class, 'uploadToGoogleDrive'])->name('upload');


        });
    }


    /**
     * Metodo para subir un archivo a google drive
     */
    public function uploadToGoogleDrive($urlVideo)
    {

        try {
            $videoPath = $urlVideo; // Ruta del video local
            $videoName = 'clip_'. time() . '.mp4'; // Nombre del video en Google Drive

            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/credentials/estasi.json')); // Ruta de las credenciales de Google
            $client->addScope(Google_Service_Drive::DRIVE); // Cambia el alcance según tus necesidades
            $client->setAccessType('offline');

            $driveService = new Google_Service_Drive($client);

            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $videoName,
            ]);
            $content = file_get_contents($videoPath);
            $file = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'uploadType' => 'multipart',
                'fields' => 'id',
            ]);

            // Set the permissions to make the file public
            $permission = new Google_Service_Drive_Permission();
            $permission->setRole('reader');
            $permission->setType('anyone');
            $permission->setAllowFileDiscovery(false);
            $driveService->permissions->create($file->id, $permission);

            // Obtener la URL de visualización del archivo
            $fileUrl = $this->getFileUrl($driveService, $file->id);
            Log::info('Exito en la subida a google drive. Url: ' . $fileUrl);

            // Devolver la URL del archivo
            return $fileUrl;
        }catch (\Exception $e){
            Log::info('Fallo en la subida a Drive: ' . $e->getMessage());
        }

    }

    protected function getFileUrl($driveService, $fileId)
    {
        // Obtener la información del archivo
        $file = $driveService->files->get($fileId, ['fields' => 'webViewLink']);

        // Devolver la URL de visualización del archivo
        return $file->webViewLink;
    }

    /**
     * Metodo para borrar un archivo de google drive a partir de su id
     */
    public function deleteFromGoogleDrive($fileId)
    {
        try {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/credentials/estasi.json')); // Ruta de las credenciales de Google
            $client->addScope(Google_Service_Drive::DRIVE); // Cambia el alcance según tus necesidades
            $client->setAccessType('offline');

            $driveService = new Google_Service_Drive($client);

            // Borrar el archivo
            $driveService->files->delete($fileId);

            return true; // Devuelve verdadero si se ha borrado correctamente
        } catch (\Exception $e) {
            // Manejar cualquier error que pueda ocurrir durante el proceso de borrado
            Log::info('Fallo en la eliminación del archivo de Drive: ' . $e->getMessage());
            return false; // Devuelve falso si ocurrió un error
        }
    }



    /**
     * Metodo para listar todos los productos en la vista principal
     */
    public function index(Request $request)
    {

        $user = Auth::user();
        $credentials = $user->getMedia('credenciales_drive')->last()->getPath();

        $client = new Google_Client();
        $client->setAuthConfig($credentials);
        $client->addScope(Google_Service_Drive::DRIVE_READONLY);
        $client->setAccessType('offline');

        $service = new Google_Service_Drive($client);
        $folderId = $user->folder_id;

        $videos = [];

        $results = $service->files->listFiles([
            'q' => "'$folderId' in parents and trashed = false",
            'includeItemsFromAllDrives' => true,
            'supportsAllDrives' => true,
        ]);

        foreach ($results->getFiles() as $file) {
            $videoObject = new \stdClass();
            $videoObject->name = $file->getName();
            $videoObject->url = 'https://drive.google.com/file/d/' . $file->getId() . '/preview';
            $videos[] = $videoObject;
        }

        return view('video', compact('videos'));
    }





}


