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

            self::createIndex($generation);
            self::createTranslate($generation);
            self::createMigration($generation);
            self::createEntities($generation);
            self::createFields($generation);
            self::createEloquent($generation);
            self::createController($generation);
            self::createServices($generation);
            self::createRepositories($generation);
            self::createValidators($generation);
            self::createRouter($generation);
            self::createHtml($generation);
            self::createProvider($generation);
            self::createMenu($generation);
            self::createTranslateMenu($generation);

            
        } else {
            return \Response::json('DATA INVALID!!!', 500);
        }

    }

    public static function createTranslate($generation){
        
        $dir = base_path() . "/config/";
        
        $nameFile = "translate";

        $function = new Functions();

        $name_file_open = $dir . $nameFile . ".php";
        if (file_exists($name_file_open)){

            $dd = include($name_file_open);
            
            foreach($dd as $key => $vl){
                if ($generation){ 
                   foreach($generation['schema']['class'] as $value){

                       if (array_key_exists($value->table['name'], $dd)){
                            if ($key == $value->table['name']){
                                $tmField = sizeof($value->table["fields"]);
                                if ($tmField > 0){
                                    foreach($value->table["fields"] as $field){
                                        if (array_key_exists($field->name,$vl)){
                                            $dd[$key][$field->name] = $vl[$field->name];
                                        } else {
                                            $_fieldValue = $field->name;
                                            if (@$field->attributes){
                                                if (@$field->attributes->translate != ""){
                                                    $_fieldValue = $field->attributes->translate;
                                                }
                                            }
                                            $dd[$key][$field->name] = $_fieldValue;
                                        }
                                        unset($field);
                                    }
                                }
                            }
                        } else {
                            $tmField = sizeof($value->table["fields"]);
                            if ($tmField > 0){
                                foreach($value->table["fields"] as $field){
                                    $_fieldValue = $field->name;
                                    if (@$field->attributes){
                                        if (@$field->attributes->translate != ""){
                                            $_fieldValue = $field->attributes->translate;
                                        }
                                    }
                                    $dd[$value->table['name']][$field->name] = $_fieldValue;
                                    unset($field);
                                }
                            }
                        }
                       unset($value);
                   }
                }
                unset($vl);
            }

            $str = "";
            $str .= "<?php\n";
            $str .= self::getHead();
            if ($dd){

                $str .= "\t\treturn [\n";
                $size = sizeof($dd);
                $cont = 1;
                if (sizeof($dd)>0){
                    foreach($dd as $key => $value){
                        $str .= "\t\t\t'".$key."' => [\n";
                        foreach($value as $vl => $cp){
                            $str .= "\t\t\t\t'".$vl."'=>'".$cp."',\n";
                            unset($cp);
                        }
                        $str .= "\t\t\t]";
                        if ($size != $cont){
                            $str .= ",\n";
                        }
                        $cont++;
                        unset($value);
                    }
                }
                $str .= "\n";
                $str .= "\t\t];\n";

                if (file_exists($dir . $nameFile . ".php")){
                    $fp4 = fopen($dir . $nameFile . ".php", "w+");
                    $escreve2 = fwrite($fp4, $str);
                    fclose($fp4);
                }

            }

        } else {

            $str = "";
            $str .= "<?php\n";
            $str .= self::getHead();
            $arrayDados = array();
            if ($generation){

                foreach($generation['schema']['class'] as $value){

                    $tmField = sizeof($value->table["fields"]);
                    if ($tmField > 0){
                        foreach($value->table["fields"] as $field){
                            if (!in_array($field->name,$arrayDados)){
                                $_fieldValue = $field->name;
                                $arrayDados[$value->table['name']][] = $_fieldValue;
                            }
                            unset($field);
                        }
                    }
                    unset($value);
                }

                $str .= "\t\treturn [\n";
                $size = sizeof($generation['schema']['class']);
                $cont = 1;
                if (sizeof($arrayDados)>0){
                    foreach($generation['schema']['class'] as $value){
                        $str .= "\t\t\t'".$value->table['name']."' => [\n";
                        //dd($arrayDados);
                        foreach($arrayDados[$value->table['name']] as $cp){
                            $vl_translate = $cp;
                            foreach($value->table["fields"] as $field){
                                if ($field->name == $cp) {
                                    if (@$field->attributes){
                                        if (@$field->attributes->translate != ""){
                                            $vl_translate = $field->attributes->translate;
                                            break;
                                        }
                                    }
                                }
                            }
                            $str .= "\t\t\t\t'".$cp."'=>'".$vl_translate."',\n";
                            unset($cp);
                        }
                        $str .= "\t\t\t]";
                        if ($size != $cont){
                            $str .= ",\n";
                        }
                        $cont++;
                        unset($value);
                    }
                }
                $str .= "\n";
                $str .= "\t\t];\n";

                if (!file_exists($dir . $nameFile . ".php")){
                    $fp4 = fopen($dir . $nameFile . ".php", "w+");
                    $escreve2 = fwrite($fp4, $str);
                    fclose($fp4);
                }

            }
        }


        $str = "";
        $str .= "<?php\n";
        $str .= self::getHead();
        $str .= "return [\n";
        $str .= "\t'app_version' => '1.0.0.0a',\n";
        $str .= "\t'app_name' => ' APPNAME',\n";
        $str .= "\t'buttonCancel' => 'Cancelar',\n";
        $str .= "\t'buttonConfirm' => 'Confirmar',\n";
        $str .= "\t'buttonAdd' => 'Adicionar',\n";
        $str .= "\t'buttonClose' => 'Fechar',\n";
        $str .= "\t'altEdit' => 'Editar',\n";
        $str .= "\t'altRemove' => 'Remover',\n";
        $str .= "\t'altList' => 'Listar',\n";
        $str .= "\t'altAdd' => 'Adicionar Registro',\n";
        $str .= "\t'altOption' => 'Opções',\n";
        $str .= "\t'altNavigation' => 'Navigation',\n";
        $str .= "\t'altExit' => 'Exit',\n";
        $str .= "\t'altSearch' => 'Pesquisar',\n";
        $str .= "\t'altMenu' => 'Menu',\n";
        $str .= "\t'altCopyright' => '2017. Direitos Reservados ',\n";
        $str .= "\t'titleAdd' => 'Inclusão de Registro',\n";
        $str .= "\t'titleEdit' => 'Edição de Registro',\n";
        $str .= "\t'titleRemove' => 'Exclusão de Registro',\n";
        $str .= "\t'titleConfirmRemove' => 'Confirma a Exclusão do Registro?',\n";
        $str .= "\t'titleReport' => 'Relatório',\n";
        $str .= "\t'selectOption' => 'Selecione um(a)',\n";
        $str .= "\t'selectComp' => ' Opção',\n";
        $str .= "\n";
        $str .= "];\n";

        if (!file_exists($dir . "options.php")){
            $fp4 = fopen($dir . "options.php", "w+");
            $escreve2 = fwrite($fp4, $str);
            fclose($fp4);
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
                        $str .= "namespace ".$generation['head']['namespace']."\\".$generation['head']['directory'].";\n\n";
                        $str .= self::getHead();
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
                        $str .= "\t\t\t\$fields = include('field/fields_".strtolower($nameClass). ".php"."');\n";
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

                }

            }

    }

    public static function createFields($generation){
        
        $dir_field = app_path()."/".$generation['head']['directory']."/field/";
        if (!file_exists($dir_field)){
            mkdir($dir_field, 0777, true);
        }

        if ($generation){
            
            $function = new Functions();
            
            foreach($generation['schema']['class'] as $value){
                
                $nameFile = $value->table["name"];
                
                $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                $nameClass = $function->getNameClass($nameFile,$fullname);
                
                $tmField = @sizeof($value->table["fields"]);
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

                    $file_field = fopen($dir_field . "fields_".strtolower($nameClass). ".php", "w+");
                    $escreve2 = fwrite($file_field, $_str);
                    fclose($file_field);
                    chmod($dir_field . "fields_".strtolower($nameClass). ".php",0777);

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
                
                if (!$function->fileExistsContent($dir, "_create_".strtolower($nameFile)."_table")){
                
                    $str = "";
                    $str .= "<?php\n\n";
                    $str .= self::getHead();
                    $str .= "use Illuminate\Database\Schema\Blueprint;\n";
                    $str .= "use Illuminate\Database\Migrations\Migration;\n";

                    if (str_contains($nameFile, "_")){
                        $class_name = explode("_",$nameFile);
                        $nameClass = $function->getNameClassFirstUpperCase($class_name[0]).$function->getNameClassFirstUpperCase($class_name[1]);
                    }
                    
                    $str .= "class Create".$function->getNameClassFirstUpperCase($nameFile)."Table extends Migration\n";
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
                        
                        if (@sizeof($value->table['foreign']) > 0){
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
                    $file_name = date('Y')."_".date('m')."_".date('d')."_".date('Hmisu').$sequence."_create_".strtolower($nameFile)."_table.php";
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
                            $file_name = date('Y')."_".date('m')."_".date('d')."_".date('Hmisu').$sequence."_addField".$nameclass_alter.strtolower($nameClass)."_table.php";
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
                    $file2 .= "<?php\n";
                    $file2 .= " namespace ".$generation['head']['namespace']."\Repositories\Eloquent;";
                    $file2 .= "\n";
                    $file2 .= self::getHead();
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

    public static function createIndex($generation){
        
        $str = '<!DOCTYPE html>
        <html lang="en">
        
          <head>
        
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <meta name="description" content="">
            <meta name="author" content="">
        
            <title>GENERATORFORLARAVEL</title>
        
            <!-- Bootstrap core CSS-->
            <!-- <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"> -->
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        
            <!-- Custom fonts for this template-->
            <!-- <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css"> -->
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
        
            <!-- Page level plugin CSS-->
            <!-- <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet"> -->
            <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" crossorigin="anonymous">
            
            <!-- Custom styles for this template-->
            <!-- <link href="css/sb-admin.css" rel="stylesheet"> -->
            <style>
                  /*!
                  * Start Bootstrap - SB Admin v5.0.2 (https://startbootstrap.com/template-overviews/sb-admin)
                  * Copyright 2013-2018 Start Bootstrap
                  * Licensed under MIT (https://github.com/BlackrockDigital/startbootstrap-sb-admin/blob/master/LICENSE)
                  */html{position:relative;min-height:100%}body{height:100%}#wrapper{display:-webkit-box;display:-ms-flexbox;display:flex}#wrapper #content-wrapper{overflow-x:hidden;width:100%;padding-top:1rem;padding-bottom:80px}body.fixed-nav #content-wrapper{margin-top:56px;padding-left:90px}body.fixed-nav.sidebar-toggled #content-wrapper{padding-left:0}@media (min-width:768px){body.fixed-nav #content-wrapper{padding-left:225px}body.fixed-nav.sidebar-toggled #content-wrapper{padding-left:90px}}.scroll-to-top{position:fixed;right:15px;bottom:15px;display:none;width:50px;height:50px;text-align:center;color:#fff;background:rgba(52,58,64,.5);line-height:46px}.scroll-to-top:focus,.scroll-to-top:hover{color:#fff}.scroll-to-top:hover{background:#343a40}.scroll-to-top i{font-weight:800}.smaller{font-size:.7rem}.o-hidden{overflow:hidden!important}.z-0{z-index:0}.z-1{z-index:1}.navbar-nav .form-inline .input-group{width:100%}.navbar-nav .nav-item.active .nav-link{color:#fff}.navbar-nav .nav-item.dropdown .dropdown-toggle::after{width:1rem;text-align:center;float:right;vertical-align:0;border:0;font-weight:900;content:"\f105";font-family:"Font Awesome 5 Free"}.navbar-nav .nav-item.dropdown.show .dropdown-toggle::after{content:"\f107"}.navbar-nav .nav-item.dropdown.no-arrow .dropdown-toggle::after{display:none}.navbar-nav .nav-item .nav-link:focus{outline:0}.navbar-nav .nav-item .nav-link .badge{position:absolute;margin-left:.75rem;top:.3rem;font-weight:400;font-size:.5rem}@media (min-width:768px){.navbar-nav .form-inline .input-group{width:auto}}.sidebar{width:90px!important;background-color:#212529;min-height:calc(100vh - 56px)}.sidebar .nav-item:last-child{margin-bottom:1rem}.sidebar .nav-item .nav-link{text-align:center;padding:.75rem 1rem;width:90px}.sidebar .nav-item .nav-link span{font-size:.65rem;display:block}.sidebar .nav-item .dropdown-menu{position:absolute!important;-webkit-transform:none!important;transform:none!important;left:calc(90px + .5rem)!important;margin:0}.sidebar .nav-item .dropdown-menu.dropup{bottom:0;top:auto!important}.sidebar .nav-item.dropdown .dropdown-toggle::after{display:none}.sidebar .nav-item .nav-link{color:rgba(255,255,255,.5)}.sidebar .nav-item .nav-link:active,.sidebar .nav-item .nav-link:focus,.sidebar .nav-item .nav-link:hover{color:rgba(255,255,255,.75)}.sidebar.toggled{width:0!important;overflow:hidden}@media (min-width:768px){.sidebar{width:225px!important}.sidebar .nav-item .nav-link{display:block;width:100%;text-align:left;padding:1rem;width:225px}.sidebar .nav-item .nav-link span{font-size:1rem;display:inline}.sidebar .nav-item .dropdown-menu{position:static!important;margin:0 1rem;top:0}.sidebar .nav-item.dropdown .dropdown-toggle::after{display:block}.sidebar.toggled{overflow:visible;width:90px!important}.sidebar.toggled .nav-item:last-child{margin-bottom:1rem}.sidebar.toggled .nav-item .nav-link{text-align:center;padding:.75rem 1rem;width:90px}.sidebar.toggled .nav-item .nav-link span{font-size:.65rem;display:block}.sidebar.toggled .nav-item .dropdown-menu{position:absolute!important;-webkit-transform:none!important;transform:none!important;left:calc(90px + .5rem)!important;margin:0}.sidebar.toggled .nav-item .dropdown-menu.dropup{bottom:0;top:auto!important}.sidebar.toggled .nav-item.dropdown .dropdown-toggle::after{display:none}}.sidebar.fixed-top{top:56px;height:calc(100vh - 56px);overflow-y:auto}.card-body-icon{position:absolute;z-index:0;top:-1.25rem;right:-1rem;opacity:.4;font-size:5rem;-webkit-transform:rotate(15deg);transform:rotate(15deg)}@media (min-width:576px){.card-columns{-webkit-column-count:1;column-count:1}}@media (min-width:768px){.card-columns{-webkit-column-count:2;column-count:2}}@media (min-width:1200px){.card-columns{-webkit-column-count:2;column-count:2}}:root{--input-padding-x:0.75rem;--input-padding-y:0.75rem}.card-login{max-width:25rem}.card-register{max-width:40rem}.form-label-group{position:relative}.form-label-group>input,.form-label-group>label{padding:var(--input-padding-y) var(--input-padding-x);height:auto}.form-label-group>label{position:absolute;top:0;left:0;display:block;width:100%;margin-bottom:0;line-height:1.5;color:#495057;border:1px solid transparent;border-radius:.25rem;-webkit-transition:all .1s ease-in-out;transition:all .1s ease-in-out}.form-label-group input::-webkit-input-placeholder{color:transparent}.form-label-group input:-ms-input-placeholder{color:transparent}.form-label-group input::-ms-input-placeholder{color:transparent}.form-label-group input::placeholder{color:transparent}.form-label-group input:not(:placeholder-shown){padding-top:calc(var(--input-padding-y) + var(--input-padding-y) * (2 / 3));padding-bottom:calc(var(--input-padding-y)/ 3)}.form-label-group input:not(:placeholder-shown)~label{padding-top:calc(var(--input-padding-y)/ 3);padding-bottom:calc(var(--input-padding-y)/ 3);font-size:12px;color:#777}footer.sticky-footer{display:-webkit-box;display:-ms-flexbox;display:flex;position:absolute;right:0;bottom:0;width:calc(100% - 90px);height:80px;background-color:#e9ecef}footer.sticky-footer .copyright{line-height:1;font-size:.8rem}@media (min-width:768px){footer.sticky-footer{width:calc(100% - 225px)}}body.sidebar-toggled footer.sticky-footer{width:100%}@media (min-width:768px){body.sidebar-toggled footer.sticky-footer{width:calc(100% - 90px)}}
            </style>
        
          </head>
        
          <body id="page-top">
        
            <nav class="navbar navbar-expand navbar-dark bg-dark static-top">
        
              <a class="navbar-brand mr-1" href="index.html">GeneratorForLaravel</a>
        
              <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
                <i class="fas fa-bars"></i>
              </button>
        
              <!-- Navbar Search -->
              <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
                <div class="input-group">
                  <input type="text" class="form-control" placeholder="Pesquisar..." aria-label="Pesquisar" aria-describedby="basic-addon2">
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="button">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>
              </form>
        
              <!-- Navbar -->
              <ul class="navbar-nav ml-auto ml-md-0">
                <li class="nav-item dropdown no-arrow mx-1">
                  <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell fa-fw"></i>
                    <span class="badge badge-danger">9+</span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="alertsDropdown">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Something else here</a>
                  </div>
                </li>
                <li class="nav-item dropdown no-arrow mx-1">
                  <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-envelope fa-fw"></i>
                    <span class="badge badge-danger">7</span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="messagesDropdown">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Something else here</a>
                  </div>
                </li>
                <li class="nav-item dropdown no-arrow">
                  <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user-circle fa-fw"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#">Settings</a>
                    <a class="dropdown-item" href="#">Activity Log</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">Logout</a>
                  </div>
                </li>
              </ul>
        
            </nav>
        
            <div id="wrapper">
        
              <!-- Sidebar -->
              <ul class="sidebar navbar-nav">
                    @include("layout.menu")
              </ul>
        
              <div id="content-wrapper">
        
                <div class="container-fluid">
        
                  <!-- Icon Cards-->
                  <div class="row">
                    <div class="col-xl-3 col-sm-6 mb-3">
                      <div class="card text-white bg-primary o-hidden h-100">
                        <div class="card-body">
                          <div class="card-body-icon">
                            <i class="fas fa-fw fa-comments"></i>
                          </div>
                          <div class="mr-5">26 New Messages!</div>
                        </div>
                        <a class="card-footer text-white clearfix small z-1" href="#">
                          <span class="float-left">View Details</span>
                          <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                          </span>
                        </a>
                      </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-3">
                      <div class="card text-white bg-warning o-hidden h-100">
                        <div class="card-body">
                          <div class="card-body-icon">
                            <i class="fas fa-fw fa-list"></i>
                          </div>
                          <div class="mr-5">11 New Tasks!</div>
                        </div>
                        <a class="card-footer text-white clearfix small z-1" href="#">
                          <span class="float-left">View Details</span>
                          <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                          </span>
                        </a>
                      </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-3">
                      <div class="card text-white bg-success o-hidden h-100">
                        <div class="card-body">
                          <div class="card-body-icon">
                            <i class="fas fa-fw fa-shopping-cart"></i>
                          </div>
                          <div class="mr-5">123 New Orders!</div>
                        </div>
                        <a class="card-footer text-white clearfix small z-1" href="#">
                          <span class="float-left">View Details</span>
                          <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                          </span>
                        </a>
                      </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-3">
                      <div class="card text-white bg-danger o-hidden h-100">
                        <div class="card-body">
                          <div class="card-body-icon">
                            <i class="fas fa-fw fa-life-ring"></i>
                          </div>
                          <div class="mr-5">13 New Tickets!</div>
                        </div>
                        <a class="card-footer text-white clearfix small z-1" href="#">
                          <span class="float-left">View Details</span>
                          <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                          </span>
                        </a>
                      </div>
                    </div>
                  </div>
        
                  <div class="row">
                    <div class="col-xl-12 col-sm-12 mb-12">
                      @yield("content")
                    </div>
                  </div>  
        
                </div>
                <!-- /.container-fluid -->
        
                <!-- Sticky Footer -->
                <footer class="sticky-footer">
                  <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                      <span>Copyright © Your Website 2018</span>
                    </div>
                  </div>
                </footer>
        
              </div>
              <!-- /.content-wrapper -->
        
            </div>
            <!-- /#wrapper -->
        
            <!-- Scroll to Top Button-->
            <a class="scroll-to-top rounded" href="#page-top">
              <i class="fas fa-angle-up"></i>
            </a>
        
        
            <!-- Logout Modal-->
            <div id="ModalForm" class="modal modal-default">
              <div class="modal-dialog modal-xs">
                <div class="modal-content" id="modalContent">
        
                </div>
              </div>
            </div>
        
            <!-- Bootstrap core JavaScript-->
            <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
          crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        
            <!-- Core plugin JavaScript-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
        
            <!-- Page level plugin JavaScript-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.js"></script>
            <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.js"></script>
            
            <script>
        
        
              (function($) {
                  "use strict"; // Start of use strict
        
                  // Toggle the side navigation
                  $("#sidebarToggle").on("click",function(e) {
                    e.preventDefault();
                    $("body").toggleClass("sidebar-toggled");
                    $(".sidebar").toggleClass("toggled");
                  });
        
                  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
                  $("body.fixed-nav .sidebar").on("mousewheel DOMMouseScroll wheel", function(e) {
                    if ($(window).width() > 768) {
                      var e0 = e.originalEvent,
                        delta = e0.wheelDelta || -e0.detail;
                      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
                      e.preventDefault();
                    }
                  });
        
                  // Scroll to top button appear
                  $(document).on("scroll",function() {
                    var scrollDistance = $(this).scrollTop();
                    if (scrollDistance > 100) {
                      $(".scroll-to-top").fadeIn();
                    } else {
                      $(".scroll-to-top").fadeOut();
                    }
                  });
        
                  // Smooth scrolling using jQuery easing
                  $(document).on("click", "a.scroll-to-top", function(event) {
                    var $anchor = $(this);
                    $("html, body").stop().animate({
                      scrollTop: ($($anchor.attr("href")).offset().top)
                    }, 1000, "easeInOutExpo");
                    event.preventDefault();
                  });
        
                })(jQuery); // End of use strict
        
                $(document).ready(function() {
                  $("#dataTable").DataTable();
        
                  $("body").on("click",".action_generator", function(){
        
                      var this_id = $(this).attr("data-id");
                      var this_modal = $(this).attr("data-target");
                      var this_url = $(this).attr("data-url");
                      var this_action = $(this).attr("data-action");
        
                      var return_form = ($(this).attr("return-form")  !== undefined) ? $(this).attr("return-form") : ""; 
                      var content = "";
                      if (return_form != ""){
                        content = $("#"+return_form);
                      }  else {
                        content = $("#modalContent");
                      }
        
                      var data_form = ($(this).attr("data-form")  !== undefined) ? $(this).attr("data-form") : ""; 
        
                      var url = "";
                      var loader = $("<img style=\"text-align=\'center\';\" src=\"https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif\" width=\"40px\" />");
                      
                      url = getUrl(this_id, this_url, this_action);
        
                      var action  = getAction(this_action);
        
                      var _dt = "";
                      if (data_form){
                        _dt = $("#"+data_form).serialize();
                      }
        
                      $.ajax({
                          url: url,
                          type: action,
                          data: _dt,
                          dataType: "html",
                          beforeSend: function(){
                            content.html("");
                            content.html(loader);
                          },
                          complete: function(){
                            loader.remove();
                          },
                          error: function() {
                            content.html("");
                            alert("Erro ao Carregar Formulario");
                          },
                          success: function(data) {
                            $(this_modal).modal("show");
                            content.html("");
                            content.html(data);
                          }
                      });
        
                  });
        
                });
        
            /**
            * MONTA A URL PARA RETORNAR APÓS O CLIQUE DO BOTÃO EM VÁRIAS TELAS, REMOVE OS CARACTERES NO INICIO E NO FIM
            * CRIADO ESPECIFICAMENTE PARA REMOVER A "/" DO INICIO E FINAL MONTAR A URL CORRETA
            * @param {*} id 
            * @param {*} url 
            * @param {*} action 
            * @param {*} directory 
            */
           function getUrl(id, url, action){
        
                var this_id = removeFirstLastPosition(removeFirstLastPosition(id, "L", "/"),"R","/");
                var this_url = removeFirstLastPosition(removeFirstLastPosition(url, "L", "/"),"R","/");;
                var this_action = removeFirstLastPosition(removeFirstLastPosition(action, "L", "/"),"R","/");
        
                var _url = "";
        
                if(this_action == "edit"){
                    _url = "/"+this_url+"/"+this_action+"/"+this_id;
                } else if(this_action == "add"){
                    _url = "/"+this_url+"/"+this_action+"";
                } else if(this_action == "delete"){
                    _url = "/"+this_url+"/"+this_action+"/"+this_id;
                } else if(this_action == "getdelete"){
                    _url = "/"+this_url+"/delete/"+this_id;
                } else if(this_action == "list"){
                    if (this_id != ""){
                        _url = "/"+this_url+"/"+this_id;
                    } else {
                        _url = "/"+this_url;
                    }
                } else if (this_action == "load"){
                  if (this_id != ""){
                    _url = "/"+this_url+"/"+this_id;
                  } else {
                    _url = "/"+this_url;
                  }
                } else if (this_action == "update"){
                    _url = "/"+this_url+"/"+this_action+"/"+this_id;
                } else if (this_action == "store"){
                    _url = "/"+this_url;
                } 
                return _url;
          }    
        
            /**
            * REMOVE O PRIMEIRO OU ULTIMO CARACTER DE UMA STRING FUNÇÃO CRIADA PARA CORREÇÃO DA URL
            * @param {*} str 
            * @param {*} pos 
            * @param {*} caracter 
            */
            function removeFirstLastPosition(str, pos, caracter){
              if (pos == "L"){
                if (str.substr(0, 1) == caracter){
                  return str.substring(1, str.length);
                } else {
                  return str;
                }
              } else {
                if (str.substr((str.length-1), 1) == caracter){
                  return str.substring(0, str.length-2);
                } else {
                  return str;
                }
              }
           }
        
            function getAction(action){
                if (action == "update"){
                  return "put";
                } else if (action == "store"){
                  return "post";
                } else if (action == "delete"){
                  return "delete";
                } else {
                  return "get";
                }
            }
        
            </script> 
        
          </body>
        
        </html>';

        $dir = base_path()."/resources/views/layout/";
        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }

        if (!file_exists($dir . "index.blade.php")){
            $fp3 = fopen($dir . "index.blade.php", "w+");
            $escreve2 = fwrite($fp3, $str);
            fclose($fp3);
            chmod($dir . "index.blade.php",0777);
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
                    $file3 .= "\n";
                    $file3 .= "namespace ".$generation['head']['namespace']."\Services;\n";
                    $file3 .= "\n";
                    $file3 .= self::getHead();
                    $file3 .= "\n";
                    $file3 .= "use ".$generation['head']['namespace']."\Repositories\\".$file_name."Repository;\n";
                    $file3 .= "use ".$generation['head']['namespace']."\Validators\\".$file_name."Validator;\n";
                    $file3 .= "use ".$generation['head']['namespace']."\\".$generation['head']['directory']."\\".$file_name.";\n";
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
                    $file3 .= "\t\t\ttry {\n";
                    $file3 .= "\t\t\t\t\$this->validator->with(\$data)->passesOrFail();\n";
                    $file3 .= "\t\t\t\t\$".strtolower($file_name)." = new ".$file_name."();\n";
					$file3 .= "\t\t\t\tif (\$data) {\n";
					$file3 .= "\t\t\t\t\t\$fields = include(app_path().\"/".$generation['head']['directory']."/field/fields_".strtolower($file_name).".php\");\n";
                    $file3 .= "\t\t\t\t\tforeach(\$data as \$ch => \$vl){\n";
                    $file3 .= "\t\t\t\t\t\tif (in_array(\$ch,\$fields)){\n";
                    $file3 .= "\t\t\t\t\t\t\t\$".strtolower($file_name)."->{\$ch} = \$vl;\n";
					$file3 .= "\t\t\t\t\t\t}\n";
                    $file3 .= "\t\t\t\t\tunset(\$vl);\n";
                    $file3 .= "\t\t\t\t\t}\n";
                    $file3 .= "\t\t\t\t}\n";
                    $file3 .= "\t\t\t\tif (\$".strtolower($file_name)."->save()){\n";
                    $file3 .= "\t\t\t\t\treturn ['error'=> false,'message'=> 'Gravado com sucesso'];	\n";
                    $file3 .= "\t\t\t\t} else {\n";
                    $file3 .= "\t\t\t\t\treturn ['error'=> true,'message'=> 'Erro ao Gravar'];\n";
                    $file3 .= "\t\t\t\t}";
                    //$file3 .= "\t\t\t\t        return \$this->repository->create(\$data);\n";
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
                    //$file3 .= "\t\t\t\t\$".strtolower($file_name)." = \$this->repository->findWhere(['id'=> \$id])->first();\n";
                    $file3 .= "\t\t\t\t\$".strtolower($file_name)." = ".$file_name."::find(\$id);\n";
                    $file3 .= "\t\t\t\tif (\$".strtolower($file_name)."){\n";
                    $file3 .= "\t\t\t\t        \$this->validator->with(\$data)->passesOrFail();\n";
                    $file3 .= "\t\t\t\t\$fields = include(app_path().\"/".$generation['head']['directory']."/field/fields_".strtolower($file_name).".php\");\n";
                    $file3 .= "\t\t\t\tforeach(\$data as \$ch => \$vl){\n";
                    $file3 .= "\t\t\t\t\tif (in_array(\$ch,\$fields)){\n";
                    $file3 .= "\t\t\t\t\t\t\$".strtolower($file_name)."->{\$ch} = \$vl;\n";
					$file3 .= "\t\t\t\t\t}\n";
                    $file3 .= "\t\t\t\tunset(\$vl);\n";
                    $file3 .= "\t\t\t\t}\n";
                    //$file3 .= "\t\t\t\t        \$this->repository->update(\$data, \$id);\n";
                    $file3 .= "\t\t\t\tif (\$".strtolower($file_name)."->update()){\n";
                    $file3 .= "\t\t\t\t\t       return self::get".$file_name."(\$id);\n";
                    $file3 .= "\t\t\t\t} else {\n";
                    $file3 .= "\t\t\t\t        return [\n";
                    $file3 .= "\t\t\t\t\t            'error'=> true,\n";
                    $file3 .= "\t\t\t\t\t            'message'=> 'Registro não Localizado para Alteração'\n";
                    $file3 .= "\t\t\t\t        ];\n";
                    $file3 .= "\t\t\t\t}\n";
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
                    $file .= "namespace ".$generation['head']['namespace']."\Repositories;\n";
                    $file .= "\n";
                    $file .= self::getHead();
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

    public static function createHtml($generation){
        
        $dir = base_path() . "/resources/views/";
        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }

        if ($generation){


            //CREATE LAYOUT DEFAULT FOR BOOTSTRAP
            $function = new Functions();

            foreach($generation['schema']['class'] as $value){

                $nameFile = $value->table["name"];
                $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
                $nameClass = $function->getNameClass($nameFile,$fullname);
                $nomeclasse = strtolower($nameClass);

                $directory = $dir."/".strtolower($nomeclasse)."/";

                if (!file_exists($directory)){
                    mkdir($directory, 0777, true);
                    chmod($directory, 0777);
                }

                $diretorio = dir($directory);

                if (!file_exists($directory."".$nomeclasse.".blade.php")){

                    $file_name = $nomeclasse.".blade.php";

                    $file = '
                    @extends("layout.index")
                    @section("content")

                    <!-- Breadcrumbs-->
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                        <a href="/">Home</a>
                        </li>
                        <li class="breadcrumb-item active">'.$function->getNameClass($nameClass).'</li>
                    </ol>

                    <!-- DataTables Example -->
                    <div class="card mb-3">
                        <div class="card-header">
                        <i class="fas fa-table"></i>
                        {{Config::get(\'options.titleReport\')}} '.$function->getNameClass($nameClass).'
                        <button class="btn btn-outline btn-success float-right margin action_generator"
                        data-toggle="modal" data-url="'.strtolower($nameFile).'"" data-target="#ModalForm" data-id=""
                        data-action="add" type="button">
                        ADD
                        </button>
                        </div>
                        <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>';
                                $cont_colun = 0;
                                    if (sizeof($value->table["fields"]) > 0){
                                        foreach($value->table["fields"] as $field){
                                            if ($field->attributes->report == "Y"){
                                                $file .= '<th>{{Config::get(\'translate.'.$value->table["name"].'.'.$field->name.'\')}}</th>';
                                                $file .= "\n";
                                                $cont_colun++;
                                            }
                                            unset($field);
                                        }
                                    }
                            $file .= '<th>{{Config::get(\'options.altOption\')}}</th>';
                            $file .= '</tr>
                            </thead>
                            <tfoot>
                                <tr>';
                                $cont_colun = 0;
                                if (sizeof($value->table["fields"]) > 0){
                                    foreach($value->table["fields"] as $field){
                                        if ($field->attributes->report == "Y"){
                                            $file .= '<th>{{Config::get(\'translate.'.$value->table["name"].'.'.$field->name.'\')}}</th>';
                                            $file .= "\n";
                                            $cont_colun++;
                                        }
                                        unset($field);
                                    }
                                }
                            $file .= '<th></th>';
                            $file .= '</tr>
                            </tfoot>
                            <tbody>';
                            $file .= '@foreach($'.strtolower($nameFile).' as $p)';
                            $file .= "\n";
                            $file .= '<tr>';
                            $file .= "\n";
                            if (sizeof($value->table["fields"]) > 0){
                                foreach($value->table["fields"] as $field){
                                    if ($field->attributes->report == "Y"){
                                        $file .= '<td>{{$p->'.$field->name.'}}</td>';
                                        $file .= "\n";
                                    }
                                    unset($field);
                                }
                            }
                            $file .= '<td>';
                            $file .= '<button class="btn btn-sm btn-outline btn-primary pull-right margin action_generator"';
                            $file .= 'data-toggle="modal"';
                            $file .= 'data-url = "'.strtolower($nameFile).'"';
                            $file .= 'data-target="#ModalForm"';
                            $file .= 'data-id="{{\Paulohsilvestre\GeneratorForLaravel\Utils\Functions::encrypt($p->id,true)}}"';
                            $file .= 'data-action="edit"';
                            $file .= 'type="button">EDIT</button>';    
                            $file .= '<button class="btn btn-sm btn-outline btn-danger pull-right margin action_generator"';
                            $file .= 'data-toggle="modal"';
                            $file .= 'data-url = "'.strtolower($nameFile).'"';
                            $file .= 'data-target="#ModalForm"';
                            $file .= 'data-id="{{\Paulohsilvestre\GeneratorForLaravel\Utils\Functions::encrypt($p->id,true)}}"';
                            $file .= 'data-action="getdelete"';
                            $file .= 'type="button">DEL</button>';
                            $file .= '</td>';
                            $file .= '</tr>';
                            $file .= '@endforeach';
                            $file .= '</tbody>
                            </table>
                            </div>
                            </div>
                            <div class="card-footer small text-muted">GENERATORFORLARAVEL</div>
                        </div>
                        @endsection
                        ';

                    if (!file_exists($directory . "".$file_name)){
                        $fp = fopen($directory . "" . $file_name, "a+");
                        $escreve = fwrite($fp, $file);
                        fclose($fp);
                        chmod($directory . "" . $file_name,0777);
                    }

                }

                   $file_name = "delete.blade.php";

                   if (!file_exists($directory."".$file_name)){

                    $file = "";
                    $file .= '<div class="modal-header">';
                    $file .= "\n";
                    $file .= '<h4 class="modal-title">{{Config::get(\'options.titleRemove\')}}</h4>';
                    $file .= "\n";
                    $file .= '<button class="close float-right" aria-label="Close" data-dismiss="modal" type="button">';
                    $file .= '<span aria-hidden="true">×</span>';
                    $file .= '</button>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '<div class="modal-body">';
                    $file .= "\n";

                    $migration = new Migration();
                    $name_default = $migration->getDefault(strtolower($nameFile), $generation['schema']['class']);
                    if (strlen(trim($name_default)) < 1){
                        $file .= '{{Config::get(\'options.titleConfirmRemove\')}}
                                <h2>{{$'.strtolower($nameFile).'->'.$value->table["primary"].'}}
                                    ADD FORMDESCRIPTION BEFORE , IN FIELD SHOW FORM
                                </h2>';
                    } else {
                        $file .= '{{Config::get(\'options.titleConfirmRemove\')}}
                                <h2>{{$'.strtolower($nameFile).'->'.$value->table["primary"].'}} -
                                {{$'.strtolower($nameFile).'->'.$name_default.'}}
                                </h2>';
                    }
                    $file .= "\n";

                    $file .= '<form name="_frm_'.strtolower($nameFile).'" id="_frm_'.strtolower($nameFile).'" method="post" class="form-horizontal">';
                    $file .= "\n";
                    $file .= "{{ csrf_field() }}\n";
                    $file .= '<input type="hidden" name="id" id="id" value="{{$'.strtolower($nameFile).'->'.$value->table["primary"].'}}" />';
                    $file .= "\n";
                    $file .= '</form>';
                    $file .= "\n";

                    $file .= '<div class="form-group">';
                    $file .= "\n";
                    $file .= '<div class="col-sm-12 text-center" id="returnform">';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '<div class="modal-footer">';
                    $file .= "\n";
                    $file .= '<button class="btn btn-warning btn-lg pull-left margin" data-dismiss="modal" type="button">{{Config::get(\'options.buttonCancel\')}}</button>';
                    $file .= "\n";
                    $file .= '<a return-form="returnform" data-url="'.strtolower($nameFile).'" data-form="_frm_'.strtolower($nameFile).'" data-modal="#ModalForm" data-id="{{$'.strtolower($nameFile).'->'.$value->table["primary"].'}}" data-action="delete" class="btn btn-danger pull-right btn-lg margin action_generator">{{Config::get(\'options.buttonConfirm\')}}</a>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";

                    if (!file_exists($directory . "".$file_name)){
                        $fp = fopen($directory . "" . $file_name, "a+");
                        $escreve = fwrite($fp, $file);
                        fclose($fp);
                        chmod($directory . "" . $file_name,0777);
                    }

                   }

                    $file_name = "insert.blade.php";
                    
                    if (!file_exists($directory."".$file_name)){

                    $file = "";
                    $file .= '<div class="modal-header">';
                    $file .= "\n";
                    $file .= '<h4 class="modal-title">{{Config::get(\'options.titleAdd\')}}</h4>';
                    $file .= "\n";
                    $file .= '<button class="close float-right" aria-label="Close" data-dismiss="modal" type="button">';
                    $file .= '<span aria-hidden="true">×</span>';
                    $file .= '</button>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '<div class="modal-body">';
                    $file .= "\n";

                    $file .= '<form name="frm_'.strtolower($nameFile).'" id="frm_'.strtolower($nameFile).'" method="post" class="form-horizontal">';
                    $file .= "\n";
                    $file .= "{{ csrf_field() }}\n";
                    $file .= '<section class="content">';
                    $file .= "\n";
                    $file .= '<div class="row">';
                    $file .= "\n";
                    $file .= '<div class="col-md-12">';
                    $file .= "\n";
                    $file .= '<div class="box-body">';
                    $file .= "\n";

                            if (@sizeof($value->table["fields"]) > 0){
                                foreach($value->table["fields"] as $field){

                                    $fk = false;
                                    if (@sizeof($value->table['foreign'])>0){
                                        foreach($value->table['foreign'] as $f){
                                            if ($field->name == $f->foreign){
                                                $fk = $f;
                                            }
                                            unset($f);
                                        }
                                    }

                                    if ($fk){

                                        $file .= '<div class="form-group row">';
                                        $file .= "\n";
                                        $file .= '    <label class="col-sm-4 col-form-label" for="'.$field->name.'">'.$function->getStringFirstUpper($fk->referencetable).'</label>';
                                        $file .= "\n";
                                                $file .= '<div class="col-sm-6">';
                                                $file .= "\n";
                                                    $file .= '<select name="'.$field->name.'" class="form-control-plaintext" required id="'.$field->name.'">';
                                                    $file .= "\n";
                                                        $file .= '<option value="">{{Config::get(\'options.selectOption\')}} '.$function->getStringFirstUpper($fk->referencetable).'</option>';
                                                        $file .= "\n";
                                                        $file .= '@foreach($'.$fk->referencetable.' as $li)';
                                                        $file .= "\n";
                                                        $migration = new Migration();
                                                        $name_default = $migration->getDefault($fk->referencetable, $generation['schema']['class']);
                                                        if (strlen(trim($name_default)) < 1){
                                                            $file .= '<option value="{{$li->id}}">{{$li->id}} - ADD FORMDESCRIPTION FIELD SCHEMA.SQL BEFORE ;</option>';
                                                        } else {
                                                            $file .= '<option value="{{$li->id}}">{{$li->id}} - {{$li->'.$name_default.'}}</option>';
                                                        }
                                                        $file .= "\n";
                                                        $file .= '@endforeach';
                                                        $file .= "\n";
                                                    $file .= '</select>';
                                                    $file .= "\n";
                                                $file .= '</div>';
                                                $file .= '<div class="col-sm-2">';
                                                $file .= '<button class="btn btn-sm btn-info float-right action_generator"
                                                    data-toggle="modal" data-url="'.$fk->referencetable.'" data-target="#ModalForm" data-id=""
                                                    data-action="add" type="button">+</button>';        
                                                $file .= '</div>';
                                                
                                                $file .= "\n";
                                            $file .= '</div>';
                                            $file .= "\n";

                                    } else {

                                        if ($field->name != $value->table['primary']){

                                            $file .= '';
                                            $file .= '<div class="form-group row">';
                                            $file .= "\n";
                                            $file .= '<label class="col-sm-4 col-form-label" for="'.$field->name.'">{{Config::get("translate.'.$value->table["name"].'.'.$field->name.'")}}</label>';
                                            $file .= "\n";
                                            $file .= '<div class="col-sm-8">';
                                            $file .= "\n";
                                            $file .= $function::getField($field, $value->table["name"]);
                                            $file .= "\n";
                                            $file .= '</div>';
                                            $file .= "\n";
                                            $file .= '</div>';
                                            $file .= "\n";
                                        }

                                    }
                                    unset($field);
                                }
                            }

         $file .='        <div class="form-group">';
         $file .= "\n";
         $file .= '                   <div class="col-sm-12 text-center" id="returnform">';
         $file .= "\n";
                            $file .= '</div>';
                            $file .= "\n";
                        $file .= '</div>';
                        $file .= "\n";

                    $file .= '</div>';
                    $file .= "\n";
                 $file .= '</div>';
                 $file .= "\n";
               $file .= '</div>';
               $file .= "\n";
        $file .= '</section>';
        $file .= "\n";
        $file .= '</form>';
        $file .= "\n";

                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '<div class="modal-footer">';
                    $file .= "\n";
                    $file .= '<button class="btn btn-warning btn-lg pull-left margin" data-dismiss="modal" type="button">{{Config::get(\'options.buttonCancel\')}}</button>';
                    $file .= "\n";
                    $file .= '<a return-form="returnform" data-url="'.strtolower($nameFile).'" data-form="frm_'.strtolower($nameFile).'" data-modal="#ModalForm" data-id="" data-action="store" class="btn btn-success pull-right btn-lg margin action_generator">{{Config::get(\'options.buttonConfirm\')}}</a>';
                    $file .= "\n";
                    $file .= '</div>'; $file .= "\n";

                    //if (!file_exists($directory . "".$file_name)){
                        $fp = fopen($directory . "" . $file_name, "a+");
                        $escreve = fwrite($fp, $file);
                        fclose($fp);
                        chmod($directory . "" . $file_name,0777);
                    //}

                   }

                    $file_name = "edit.blade.php";

                    if (!file_exists($directory."".$file_name)){

                    $file = "";
                    $file .= '<div class="modal-header">\n';
                    $file .= '<h4 class="modal-title">{{Config::get(\'options.titleEdit\')}}</h4>';
                    $file .= '<button class="close float-right" aria-label="Close" data-dismiss="modal" type="button">';
                    $file .= '<span aria-hidden="true">×</span>';
                    $file .= '</button>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '<div class="modal-body">';
                    $file .= "\n";

                    $file .= '<form name="frm_'.strtolower($nameFile).'" id="frm_'.strtolower($nameFile).'" method="post" class="form-horizontal">';
                    $file .= "\n";
                    $file .= "{{ csrf_field() }}\n";
                    $file .= '<input type="hidden" name="id" id="id" value="{{$'.strtolower($nameFile).'->'.$value->table['primary'].'}}" />';
                    $file .= "\n";
                    $file .= '<input type="hidden" name="formulario" id="formulario" value="'.strtolower($nameFile).'" />';
                    $file .= "\n";
                    $file .= '<section class="content">';
                    $file .= "\n";
                    $file .= '<div class="row">';
                    $file .= "\n";
                    $file .= '<div class="col-md-12">';
                    $file .= "\n";
                    $file .= '<div class="box-body">';
                    $file .= "\n";

                    if (sizeof($value->table["fields"]) > 0){
                        foreach($value->table["fields"] as $field){

                            $fk = false;
                            if (@sizeof($value->table['foreign'])>0){
                                foreach($value->table['foreign'] as $f){
                                    if ($field->name == $f->foreign){
                                        $fk = $f;
                                    }
                                    unset($f);
                                }
                            }

                            if ($fk){

                                $file .= '<div class="form-group">';
                                $file .= "\n";
                                $file .= '    <label class="col-sm-3 col-form-label" for="'.$field->name.'">'.$function->getStringFirstUpper($fk->referencetable).'</label>';
                                $file .= "\n";
                                $file .= '<div class="col-sm-9">';
                                $file .= "\n";
                                $file .= '<select name="'.$field->name.'" class="form-control-plaintext" required id="'.$field->name.'">';
                                $file .= "\n";
                                $file .= '<option value="">{{Config::get(\'options.selectOption\')}} '.$function->getStringFirstUpper($fk->referencetable).'</option>';
                                $file .= "\n";

                                $file .= '@foreach($'.strtolower($fk->referencetable).' as $p)';
                                $file .= "\n";
                                $file .= '@if ($p->id == $'.strtolower($nameFile).'->'.$fk->foreign.')';
                                $file .= "\n";
                                $file .= ' <option value="{{$p->id}}" selected="selected">{{$p->id}} - {{$p->nome}}</option>';
                                $file .= "\n";
                                $file .= '@else';
                                $file .= "\n";
                                $file .= ' <option value="{{$p->id}}">{{$p->id}} - {{$p->nome}}</option>';
                                $file .= "\n";
                                $file .= '@endif';
                                $file .= "\n";
                                $file .= '@endforeach';
                                $file .= "\n";
                                $file .= '</select>';
                                $file .= "\n";
                                $file .= '</div>';
                                $file .= "\n";
                                $file .= '</div>';
                                $file .= "\n";

                                //$foreign->referencefield

                            } else {

                                    if ($field->name != $value->table['primary']){
                                        $file .= '';
                                        $file .= '<div class="form-group">';
                                        $file .= "\n";
                                        $file .= '<label class="col-sm-3 col-form-label" for="'.$field->name.'">'.$function->getStringFirstUpper($field->name).'</label>';
                                        $file .= "\n";
                                        $file .= '<div class="col-sm-9">';
                                        $file .= "\n";
                                        $file .= $function::getField($field, $value->table["name"],'{{$'.strtolower($nameFile).'->'.$field->name.'}}');
                                        $file .= "\n";
                                        $file .= '</div>';
                                        $file .= "\n";
                                        $file .= '</div>';
                                        $file .= "\n";
                                    }
                            }
                            unset($field);
                        }
                    }

                    $file .='        <div class="form-group">';
                    $file .= "\n";
                    $file .= '   <div class="col-sm-12 text-center" id="returnform">';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";

                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '</section>';
                    $file .= "\n";
                    $file .= '</form>';
                    $file .= "\n";

                    $file .= '</div>';
                    $file .= "\n";
                    $file .= '<div class="modal-footer">';
                    $file .= "\n";
                    $file .= '<button class="btn btn-warning btn-lg pull-left margin" data-dismiss="modal" type="button">{{Config::get(\'options.buttonCancel\')}}</button>';
                    $file .= "\n";
                    $file .= '<a return-form="returnform" data-url="'.strtolower($nameFile).'" data-form="frm_'.strtolower($nameFile).'" data-modal="#ModalForm" data-id="{{$'.strtolower($nameFile).'->'.$value->table["primary"].'}}" data-action="update" class="btn btn-success pull-right btn-lg margin action_generator">{{Config::get(\'options.buttonConfirm\')}}</a>';
                    $file .= "\n";
                    $file .= '</div>';
                    $file .= "\n";

                    if (!file_exists($directory . "".$file_name)){
                        //unlink($dir . "Repositories/" . $file_name . "Repository.php");
                        $fp = fopen($directory . "" . $file_name, "a+");
                        $escreve = fwrite($fp, $file);
                        fclose($fp);
                        chmod($directory . "" . $file_name,0777);
                    }
                }

            }
        }
    }

    public static function createMenu($generation){
        
        $function = new Functions();
        
        $dir = base_path() . "/resources/views/layout/";

        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $string = "";

        if ($generation){
            foreach($generation['schema']['class'] as $valor){

               $nameFile = $valor->table["name"];    
               $fullname = (@$generation['head']['namemodel'] == "Y") ? "Y" : "N";
               $nameClass = $function->getNameClass($nameFile,$fullname);
               $file_name = $nameClass;

               $string .= "\t\t";
               $string .= '<li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="/'.strtolower($nameFile).'" 
                    role="button">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>{{Config::get(\'menu.'.$valor->table["name"].'.menu\')}}</span>
                    </a>
                </li>';
               $string .= "\n";
               unset($valor);
            }
        }

        $fp = fopen($dir . "menu.blade.php", "w+");
        $escreve2 = fwrite($fp, $string);
        fclose($fp);

    }

    public static function createTranslateMenu($generation){
        
        $function = new Functions();
        
        $dir = base_path() . "/config/";
        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $string = "";
        $string .= "<?php\n";
        $string .= self::getHead();
        $string .= "\treturn [\n";
        if ($generation){
            foreach($generation['schema']['class'] as $valor){
                $string .= "\t\t'".$valor->table["name"]."' => [\n";
                $string .= "\t\t\t'menu'=>'".$valor->table["name"]."',\n";
                $string .= "\t\t\t'list'=>'".$valor->table["name"]."',\n";
                $string .= "\t\t],\n";
               unset($valor);
            }
        }
        $string .= "\t];\n";

        $fp = fopen($dir . "menu.php", "w+");
        $escreve2 = fwrite($fp, $string);
        fclose($fp);

    }


}