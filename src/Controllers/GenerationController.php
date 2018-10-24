<?php

namespace Paulohsilvestre\GeneratorForLaravel\Controllers;
 
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Paulohsilvestre\GeneratorForLaravel\Utils\Migration as Migration;
use Paulohsilvestre\GeneratorForLaravel\Utils\Functions;
 
class GenerationController extends Controller
{
 
    private $_file;

    public function getFile(){
        $storage = storage_path();
        $dire = $storage."/der";
        $dire .= "/process.sql";
        $this->_file = $dire;
    }

    public static function getHead(){
        $file = "/**\n";
        $file .= "* CONTRIBUA PARA O PROJETO em https://github.com/paulohsilvestre/generatorforlaravel\n";
        $file .= "* Created by GeneratorForLaravel - Paulo Henrique Silvestre.\n";
        $file .= "* Email: paulohsilvestre@gmail.com\n";
        $file .= "* Phone: (46) 99106-1331\n";
        $file .= "* Date: ".date('d/m/Y')."\n";
        $file .= "* Time: ".date('H:i:s')."\n";
        $file .= "*/\n";
        return $file;
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
            if (@$data['directory'] == ""){
                $data['directory'] = "Entities";
            }
            $schema = Migration::getSchema($this->_file);
            $generation['head'] = $data;
            $generation['schema'] = $schema;

            self::createMigration($generation);
            self::createEntities($generation);
            self::createEloquent($generation);
            self::createController($generation);
            self::createServices($generation);
            self::createRepositories($generation);
            self::createValidators($generation);
            self::createRouter($generation);
            self::createHtml($generation);
            self::createProvider($generation);
            
        } else {
            return \Response::json('DATA INVALID!!!', 500);
        }

    }

    public static function createEntities($generation){
        
            //dd($generation);
            $dir = app_path()."/".$generation['head']['directory'];
            if (!file_exists($dir)){
                mkdir($dir, 0777, true);
            }

            $dir_field = app_path()."/".$generation['head']['directory']."/field";
            if (!file_exists($dir_field)){
                mkdir($dir_field, 0777, true);
            }

            if ($generation){
                
                $function = new Functions();
                
                foreach($generation['schema']['class'] as $value){
                    
                    $nameFile = $value->table["name"];
                    
                    $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                    $nameClass = $function->getNameClass($nameFile,$fullname);
                    if (!file_exists($dir."/".$nameClass.".php")){
    
                        $str = "";
                        $str .= "<?php\n";
                        $str .= self::getHead();
                        $str .= "namespace ".$generation['head']['namespace']."\\".$generation['head']['directory'].";\n\n";
                        $str .= "use Illuminate\Database\Eloquent\Model;\n\n";
                        
                        $str .= "class ".$nameClass." extends Model\n";
                        $str .= "{\n";
                        $str .= "\n";
                        if (@$generation['head']['addcon'] == "Y"){
                            $str .= "\t\tprotected \$connection = '".$generation['head']['connection']."';\n";    
                        }
                        $str .= "\t\tprotected \$table = '".$nameFile."';\n";
                        $str .= "\t\tprotected \$fillable;\n";
                        $str .= "\n";
                        $str .= "\t\tpublic function __construct()\n";
                        $str .= "\t\t{\n";
                        $str .= "\t\t\t\$fields = include('fields/fields_".strtolower($nameClass). ".php"."');\n";
                        $str .= "\t\t\t\$this->fillable = \$fields;\n";
                        $str .= "\t\t}\n";
                        $str .= "\n";
                        
                        $tmForeign = @sizeof(@$value->table['foreign']);
                        if ($tmForeign > 0){
                            foreach ($value->table['foreign'] as $chave){
                                
                                $str .= "\t\tpublic function ".strtolower($chave->referencetable)."()\n";
                                $str .= "\t\t{\n";
                                $str .= "\t\t\treturn \$this->hasOne('".$function->getNameClass($chave->referencetable)."');\n";
                                $str .= "\t\t}\n";
                                $str .= "\n";
                                
                                unset($chave);
                            }
                        }
                        
                        $str .= "}\n";
                        
                        if (!file_exists($dir ."/". $nameClass . ".php")){
                            $fp4 = fopen($dir ."/". $nameClass . ".php", "w+");
                            $escreve2 = fwrite($fp4, $str);
                            fclose($fp4);
                            chmod($dir ."/". $nameClass . ".php",0777);
                        }
                        unset($value);
                    }

                    $tmField = sizeof($value->table["fields"]);
                    if ($tmField > 0){
                            $_str = "";
                            $_str .= "<?php\n";
                            $_str .= self::getHead();
                            $_str .= "\n";
                            $_str .= "return [\n";
                        $cont = 1;
                        foreach($value->table["fields"] as $field){
                            if ($value->table["primary"] != $field->name){
                                if ($cont == $tmField){
                                    $_str .= "\t'".$field->name."'\n";
                                } else {
                                    $_str .= "\t'".$field->name."',\n";
                                }
                            }
                            $cont++;
                            unset($field);
                        }
                        $_str .= "];\n";
                        $_str .= "\n";

                        $file_field = fopen($dir_field . "/fields_".strtolower($nameClass). ".php", "w+");
                        $escreve2 = fwrite($file_field, $_str);
                        fclose($file_field);
                        chmod($dir_field . "/fields_".strtolower($nameClass). ".php",0777);

                    }


                }
                
            }

    }

    public static function createMigration($generation){

        //$dir = dirname(dirname(dirname(__FILE__))) . "/database/";

        $dir = database_path()."/migrations/";
        $dir_entities = app_path()."/".$generation['head']['directory'];

        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }
        
        if ($generation){
            
            $function = new Functions();
            $migration = new Migration();
            $sequence = 10;
            foreach($generation['schema']['class'] as $value){
                
                $nameFile = $value->table["name"];
                
                $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                $nameClass = $function->getNameClass($nameFile,$fullname);
                
                if (!$function->fileExistsContent($dir, "_create_".$nameFile."_table")){
                
                    $str = "";
                    $str .= "<?php\n\n";
                    $str .= self::getHead();
                    $str .= "use Illuminate\Database\Schema\Blueprint;\n";
                    $str .= "use Illuminate\Database\Migrations\Migration;\n";

                    if (str_contains($nameFile, "_")){
                        $class_name = explode("_",$nameFile);
                        $nameClass = $function->getNameClassFirstUpperCase($class_name[0]).$function->getNameClassFirstUpperCase($class_name[1]);
                    }
                    
                    $str .= "class Create".$nameClass."Table extends Migration\n";
                    $str .= "{\n";
                    $str .= "\n\n\n";
                    $str .= "\t/**\n";
                    $str .= "\t* Run the migrations.\n";
                    $str .= "\t*\n";
                    $str .= "\t* @return void\n";
                    $str .= "\t*/\n";
                    $str .= "\tpublic function up()\n";
                    $str .= "\t{\n";
                    
                    if (@$generation['head']['addcon'] == "Y"){
                        $str .= "\t\t\tSchema::connection('".$generation['head']['connection']."')->create('".$nameFile."', function (Blueprint \$table) {\n";
                    } else {
                        $str .= "\t\t\tSchema::create('".$nameFile."', function (Blueprint \$table) {\n";
                    }
                    $tmField = sizeof($value->table["fields"]);
                    if ($tmField > 0){
                       
                        foreach($value->table["fields"] as $field){
                            
                            $addField = true;
                            if (@sizeof($value->table["foreign"]) > 0){
                                foreach ($value->table["foreign"] as $vl){
                                    if ($field->name == $vl->foreign){
                                        $addField = false;
                                        break;
                                    }
                                    unset($vl);
                                }
                            }
                            if ($addField){
                                $str .= "\t\t\t\t".$migration->getMigrationField($field, @$value->table['index'])."\n";
                            }
                            unset($field);
                        }
                        
                        if (sizeof(@$value->table['foreign']) > 0){
                            foreach ($value->table['foreign'] as $chave){
                                $str .= $migration->getMigrationForeign($chave);
                                unset($chave);
                            }
                        }
                        
                        $str .= "\t\t\t\t\$table->timestamps();\n";
                    }
                    $str .= "\t\t\t});\n";
                    $str .= "\t}\n";
                    
                    $str .= "\t\n\n\n";
                    $str .= "\t/**\n";
                    $str .= "\t * Reverse the migrations.\n";
                    $str .= "\t *\n";
                    $str .= "\t * @return void\n";
                    $str .= "\t */\n";
                    $str .= "\tpublic function down()\n";
                    $str .= "\t{\n";
                    if ($generation['head']['connection'] != "") {
                        $str .= "\t\t\tSchema::connection('".$generation['head']['connection']."')->drop('".$nameFile."');\n";
                    } else {
                        $str .= "\t\t\tSchema::drop('".$nameFile."');\n";
                    }
                    $str .= "\t}\n";
                    
                    $str .= "\n\n}";
                    $micro = microtime();
                    $micro = str_ireplace(".", "", $micro);
                    $file_name = date('Y')."_".date('m')."_".date('d')."_".date('Hmisu').$sequence."_create_".$nameFile."_table.php";
                    $sequence++;
                    if (!file_exists($dir.$file_name)){
                        $fp4 = fopen($dir. $file_name, "w+");
                        $escreve2 = fwrite($fp4, $str);
                        fclose($fp4);
                        chmod($dir . $file_name,0777);
                    }
                    unset($value);

                } else {

                    $name_search = $dir_entities."/field/fields_".strtolower($nameClass).".php";
                    if (file_exists($name_search)){

                        $nameclass_alter = date('Y').date('m').date('d').date('Hmisu');

                        $strFileMigration = "";
                        $strFileMigration .= "<?php\n";
                        $strFileMigration .= self::getHead();
                        $strFileMigration .= "use Illuminate\Database\Schema\Blueprint;\n";
                        $strFileMigration .= "use Illuminate\Database\Migrations\Migration;\n";
                        $strFileMigration .= "use Illuminate\Support\Facades\Schema;\n";
                        $strFileMigration .= "\n";
                        $strFileMigration .= "class AddField".$nameclass_alter.$nameClass."Table extends Migration\n";
                        $strFileMigration .= "{\n";
                        $strFileMigration .= "\n";
                        $strFileMigration .= "\tpublic function up()\n";
                        $strFileMigration .= "\t{\n";
                        $strFileMigration .= "\n";
                        if ($generation['head']['connection'] != "") {
                            $strFileMigration .= "\t\tSchema::connection('".$generation['head']['connection']."')->table('".$nameFile."', function (\$table) {\n";
                        } else {
                            $strFileMigration .= "\t\tSchema::table('".$nameFile."', function (\$table) {\n";
                        }

                        $ponteiro = fopen ($dir_entities."/field/fields_".strtolower($nameClass).".php","r");
                        $stream = "";
                        while (!feof ($ponteiro)) {
                            $stream .= fgets($ponteiro,4096);
                        }
                        fclose ($ponteiro);   

                        $tmField = sizeof($value->table["fields"]);
                        $criaMigration = false;
                        if ($tmField > 0){
                           
                            foreach($value->table["fields"] as $field){
                                $addField = false;
                                if ($field->name != "id"){
                                    $one_search = "'".$field->name."'";
                                    //$sec_search = "'".$field->name."'";
                                    $find1 = (strpos($stream,$one_search) > -1);
                                    //$find2 = (strpos($stream, $sec_search) > -1);
                                    if (($find1 == false)) { //} && ($find2 == false)){
                                        $addField = true;
                                    }
                                    if ($addField){
                                        $criaMigration = true;
                                        $strFileMigration .= "\t\t\t".$migration->getMigrationField($field, @$value->table['index'])."\n";
                                    }
                                }
                                unset($field);
                            }
                            
                            if (@sizeof($value->table['foreign']) > 0){
                                foreach ($value->table['foreign'] as $chave){
                                    $one_search = "'".$chave->foreign."',";
                                    $sec_search = ",'".$chave->foreign."'";
                                    $find1 = (strpos($stream,$one_search) > -1);
                                    $find2 = (strpos($stream, $sec_search) > -1);
                                    if (($find1 == false) && ($find2 == false)){
                                        $strFileMigration .= $migration->getMigrationForeign($chave);
                                    }
                                    unset($chave);
                                }
                            }
                        }    

                        $strFileMigration .= "\t\t});\n";
                        $strFileMigration .= "\t}\n";
                        if ($generation['head']['connection'] != "") {
                            $strFileMigration .= "\tpublic function down(){Schema::connection('".$generation['head']['connection']."')->drop('".$nameFile."');}\n";
                        } else {
                            $strFileMigration .= "\tpublic function down(){Schema::drop('".$nameFile."');}\n";
                        }
                        
                        $strFileMigration .= "\t}\n";

                        if ($criaMigration){
                            $file_name = date('Y')."_".date('m')."_".date('d')."_".date('Hmisu').$sequence."_addField".$nameclass_alter.$nameClass."_table.php";
                            $sequence++;
                            if (!file_exists($dir."/".$file_name)){
                                $fp4 = fopen($dir. "/" . $file_name, "w+");
                                $escreve2 = fwrite($fp4, $strFileMigration);
                                fclose($fp4);
                                chmod($dir. "/" . $file_name,0777);
                            }
                        }

                    }

                }
                

            }
            
        }


    }

    public static function createEloquent($generation){

        $dir = app_path()."/Repositories/Eloquent/";
        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }
        if ($generation){

            $function = new Functions();
            
            foreach($generation['schema']['class'] as $value){
                
                $nameFile = $value->table["name"];
                    
                $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                $nameClass = $function->getNameClass($nameFile,$fullname);
                
                if (!file_exists($dir."".$nameClass.".php")){
                    
                    $file_name = $nameClass;

                    $file2 = "";
                    $file2 .= "<?php";
                    $file2 .= self::getHead();
                    $file2 .= " namespace ".$generation['head']['namespace']."\Repositories\Eloquent;";
                    $file2 .= "\n";
                    $file2 .= "\n";
                    $file2 .= "use Prettus\Repository\Eloquent\BaseRepository;";
                    $file2 .= "\n";
                    $file2 .= "use Prettus\Repository\Criteria\RequestCriteria;";
                    $file2 .= "\n";
                    $file2 .= "use ".$generation['head']['namespace']."\Repositories\\" . $file_name . "Repository;";
                    $file2 .= "\n";
                    $file2 .= "use ".$generation['head']['namespace']."\\".$generation['head']['directory']."\\" . $file_name . ";";
                    $file2 .= "\n";
                    $file2 .= "\n";
                    
                    $file2 .= "/**";
                    $file2 .= "\n";
                    $file2 .= " * Class ".$file_name."RepositoryEloquent";
                    $file2 .= "\n";
                    $file2 .= " * @package namespace ".$generation['head']['namespace']."\Repositories\Eloquent;";
                    $file2 .= "\n";
                    $file2 .= " */";
                    $file2 .= "\n";
                    $file2 .= "class " . $file_name . "RepositoryEloquent extends BaseRepository implements " . $file_name . "Repository";
                    $file2 .= "\n";
                    $file2 .= "{";
                    $file2 .= "\n";
                    $file2 .= "\t\t/**";
                    $file2 .= "\n";
                    $file2 .= "\t\t* Specify Model class name";
                    $file2 .= "\n";
                    $file2 .= "\t\t*";
                    $file2 .= "\n";
                    $file2 .= "\t\t* @return string";
                    $file2 .= "\n";
                    $file2 .= "\t\t*/";
                    $file2 .= "\n";
                    $file2 .= "\t\tpublic function model()";
                    $file2 .= "\n";
                    $file2 .= "\t\t{";
                    $file2 .= "\n";
                    $file2 .= "\t\t\treturn " . $file_name . "::class;";
                    $file2 .= "\n";
                    $file2 .= "\t\t}";
                    $file2 .= "\n";
                    $file2 .= "\n";
                    $file2 .= "\t\t/**";
                    $file2 .= "\n";
                    $file2 .= "\t\t* Boot up the repository, pushing criteria";
                    $file2 .= "\n";
                    $file2 .= "\t\t*/";
                    $file2 .= "\n";
                    $file2 .= "\t\tpublic function boot()";
                    $file2 .= "\n";
                    $file2 .= "\t\t{";
                    $file2 .= "\n";
                    $file2 .= "\t\t\t\$this->pushCriteria(app(RequestCriteria::class));";
                    $file2 .= "\n";
                    $file2 .= "\t\t}";
                    $file2 .= "\n";
                    $file2 .= "}";
                    $file2 .= "\n";
                    
                    if (!file_exists($dir . $file_name . "RepositoryEloquent.php")){
                        $fp2 = fopen($dir . $file_name . "RepositoryEloquent.php", "w+");
                        $escreve2 = fwrite($fp2, $file2);
                        fclose($fp2);
                        chmod($dir . $file_name . "RepositoryEloquent.php",0777);
                    }
                    
                }
            }
        }
    }

    public static function createController($generation){
                
        $dir = app_path()."/Http/Controllers/generator/";
        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }

        if ($generation){

            $function = new Functions();
            
            foreach($generation['schema']['class'] as $value){

                $nameFile = $value->table["name"];
                    
                $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                $nameClass = $function->getNameClass($nameFile,$fullname);
                $file_name = $nameClass;

                if (!file_exists($dir  . $file_name . "Controller.php")){   
                    
                    $file4 = "";
                    $file4 .= "<?php\n";
                    $file4 .= self::getHead();
                    $file4 .= "namespace ".$generation['head']['namespace']."\Http\Controllers;\n";
                    $file4 .= "\n";
                    $file4 .= "use Illuminate\Http\Request;\n";
                    $file4 .= "\n";
                    $file4 .= "use ".$generation['head']['namespace']."\Http\Requests;\n";
                    $file4 .= "use ".$generation['head']['namespace']."\Http\Controllers\Controller;\n";
                    $file4 .= "use ".$generation['head']['namespace']."\Repositories\\".$file_name."Repository;\n";
                    $file4 .= "use ".$generation['head']['namespace']."\Services\\".$file_name."Service;\n";
                    $file4 .= "use ".$generation['head']['namespace']."\\".$generation['head']['directory']."\\".$file_name.";\n";
                    $file4 .= "use Paulohsilvestre\GeneratorForLaravel\Utils\Functions;\n";
                    
                    if (@sizeof($value->table["foreign"]) > 0){
                        foreach($value->table["foreign"] as $fk){
                            $nameFK = $function->getNameClass($fk->referencetable,$fullname);
                            $file4 .= "use ".$generation['head']['namespace']."\\".$generation['head']['directory']."\\".$nameFK.";\n";
                            unset($fk);
                        }
                    }
                    

                    $file4 .= "\n";
                    $file4 .= "class ".$file_name."Controller extends Controller\n";
                    $file4 .= "{\n";
                    $file4 .= "\n";
                    $file4 .= "\t    /**\n";
                    $file4 .= "\t     * @var ".$file_name."Repository\n";
                    $file4 .= "\t     */\n";
                    $file4 .= "\t    private \$repository;\n";
                    $file4 .= "\n";
                    $file4 .= "\t    /**\n";
                    $file4 .= "\t     * @var ".$file_name."Service\n";
                    $file4 .= "\t     */\n";
                    $file4 .= "\t    private \$service;\n";
                    $file4 .= "\n";
                    $file4 .= "\n";
                    $file4 .= "\t    public function __construct(".$file_name."Repository \$repository, ".$file_name."Service \$service)\n";
                    $file4 .= "\t    {\n";
                    $file4 .= "\t\t        \$this->repository = \$repository;\n";
                    $file4 .= "\t\t        \$this->service = \$service;\n";
                    $file4 .= "\t    }\n";
                    $file4 .= "\n";
                    $file4 .= "\n";
                    $file4 .= "\t    /**\n";
                    $file4 .= "\t     * Display a listing of the resource.\n";
                    $file4 .= "\t     *\n";
                    $file4 .= "\t     * @return \Illuminate\Http\Response\n";
                    $file4 .= "\t     */\n";
                    $file4 .= "\t    public function index()\n";
                    $file4 .= "\t    {\n";
                    $file4 .= "\t\t        \$".strtolower($nameFile)." = ".$file_name."::all();\n";
	                $file4 .= "\t\t        return view('".strtolower($file_name).".".strtolower($file_name)."')->with('".strtolower($nameFile)."',\$".strtolower($nameFile).");\n";
                    $file4 .= "\t    }\n";
                    $file4 .= "\n";
                    
                    $file4 .= "\t\t/**\n";
                    $file4 .= "\t\t * EVENT FOR INSERT/UPDATE/DELETE CALL FORMS\n";
                    $file4 .= "\t\t */\n";
                    $file4 .= "\t\tpublic function add()\n";
                    $file4 .= "\t\t{\n";
                    
                    $text = "";
                    $descwith = "";
                    if (@sizeof($value->table["foreign"]) > 0){
                        $descwith .= "->with(";
                        $contfk = 0;
                        $descfk = "";
                        foreach($value->table["foreign"] as $fk){
                            $file4 .= "\t\t\t\$".strtolower($fk->referencetable)." = ".$function->getNameClass($fk->referencetable)."::all();\n";
                            if ($contfk >= 1){
                                $descfk .= ",";
                            }
                            $descfk .= "'".strtolower($fk->referencetable)."' => \$".strtolower($fk->referencetable)."";
                            $contfk++;
                            unset($fk);
                        }
                        if ($contfk >= 1){
                            $descwith .= "array(".$descfk.")";
                        }
                        $descwith .= ")";
                    }
                    
                    $text .= "\t\t\treturn view('".strtolower($file_name).".insert')".$descwith.";\n";
                    $file4 .= $text;
                    $file4 .= "\t\t}\n";
                    $file4 .= "\n";
                    
                    $file4 .= "\t\t/**\n";
                    $file4 .= "\t\t * VIEW FORM EDIT\n";
                    $file4 .= "\t\t */\n";
                    $file4 .= "\t\tpublic function edit(\$id)\n";
                    $file4 .= "\t\t{\n";
                    $file4 .= "\t\t\tif (Functions::validaId(\$id)){\n";
                    $file4 .= "\t\t\t\t \$cod = Functions::getId(\$id);\n";
                    $file4 .= "\t\t\t\t \$".strtolower($file_name)." = ".$file_name."::find(\$cod);\n";
                    
                    $desc_with = "'".strtolower($nameFile)."' => \$".strtolower($file_name)."";
                    
                    $text = "";
                    $descwith = "";
                    if (@sizeof($value->table["foreign"]) > 0){
                        $descwith .= "->with(";
                        $contfk = 0;
                        $descfk = "";
                        foreach($value->table["foreign"] as $fk){
                            $file4 .= "\t\t\t\t\$".strtolower($fk->referencetable)." = ".$function->getNameClass($fk->referencetable)."::all();\n";
                            if ($contfk >= 1){
                                $descfk .= ",";
                            }
                            $descfk .= "'".strtolower($fk->referencetable)."' => \$".strtolower($fk->referencetable)."";
                            $contfk++;
                            unset($fk);
                        }
                        if ($contfk >= 1){
                            $descwith .= "array(".$desc_with.",".$descfk.")";
                        } else {
                            $descwith .= "array(".$desc_with.")";
                        }
                        $descwith .= ")";
                    }
                    if (!$descwith){
                        $descwith .= "->with(array(".$desc_with."))";
                    } 

                    $text .= "\t\t\t\treturn view('".strtolower($file_name).".edit')".$descwith.";\n";
                    
                    $file4 .= $text;
                    $file4 .= "\t\t\t} else {\n";
                    $file4 .= "\t\t\t\tFunctions::writeLog(\"W\", \"ACCESS\", \"DENIED\", \"EDIT INVALID ID: \".__FILE__);\n";
                    $file4 .= "\t\t\t\treturn view('denied.denied');\n";
                    $file4 .= "\t\t\t}\n";
                    
                    $file4 .= "\t\t}\n";
                    $file4 .= "\n";
                    
                    $file4 .= "\t\t/**\n";
                    $file4 .= "\t\t * VIEW FORM DELETE\n";
                    $file4 .= "\t\t */\n";
                    $file4 .= "\t\tpublic function exclusion(\$id)\n";
                    $file4 .= "\t\t{\n";
                    
                    $file4 .= "\t\t\tif (Functions::validaId(\$id)){\n";
                    $file4 .= "\t\t\t\t \$cod = Functions::getId(\$id);\n";
                    
                    $file4 .= "\t\t\t\t\$".strtolower($file_name)." = ".$file_name."::find(\$cod);\n";
                    $file4 .= "\t\t\t\treturn view('".strtolower($file_name).".delete')->with('".strtolower($nameFile)."',\$".strtolower($file_name).");\n";
                    
                    $file4 .= "\t\t\t} else {\n";
                    $file4 .= "\t\t\t\tFunctions::writeLog(\"W\", \"ACCESS\", \"DENIED\", \"EDIT INVALID ID: \".__FILE__);\n";
                    $file4 .= "\t\t\t\treturn view('denied.denied');\n";
                    $file4 .= "\t\t\t}\n";
                    $file4 .= "\t\t}\n";
                    $file4 .= "\n";
                    $file4 .= "\t\t/**\n";
                    $file4 .= "\t\t * END EVENT FORMS\n";
                    $file4 .= "\t\t */\n";
                    $file4 .= "\t\tpublic function store(Request \$request)\n";
                    $file4 .= "\t\t{\n";
                    $file4 .= "\t\t\treturn \$this->service->create(\$request->all());\n";
                    $file4 .= "\t\t}\n";
                    $file4 .= "\n";
                    $file4 .= "\t\t/**\n";
                    $file4 .= "\t\t* Display the specified resource.\n";
                    $file4 .= "\t\t* @param  int  \$id\n";
                    $file4 .= "\t\t* @return \Illuminate\Http\Response\n";
                    $file4 .= "\t\t*/\n";
                    $file4 .= "\t\tpublic function show(\$id)\n";
                    $file4 .= "\t\t{\n";
                    $file4 .= "\t\t\treturn \$this->service->get".$file_name."(\$id);\n";
                    $file4 .= "\t\t}\n";
                    $file4 .= "\n";
                    $file4 .= "\t\t/**\n";
                    $file4 .= "\t\t* Update the specified resource in storage.\n";
                    $file4 .= "\t\t* @param  \Illuminate\Http\Request  \$request\n";
                    $file4 .= "\t\t* @param  int  \$id\n";
                    $file4 .= "\t\t* @return \Illuminate\Http\Response\n";
                    $file4 .= "\t\t*/\n";
                    $file4 .= "\t\tpublic function update(Request \$request, \$id)\n";
                    $file4 .= "\t\t{\n";
                    $file4 .= "\t\t\treturn \$this->service->update(\$request->all(), \$id);\n";
                    $file4 .= "\t\t}\n";
                    $file4 .= "\n";
                    $file4 .= "\t\t/**\n";
                    $file4 .= "\t\t* Remove the specified resource from storage.\n";
                    $file4 .= "\t\t* @param  int  \$id\n";
                    $file4 .= "\t\t* @return \Illuminate\Http\Response\n";
                    $file4 .= "\t\t*/\n";
                    $file4 .= "\t\tpublic function destroy(\$id)\n";
                    $file4 .= "\t\t{\n";
                    $file4 .= "\t\t\t\$return = \$this->service->destroy(\$id);\n";
                    $file4 .= "\t\t\t\$".strtolower($file_name)." = ".$file_name."::find(\$id);\n";
                    $file4 .= "\t\t\tif (\$".strtolower($file_name)." == null){\n";
                    $file4 .= "\t\t\t\treturn [\n";
                    $file4 .= "\t\t\t\t\t'error'=> false,\n";
                    $file4 .= "\t\t\t\t\t'message'=> 'Registro Excluído'\n";
                    $file4 .= "\t\t\t\t];\n";
                    $file4 .= "\t\t\t} else {\n";
                    $file4 .= "\t\t\t\treturn [\n";
                    $file4 .= "\t\t\t\t\t'error'=> true,\n";
                    $file4 .= "\t\t\t\t\t'message'=> 'Erro ao Excluir Registro'\n";
                    $file4 .= "\t\t\t\t];\n";
                    $file4 .= "\t\t\t}\n";
                    $file4 .= "\t\t}\n";
                    $file4 .= "\n";
                    $file4 .= "}\n";
                    
                    $fp4 = fopen($dir . $file_name . "Controller.php", "w+");
                    $escreve2 = fwrite($fp4, $file4);
                    fclose($fp4);
                    chmod($dir . $file_name . "Controller.php",0777);
                 }
                    
            }
        }
    }

    public static function createServices($generation){
        
        $dir = app_path()."/Services/";
        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }

        if ($generation){

            $function = new Functions();
            
            foreach($generation['schema']['class'] as $value){
                
                $nameFile = $value->table["name"];    
                $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                $nameClass = $function->getNameClass($nameFile,$fullname);
                $file_name = $nameClass;

                if (!file_exists($dir."".$nameClass.".php")){
                    
                    $file3 = "";
                    $file3 .= "<?php\n";
                    $file3 .= self::getHead();
                    $file3 .= "\n";
                    $file3 .= "namespace ".$generation['head']['namespace']."\Services;\n";
                    $file3 .= "\n";
                    $file3 .= "\n";
                    $file3 .= "use ".$generation['head']['namespace']."\Repositories\\".$file_name."Repository;\n";
                    $file3 .= "use ".$generation['head']['namespace']."\Validators\\".$file_name."Validator;\n";
                    $file3 .= "use Prettus\Validator\Exceptions\ValidatorException;\n";
                    $file3 .= "use Symfony\Component\HttpKernel\Client;\n";
                    $file3 .= "\n";
                    $file3 .= "class ".$file_name."Service\n";
                    $file3 .= "{\n";
                    $file3 .= "\n";
                    $file3 .= "\t\t/**\n";
                    $file3 .= "\t\t* @var ".$file_name."Repository\n";
                    $file3 .= "\t\t*/\n";
                    $file3 .= "\t\tprotected \$repository;\n";
                    $file3 .= "\n";
                    $file3 .= "\t\t/**\n";
                    $file3 .= "\t\t* @var ".$file_name."Validator\n";
                    $file3 .= "\t\t*/\n";
                    $file3 .= "\t\tprotected \$validator;\n";
                    $file3 .= "\n";
                    $file3 .= "\t\tpublic function __construct(".$file_name."Repository \$repository, ".$file_name."Validator \$validator)\n";
                    $file3 .= "\t\t{\n";
                    $file3 .= "\t\t\t\$this->repository = \$repository;\n";
                    $file3 .= "\t\t\t\$this->validator = \$validator;\n";
                    $file3 .= "\t\t}\n";
                    $file3 .= "\n";
                    $file3 .= "\t\tpublic function create(array \$data)\n";
                    $file3 .= "\t\t{\n";
                    $file3 .= "\n";
                    $file3 .= "\t\t\t    try {\n";
                    $file3 .= "\t\t\t\t        \$this->validator->with(\$data)->passesOrFail();\n";
                    $file3 .= "\t\t\t\t        return \$this->repository->create(\$data);\n";
                    $file3 .= "\t\t\t    } catch (ValidatorException \$ex){\n";
                    $file3 .= "\t\t\t\t        return [\n";
                    $file3 .= "\t\t\t\t\t            'error'=> true,\n";
                    $file3 .= "\t\t\t\t\t            'message'=> \$ex->getMessageBag()\n";
                    $file3 .= "\t\t\t\t        ];\n";
                    $file3 .= "\t\t\t    }\n";
                    $file3 .= "\n\n";
                    $file3 .= "\t\t}\n";
                    $file3 .= "\n";
                    $file3 .= "\n";

                    $file3 .= "\t\tpublic function update(array \$data, \$id)\n";
                    $file3 .= "\t\t{\n";
                    $file3 .= "\n";
                    $file3 .= "\t\t\t    try {\n";
                    $file3 .= "\t\t\t\t\$".strtolower($file_name)." = \$this->repository->findWhere(['id'=> \$id])->first();\n";
                    $file3 .= "\t\t\t\tif (\$".strtolower($file_name)."){\n";
                    $file3 .= "\t\t\t\t        \$this->validator->with(\$data)->passesOrFail();\n";
                    $file3 .= "\t\t\t\t        \$this->repository->update(\$data, \$id);\n";
                    $file3 .= "\t\t\t\t        return self::get".$file_name."(\$id);\n";
                    $file3 .= "\t\t\t\t} else {\n";
                    $file3 .= "\t\t\t\t        return [\n";
                    $file3 .= "\t\t\t\t\t            'error'=> true,\n";
                    $file3 .= "\t\t\t\t\t            'message'=> 'Registro não Localizado para Alteração'\n";
                    $file3 .= "\t\t\t\t        ];\n";
                    $file3 .= "\t\t\t\t}\n";
                    $file3 .= "\t\t\t    } catch (ValidatorException \$ex){\n";
                    $file3 .= "\t\t\t\t        return [\n";
                    $file3 .= "\t\t\t\t\t            'error'=> true,\n";
                    $file3 .= "\t\t\t\t\t            'message'=> \$ex->getMessageBag()\n";
                    $file3 .= "\t\t\t\t        ];\n";
                    $file3 .= "\t\t\t    }\n";
                    $file3 .= "\n";
                    $file3 .= "\t\t}\n";
                    $file3 .= "\n";
                    $file3 .= "\n";
                    $file3 .= "\t\tpublic function destroy(\$id)\n";
                    $file3 .= "\t\t{\n";
                    $file3 .= "\n";
                    $file3 .= "\t\t\t    try {\n";
                    $file3 .= "\t\t\t\t\$".strtolower($file_name)." = \$this->repository->findWhere(['id'=> \$id])->first();\n";
                    $file3 .= "\t\t\t\tif (\$".strtolower($file_name)."){\n";
                    $file3 .= "\t\t\t\t        return \$this->repository->delete(\$id);\n";
                    $file3 .= "\t\t\t\t} else {\n";
                    $file3 .= "\t\t\t\t        return [\n";
                    $file3 .= "\t\t\t\t\t            'error'=> true,\n";
                    $file3 .= "\t\t\t\t\t            'message'=> 'Registro não Localizado para Exclusão'\n";
                    $file3 .= "\t\t\t\t        ];\n";
                    $file3 .= "\t\t\t\t}\n";
                    $file3 .= "\t\t\t    } catch (ValidatorException \$ex){\n";
                    $file3 .= "\t\t\t\t        return [\n";
                    $file3 .= "\t\t\t\t\t            'error'=> true,\n";
                    $file3 .= "\t\t\t\t\t            'message'=> \$ex->getMessageBag()\n";
                    $file3 .= "\t\t\t\t        ];\n";
                    $file3 .= "\t\t\t    }\n";
                    $file3 .= "\n";
                    $file3 .= "\t\t}\n";
                    $file3 .= "\n";
                    $file3 .= "\n";
                    $file3 .= "\t\tpublic function get".$file_name."(\$id = null)\n";
                    $file3 .= "\t\t{\n";
                    $file3 .= "\t\t\t    if (\$id)\n";
                    $file3 .= "\t\t\t    {\n";
                    $file3 .= "\t\t\t\t try {\n";
                    $file3 .= "\t\t\t\t\t \$".strtolower($file_name)." = \$this->repository->find(\$id);\n";
                    $file3 .= "\t\t\t\t\t    return \$".strtolower($file_name).";\n";
                    $file3 .= "\t\t\t\t } catch (\Exception \$ex){\n";
                    $file3 .= "\t\t\t\t\t    return [\n";
                    $file3 .= "\t\t\t\t\t\t        'error'=> true,\n";
                    $file3 .= "\t\t\t\t\t\t        'message'=>'Erro ao buscar Registro'\n";
                    $file3 .= "\t\t\t\t\t    ];\n";
                    $file3 .= "\t\t\t\t}\n";
                    $file3 .= "\t\t\t    } else {\n";
                    $file3 .= "\t\t\t\t        return \$this->repository->all();\n";
                    $file3 .= "\t\t\t    }\n";
                    $file3 .= "\n";
                    $file3 .= "\t\t}\n";
                    $file3 .= "\n";
                    $file3 .= "\n";
                    $file3 .= "}\n";

                    if (!file_exists($dir . $file_name . "Service.php")){
                        $fp3 = fopen($dir . $file_name . "Service.php", "w+");
                        $escreve2 = fwrite($fp3, $file3);
                        fclose($fp3);
                        chmod($dir . $file_name . "Service.php",0777);
                    }
                    
                }
            }
        }
    }

    public static function createRepositories($generation){
        
        $dir = app_path()."/Repositories/";

        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }

        if ($generation){

            $function = new Functions();
            
            foreach($generation['schema']['class'] as $value){

                $nameFile = $value->table["name"];    
                $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                $nameClass = $function->getNameClass($nameFile,$fullname);
                $file_name = $nameClass;
                
                if (!file_exists($dir.$nameClass.".php")){
                    
                    $file = "";
                    $file .= "<?php \n";
                    $file .= self::getHead();
                    $file .= "namespace ".$generation['head']['namespace']."\Repositories;\n";
                    $file .= "\n";
                    $file .= "use Prettus\Repository\Contracts\RepositoryInterface;\n";
                    $file .= "\n";
                    $file .= "/**\n";
                    $file .= " * Interface " . $file_name . "RepositoryRepository\n";
                    $file .= " * @package namespace \\".$generation['head']['namespace']."\RepositoriesRepositories;\n";
                    $file .= " */\n";
                    $file .= "\n";
                    $file .= "interface " . $file_name . "Repository extends RepositoryInterface\n";
                    $file .= "\n";
                    $file .= "{\n";
                    $file .= "}\n";

                    if (!file_exists($dir . $file_name . "Repository.php")){
                        $fp = fopen($dir . $file_name . "Repository.php", "w+");
                        $escreve = fwrite($fp, $file);
                        fclose($fp);
                        chmod($dir . $file_name . "Repository.php",0777);
                    }
                    
                }
            }
        }
    }

    public static function createValidators($generation){
        
        $dir = app_path()."/Validators/";

        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }

        if ($generation){

            $function = new Functions();
            
            foreach($generation['schema']['class'] as $value){
                
                $nameFile = $value->table["name"];    
                $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                $nameClass = $function->getNameClass($nameFile,$fullname);
                $file_name = $nameClass;
                
                if (!file_exists($dir.$nameClass.".php")){
                    
                    $file2 = "";
                    $file2 .= "<?php\n";
                    $file2 .= self::getHead();
                    $file2 .= " namespace ".$generation['head']['namespace']."\Validators;\n";
                    $file2 .= "\n";
                    $file2 .= "use Prettus\Validator\LaravelValidator;";
                    $file2 .= "\n";
                    $file2 .= "\n";
                    $file2 .= "class ".$file_name."Validator extends LaravelValidator\n";
                    $file2 .= "{\n";
                    $file2 .= "\t\tprotected \$rules = [\n";

                    foreach($value->table["fields"] as $field){
                        $desc = "";
                        if ($value->table["primary"] != $field->name){
                            if ($field->null == "N"){
                                $desc .= "required";
                            }
                            if (@$field->attributes->max > 0){
                                if (strlen($desc)>0){
                                    $desc .= "|";
                                }
                                $desc .= "max:".$field->attributes->max;
                            }

                            if (@$field->attributes->min > 0){
                                if (strlen($desc)>0){
                                    $desc .= "|";
                                }
                                $desc .= "min:".$field->attributes->min;
                            }
                            $file2 .= "\t\t\t'".$field->name."' => '".$desc."',\n";
                        }
                        unset($field);
                    }
                   $file2 .= "\t\t];\n";
                   $file2 .= "}\n";

                    if (!file_exists($dir . $file_name . "Validator.php")){
                        $fp2 = fopen($dir . $file_name . "Validator.php", "w+");
                        $escreve2 = fwrite($fp2, $file2);
                        fclose($fp2);
                        chmod($dir . $file_name . "Validator.php",0777);
                    }
                    
                }
            }
        }
    }

    public static function createRouter($generation){
        
        $dir = app_path();

        $directory = $dir."/".$generation['head']['directory'];
        $diretorio = dir($directory);
        $string = "";

        $file5 = "";
        $file5 .= "<?php\n";
        $file5 .= self::getHead();
        $file5 .= "namespace ".$generation['head']['namespace']."\Providers;\n";
        $file5 .= "\n";
        $file5 .= "use Illuminate\Support\ServiceProvider;\n";
        $file5 .= "\n";
        $file5 .= "class ".$generation['head']['namespace']."RepositoryProvider extends ServiceProvider\n";
        $file5 .= "{\n";
        $file5 .= "\t    /**\n";
        $file5 .= "\t     * Bootstrap the application services.\n";
        $file5 .= "\t     *\n";
        $file5 .= "\t     * @return void\n";
        $file5 .= "\t     */\n";
        $file5 .= "\t    public function boot()\n";
        $file5 .= "\t    {\n";
        $file5 .= "\t\t\n";
        $file5 .= "\t   }\n";
        $file5 .= "\n";
        $file5 .= "\t    /**\n";
        $file5 .= "\t     * Register the application services.\n";
        $file5 .= "\t     *\n";
        $file5 .= "\t    * @return void\n";
        $file5 .= "\t     */\n";
        $file5 .= "\t    public function register()\n";
        $file5 .= "\t    {\n";
        $file5 .= "\t\t        /**\n";
        $file5 .= "\t\t         * REGISTRAR TODOS OS REPOSITORIES PARA QUE SEJAM CHAMADOS\n";
        $file5 .= "\t\t         */\n";

        while($arquivo = $diretorio -> read()) {
            if (($arquivo != ".") && ($arquivo != "..")) {
                $file_name = explode(".php", $arquivo);
                $file_name = $file_name[0];
                $file5 .= "\t\t\$this->app->bind(\\" . $generation['head']['namespace'] . "\Repositories\\".$file_name."Repository::class,\\" . $generation['head']['namespace'] . "\Repositories\Eloquent\\".$file_name."RepositoryEloquent::class);\n";
            }
        }

        $file5 .= "\t    }\n";
        $file5 .= "}\n";

        if (file_exists($dir . "/Providers/".$generation['head']['namespace']."RepositoryProvider.php")){
            unlink($dir . "/Providers/".$generation['head']['namespace']."RepositoryProvider.php");
        }
        $fp4 = fopen($dir . "/Providers/".$generation['head']['namespace']."RepositoryProvider.php", "a+");
        $escreve2 = fwrite($fp4, $file5);
        fclose($fp4);
        $diretorio->close();

        $web_route = base_path()."/routes/web.php";
        $get_content = file_get_contents($web_route);   

        $contains = (strpos($get_content,"####ROUTEADD") >=0) ? true : false;

        $file = "";
        $file .=  "<?php\n";
        $file .= self::getHead();

        $file_route = "";
        if (@$generation['head']['resource'] == "Y"){
            $add_str = "Route::resource('".strtolower($value->table["name"])."','".$file_name."Controller', ['except' => ['create']]);\n";
            $valid_str = "Route::resource('".strtolower($value->table["name"])."','".$file_name."Controller',";
            if ($contains){
                if (strpos($get_content, $valid_str) < 0){
                    $file_route .= $add_str;
                }
            } 
            $file .= $add_str;
            
        } else {

            $file .=  "###GERAÇÃO AUTOMÁTICA BASEADO NAS ENTITIES\n";
            $directory = $dir."/".$generation['head']['directory'];
            $diretorio = dir($directory);
            if (sizeof($generation['schema']['class']) > 0){
                
                $function = new Functions();
                
                foreach($generation['schema']['class'] as $value){
                    
                    $nameFile = $value->table["name"];    
                    $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                    $nameClass = $function->getNameClass($nameFile,$fullname);
                    $file_name = $nameClass;
                    
                    $valid_str = "Route::get('".strtolower($nameFile)."/delete/{id}','".$file_name."Controller@exclusion');";
                    if ($contains){
                        if (strpos($get_content, $valid_str) == false){
                            $file_route .= "Route::get('".strtolower($nameFile)."/delete/{id}','".$file_name."Controller@exclusion');\n";
                            $file_route .= "Route::delete('".strtolower($nameFile)."/delete/{id}','".$file_name."Controller@destroy');\n";
                            $file_route .= "Route::get('".strtolower($nameFile)."/edit/{id}','".$file_name."Controller@edit');\n";
                            $file_route .= "Route::put('".strtolower($nameFile)."/update/{id}','".$file_name."Controller@update');\n";
                            $file_route .= "Route::get('".strtolower($nameFile)."/add','".$file_name."Controller@add');\n";
                            $file_route .= "Route::post('".strtolower($nameFile)."','".$file_name."Controller@store');\n";
                            $file_route .= "Route::get('".strtolower($nameFile)."','".$file_name."Controller@index');\n";
                            $file_route .= "\n";
                        }
                    } 

                    $file .= "Route::get('".strtolower($nameFile)."/delete/{id}','".$file_name."Controller@exclusion');\n";
                    $file .= "Route::delete('".strtolower($nameFile)."/delete/{id}','".$file_name."Controller@destroy');\n";
                    $file .= "Route::get('".strtolower($nameFile)."/edit/{id}','".$file_name."Controller@edit');\n";
                    $file .= "Route::put('".strtolower($nameFile)."/update/{id}','".$file_name."Controller@update');\n";
                    $file .= "Route::get('".strtolower($nameFile)."/add','".$file_name."Controller@add');\n";
                    $file .= "Route::post('".strtolower($nameFile)."','".$file_name."Controller@store');\n";
                    $file .= "Route::get('".strtolower($nameFile)."','".$file_name."Controller@index');\n";
                    $file .= "\n";
                    unset($value);
                }
            }

            //$file .=  "//}\n";
            //$file .=  "###FIM DO EXEMPLO OAUTH\n";

            $app_route = base_path();
            @unlink($app_route . "/routes/generate.php");
            $fp = fopen($app_route . "/routes/generate.php", "w+");
            $escreve2 = fwrite($fp, $file);
            fclose($fp);
            chmod($app_route . "/routes/generate.php",0777);
            
            if ($contains && $file_route){
                $file_route .= "####ROUTEADD\n";
                $get_content = str_ireplace("####ROUTEADD",$file_route, $get_content);
                $handle = fopen($web_route, 'w') or die('Cannot open file:  '.$web_route);
                fwrite($handle, $get_content);
                fclose($handle);
            }
        }
    }

    public static function createProvider($generation){}

    public static function createHtml($generation){}

}