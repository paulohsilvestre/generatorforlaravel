<?php

namespace Paulohsilvestre\GeneratorForLaravel\Generation;

use Paulohsilvestre\GeneratorForLaravel\Generation\Functions;

class Migration {

    public static function getSchema($file){


        $content = "";
        unset($database);
        $class = array();
        $classname = "";
        $objClass = null;

        if (!file_exists($file)){
            throw new \Exception("Not file exists database/schema/".$schema." for generated Files");
            exit(0);
        }

        $handle = @fopen($file, "r");
        $array = explode("\n", str_ireplace('`','',file_get_contents($file)));

        if (sizeof($array) > 0){

            foreach ($array as $list){

                if (substr(trim($list),0,2) != "--"){

                    if (substr($list, 0, 3) == "USE"){

                        $db = explode(" ",$list);
                        $database = substr(trim($db[1]),0,(strlen($db[1])));
                        $class["database"] = $database;

                    } else if (substr($list, 0, 3) == "SET"){
                    } else if (stripos($list, 'CREATE SCHEMA') !== false){

//                    } else if (substr($list, 0, 12) == "CREATE TABLE"){
                    } else if (stripos($list, 'CREATE TABLE') !== false){

                        $objClass = new \stdClass();
                        $table = explode(".",$list);
                        $_classname = substr(trim($table[1]),0,(strlen($table[1])-2));
                        $objClass->table["name"] = $_classname;
                        $classname = $_classname;

                    } else if (trim($list) != "ENGINE = InnoDB;"){

                        if (substr(trim($list), 0, 11) == "PRIMARY KEY"){
                            $primary = Functions::getValuesCaracter(trim($list), "(", ")");
                            $objClass->table["primary"] = $primary;

                        } else if (substr(trim($list),0,5) == "INDEX") {
                            //INDEX fk_remontas_usuarios1_idx (usuarios_id ASC),
                            $index = Functions::getValuesCaracter(trim($list), "(", ")");
                            $explode = explode(" ",$index);
                            if (sizeof($explode) > 0){
                                $objClass->table["index"][] = $explode[0];
                            }

                        } else if (substr(trim($list),0,10) == "CONSTRAINT") {
                            $constraint = new \stdClass();
                            $explode = explode(" ",trim($list));
                            $constraint->name = $explode[1];
                        } else if (substr(trim($list),0,11) == "FOREIGN KEY"){
                            //FOREIGN KEY (camas_id)
                            $index = Functions::getValuesCaracter(trim($list), "(", ")");
                            $constraint->foreign = $index;
                        } else if (substr(trim($list),0,10) == "REFERENCES"){
                            $index = Functions::getValuesCaracter(trim($list), "(", ")");
                            $_expTable = explode(".",$list);
                            $_expSecond = explode(" ",$_expTable[1]);
                            $constraint->referencetable = $_expSecond[0];
                            $constraint->referencefield = $index;
                            $objClass->table["foreign"][] = $constraint;
                            //REFERENCES mydb.camas (id)
                        } else if (substr(trim($list),0,2) == "ON"){
                            //ON DELETE NO ACTION
                            //ON UPDATE NO ACTION,
                        } else if (substr(trim($list),0,12) == "UNIQUE INDEX"){
                            //$index = Functions::getValuesCaracter(trim($list), "(", ")");
                            //$_expField = explode(" ",$index);
                        } else {

                            //find first space for separeted field
                            $space = stripos(trim($list), " ");
                            if ($space){
                                $field = new \stdClass();
                                $field->name = substr(trim($list), 0, $space);

                                $field->null = "Y";
                                if (stripos(trim($list), "NOT")){
                                    $field->null = "N";
                                }

                                $field->increment = "N";
                                if (stripos(trim($list), "INCREMENT")){
                                    $field->increment = "Y";
                                }

                                $field->enum = "N";
                                if (stripos(trim($list), "ENUM")){
                                    $field->enum = "Y";
                                }

                                if (stripos(trim($list), "enum")){
                                    $field->enum = "Y";
                                }

                                if (stripos(trim($list), "FORMDESCRIPTION")){
                                    $field->default = "Y";
                                }

                                if (stripos(trim($list), "FORMS")){
                                    $field->default = "Y";
                                }

                                if (stripos(trim($list), "COMMENT")){

                                    $pos_com = strpos($list, "COMMENT")+9;
                                    $pos_vir = strpos($list, "',");
                                    //dd(substr($list,$pos_com, ($pos_vir-$pos_com)));
                                    $field->comment = substr($list,$pos_com, ($pos_vir-$pos_com));
                                }

                                $field->report = 'N';
                                if (stripos(trim($list), "REPORT")){
                                    $field->report = "Y";
                                }

                                if ($field->enum == "Y"){
                                    $field->type = "ENUM";
                                    $field->null = "N";
                                    $valores = Functions::getValuesCaracter($list, "(", ")");
                                    $tm = $valores;
                                } else {
                                    //get type field for definition
                                    $nlist = substr(trim($list), $space);
                                    $spacetype = stripos(trim($nlist), " ");
                                    $field->type = substr(trim($nlist), 0, $spacetype);
                                    $posI = stripos($field->type,"(");
                                    $tm = "";
                                    if ($posI){
                                        $tm = Functions::getValuesCaracter($field->type, "(", ")");
                                    }
                                    if ($posI){
                                        $field->type = substr($field->type, 0, $posI);
                                    }
                                }

                                $field->width = $tm;

                                $fields[] = $field;
                                $objClass->table["fields"] = $fields;
                            }
                        }


                    } else {
                        $class["class"][] = $objClass;
                        unset($fields);
                        $objClass = new \stdClass();
                    }
                }

            }
        }

        return $class;

    }



