<?php

namespace Paulohsilvestre\GeneratorForLaravel\Generation;
 
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Paulohsilvestre\GeneratorForLaravel\Generation\Migration;
 
class GenerationController extends Controller
{
 
    private $_file;

    public function getFile(){
        $storage = storage_path();
        $dire = $storage."/der";
        $dire .= "/process.sql";
        $this->_file = $dire;
    }


    public function index()
    {
        $storage = storage_path();
        $dire = $storage."/der";
        $dire .= "/process.sql";
        $exist = file_exists($dire);
        return view('generation::upload',compact('exist'));
    }
 
    public function upload(Request $request){
        
        $file = $request->file('file');
        $name = $file->getClientOriginalName();
        $contain = strpos($name, ' ');
        $ext = substr($name,strlen($name)-3,strlen($name));
        
        $permitidos = array("SQL");
        if (in_array(strtoupper($ext), $permitidos)){

            $storage = storage_path();
            $dire = $storage."/der";
            if (!file_exists($dire)){
                $result = \File::makeDirectory($dire, 0777, true);
            }

            $file->move($dire, "process.sql");
            return \Response::json('Arquivo Enviado com sucesso', 201);

        } else {
            return \Response::json('EXTENSION FILE INVALID, AUTHORIZED SEND .SQL', 500);
        }
    }

    public function generation(Request $request){

        $data = $request->all();
        self::getFile();
        if (sizeof($data) > 0){
            $schema = Migration::getSchema($this->_file);
            dd($schema);
        } else {
            return \Response::json('DATA INVALID!!!', 500);
        }

    }


}