    /**
     * RETORNA CADA STRING COM SEU TIPO PARA QUE SEJA GERADA AS LINHAS DOS MIGRATIONS
     * PARA O LARAVEL
     * @param unknown $obj
     * @return string
     */
    public static function getMigrationField($obj, $indices){

        if ($obj){

            $index = "N";
            if (sizeof($indices)>0){
                foreach($indices as $value){
                    if ($obj->name == $value){
                        $index = "S";
                        break;
                    }
                    unset($valor);
                }
            }

            $obj->type = strtoupper($obj->type);
            $ret = "";
            if ($obj->type == "BLOB"){
                $ret = "\$table->binary('".$obj->name."')";
            } else if ($obj->type == "BIGINT"){
                $ret = "\$table->bigInteger('".$obj->name."')";
            } else if ($obj->type == "BOOLEAN"){
                $ret = "\$table->boolean('".$obj->name."')";
            } else if ($obj->type == "CHAR"){
                $ret = "\$table->char('".$obj->name."',".$obj->width.")";
            } else if ($obj->type == "DATE"){
                $ret = "\$table->date('".$obj->name."')";
            } else if ($obj->type == "DATETIME"){
                $ret = "\$table->dateTime('".$obj->name."')";
            } else if ($obj->type == "DECIMAL"){
                $ret = "\$table->decimal('".$obj->name."',".$obj->width.")";
            } else if ($obj->type == "DOUBLE"){
                if ($obj->width > 0){
                    $ret = "\$table->double('".$obj->name."',".$obj->width.")";
                } else {
                    $obj->width = 2;
                    $ret = "\$table->double('".$obj->name."',".$obj->width.")";
                }
            } else if ($obj->type == "FLOAT"){
                $ret = "\$table->float('".$obj->name."')";
            } else if ($obj->type == "INT"){
                if ($obj->increment == "Y"){
                    $ret = "\$table->increments('".$obj->name."')";
                } else {
                    $ret = "\$table->integer('".$obj->name."')";
                }
            } else if ($obj->type == "VARCHAR"){
                if ($obj->width > 0){
                    $ret = "\$table->string('".$obj->name."',".$obj->width.")";
                } else {
                    $ret = "\$table->string('".$obj->name."')";
                }
            } else if ($obj->type == "LONGTEXT"){
                $ret = "\$table->longText('".$obj->name."')";
            } else if ($obj->type == "TIMESTAMP"){
                $ret = "\$table->timestamp('".$obj->name."')";
            } else if ($obj->type == "TEXT"){
                $ret = "\$table->text('".$obj->name."')";
            } else if ($obj->type == "MEDIUMINT"){
                $ret = "\$table->mediumInteger('".$obj->name."')";
            } else if ($obj->type == "MEDIUMTEXT"){
                $ret = "\$table->mediumText('".$obj->name."')";
            } else if ($obj->type == "SMALLINT"){
                $ret = "\$table->smallInteger('".$obj->name."')";
            } else if ($obj->type == "TIME"){
                $ret = "\$table->time('".$obj->name."')";
            } else if ($obj->type == "BIG INTEGE"){
                $ret = "\$table->bigIncrements('".$obj->name."')";
            } else if (strtoupper($obj->type) == "ENUM"){
                $ret = "\$table->enum('".$obj->name."',[".$obj->width."])";
            }

            if ($obj->null == "Y"){
                $ret .= "->nullable()";
            }

            if ($index == "S"){
                $ret .= "->index()";
            }

            $ret .= ";";

            return $ret;

        }

        //         $table->enum('choices', ['foo', 'bar']);	ENUM
        //         $table->json('options');	JSON
        //         $table->jsonb('options');	JSONB
        //         $table->morphs('taggable');	INTEGER
        //         $table->nullableTimestamps(); timestamps()
        //         $table->rememberToken(); remember_token.
        //         $table->tinyInteger('numbers');	TINYINT
        //         $table->timestamps();
        //         $table->uuid('id');	UUID

    }


    public static function getDefault($table, $array){

        if ($table && $array){
            $name = "";
            if (sizeof($array) > 0){
                $achou = false;

                foreach($array as $value){
                    if ($value->table["name"] == strtolower($table)){
                        if (sizeof($value->table["fields"]) > 0){
                            foreach($value->table["fields"] as $field){
                                if (@$field->default == "Y"){
                                    $achou = true;
                                    $name = $field->name;
                                    break;
                                }
                                unset($field);
                            }
                        }
                    }
                    if ($achou){
                        break;
                    }
                    unset($value);
                }
                return $name;
            }
            return $name;

        } else {
            return $name;
        }

    }




    /**
     * RETORNA AS FOREIGN DAS TABELAS PARA CRIAR O MIGRATE
     * @param unknown $foreign
     * @return string
     */
    public static function getMigrationForeign($foreign){

        // "name": "fk_produtores_empresas1"
        // "foreign": "empresas_id"
        // "referencetable": "empresas"
        // "referencefield": "id"
        $ret = "";
        if ($foreign){
            $ret .= "\t\t\t\t\$table->integer('".$foreign->foreign."')->unsigned();\n";
            $ret .= "\t\t\t\t\$table->foreign('".$foreign->foreign."')->references('".$foreign->referencefield."')->on('".$foreign->referencetable."');\n";
        }
        return $ret;

    }


}
