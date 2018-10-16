<?php

namespace Paulohsilvestre\GeneratorForLaravel\Utils;

use Illuminate\Support\Facades\Auth;

class Functions {
    
    
    /**
     * RETORNA A MENSAGEM EM UM ARRAY PARA QUE O JQUERY CONSIGA TRATAR
     * @param unknown $text
     * @return unknown
     */
    public static function getMessageJquery($text,$jsonEncode = ""){
        if ($jsonEncode == "S"){
            $message[] = $text;
            return json_encode($message);
        } else {
            return $message[] = $text; 
        }
    }
    
    
    /**
     * 
     * @param unknown $date
     * @return string
     */
    public static function formatDate($date, $tipo, $formate = "dd/mm/yyyy"){
        if ($tipo == "I"){
            $date_hora = "";
            if ($date){
                if (stripos($date, " ")){
                    $date_hora = explode(" ",$date);
                }
                if (sizeof($date_hora) <= 1){
                    $_date = explode("/",$date);
                    $date_return =  $_date[2]."-".$_date[1]."-".$_date[0];
                    return $date_return;
                } else {
                    $_date = explode("/",$date_hora[0]);
                    $date_return = $_date[2]."-".$_date[1]."-".$_date[0];
                    return $date_return." ".$date_hora[1];
                }
            }
        } else if ($tipo == "F"){
            $date_hora = $date;
            if (stripos($date, " ")){
                $date_hora = explode(" ",$date);
                $dt = explode("-",$date_hora[0]);
            } else {
                $dt = explode("-",$date_hora);
            }
            $ret = "";
            if (strtoupper($formate) == "DD/MM/YYYY"){
                $ret = $dt[2]."/".$dt[1]."/".$dt[0];
                if ($ret == "00/00/0000"){
                    return "";
                } else {
                    return $ret;
                }
            } else {
                $ret = $dt[2]."-".$dt[1]."-".$dt[0];
                if ($ret == "00-00-0000"){
                    return "";
                } else {
                    return $ret;
                }
            }
            
        } else if ($tipo == "R"){
            return $date;
        }
        
        return $date;

    }
    
    
    /**
     * SUBSTITUI O CARACTER POR VAZIO
     * @param unknown $string
     * @param unknown $char
     * @return mixed
     */
    public static function removeCaracter($string, $char){
        $str = "";
        $str = str_ireplace($char, "", $string);
        return $str;
    }
    
    /**
     * 
     * @param unknown $role
     * @return string
     */
    public static function getRole($role){
        
        if (strtoupper($role) == "ADMIN"){
            return "Administrador";
        } else if (strtoupper($role) == "EMPRESA"){
            return "Empresa Administradora";
        } else if (strtoupper($role) == "PRODUTOR"){
            return "Acesso Produtor";
        } else if (strtoupper($role) == "USUARIO"){
            return "Operador";
        } else {
            return "Não Informado";
        }
        
    }
    
    
    /**
     * RETURNA CARACTERES BETWEEN TO OTHERS VALUES
     * @param String $string
     * @param String $charI
     * @param String $charE
     */
    public static function getValuesCaracter($string, $charI, $charE){

        if ($string){
            if ($charI && $charE){
                $ini = stripos($string, $charI);
                $strSplit = substr($string, $ini+1, strlen($string));
                $end = stripos($strSplit,$charE);
                //$str = substr($string, ($ini+1), ($ini+$end));
                $strReturn = "";
                for ($i=$ini+1;$i<=($ini+$end);$i++){
                    $strReturn .= $string[$i];
                }
                return $strReturn;
            }
        }
        return $string;
    }

    /**
     * RETORNA O NOME DA CLASSE QUE SERÁ GERADA PARA AS ENTITIES
     * EX: empresas = Empresa
     * @param unknown $name
     * @return string
     */
    public static function getNameClass($name){
        if ($name){
            
            $tm = strlen($name);
            if (strtoupper($name[($tm-1)]) == "S"){
                $name = substr($name,0,($tm-1));
            }
            return strtoupper(substr($name,0,1))."".strtolower(substr($name,1,$tm));
            
        } else {
            return $name;
        }
    }
    
    /**
     * RETORNA O NOME DA CLASSE QUE SERÁ GERADA PARA AS ENTITIES
     * EX: empresas = Empresa
     * @param unknown $name
     * @return string
     */
    public static function getStringFirstUpper($name){
        if ($name){
            $tm = strlen($name);
            return strtoupper(substr($name,0,1))."".strtolower(substr($name,1,$tm));
        } else {
            return $name;
        }
    }
    
    
    /**
     * RETORNA O NOME DA CLASSE QUE SERÁ GERADA PARA AS ENTITIES
     * EX: empresas = Empresa
     * @param unknown $name
     * @return string
     */
    public static function getNameClassFirstUpperCase($name){
        if ($name){    
            $tm = strlen($name);
            return strtoupper(substr($name,0,1))."".strtolower(substr($name,1,$tm));
        } else {
            return $name;
        }
    }
    
    /**
     * VERIFICA SE UM ARQUIVO QUE CONTENHA TAL NOME EXISTE NO DIRETORIO INFORMADO
     * @param String $dir
     * @param String $filename
     */
    public static function fileExistsContent($dir, $filename){

        $find = false;
        $diretorio = dir($dir); 
        while($arquivo = $diretorio -> read()){
            if (stripos($arquivo, $filename)){
                $find = true;
                break;
            }  
        } 
        $diretorio -> close();
        return $find;
    }
    
    /**
     * FRASE A SER CRIPTOGRAFADA
     * 
     * A MESMA CHAVE DEVE SER USADA TANTO PARA DESCRIPTIGRAFAR QUANTO CRIPTOGRAFAR
     * 
     * CRYPT PASSADO COMO FALSE DESCRIPTOGRAFA, TRUE CRIPTOGRAFA
     * 
     * @param String $frase
     * @param String $chave
     * @param String $crypt
     * @return string
     */
    public static function encrypt ($frase, $crypt)
    {
        $retorno = "";
        $chave = env("APP_ENC");
        
        if ($frase == ''){
            return '';
        } else {
            if ($crypt){
                $frase = date('dmY')."|".$frase;
            }
        }
    
        if ($crypt) {
            $string = $frase;
            $i = strlen($string) - 1;
            $j = strlen($chave);
            do {
                $retorno .= ($string{$i} ^ $chave{$i % $j});
            } while ($i --);
    
            $retorno = strrev($retorno);
            $retorno = base64_encode($retorno);
        } else {
            $string = base64_decode($frase);
            $i = strlen($string) - 1;
            $j = strlen($chave);
    
            do {
                $retorno .= ($string{$i} ^ $chave{$i % $j});
            } while ($i --);
    
            $retorno = strrev($retorno);
        }
        return $retorno;
    }
    
    public static function getPermission($form, $op){
        
        $user = Auth::user();

        return true;
    }
    
    
    public static function validaId($id){
        
        if ($id){
            
            $des = self::encrypt($id, false);
            $explode = explode("|",$des);
            if ($explode[0] == date('dmY')){
                return true;
            } else {
                return false;
            }
            
        } else {
            return false;
        }
        
    }
    
    
    public static function getId($id){
    
        if ($id){
            $des = self::encrypt($id, false);
            $explode = explode("|",$des);
            if ($explode[0] == date('dmY')){
                return $explode[1];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    
    }
    
    
    /**
     * GRAVA LOGS NA BASE DE DADOS DOS PROCESSOS E AÇÕES REALIZADAS
     * @param String $type
     * @param String $operacao
     * @param String $processo
     * @param String $descricao
     * @param String $object
     * @param array $request
     */
    public static function writeLog($type, $operacao, $processo, $descricao, $object = "", $request = "", $table="", $registro = ""){
        
        $user = \Auth::user();
        
        if (strtoupper($type) == "W"){
          
          //'usuariosempresa_id'  
          $log = new Log();
          $log->data = self::formatDate(date('d/m/Y H:m:i'), "I"); 
          $log->processo = $processo;
          $log->operacao = $operacao;
          $log->descricao = $descricao; 
          $log->table = strtoupper($table);
          $log->registro = strtoupper($registro);
          if ($user->produtor_id != ""){
            $log->produtores_id = $user->produtor_id;
          }
          $log->empresas_id = $user->empresa_id;
          $log->usuarios_id = $user->id;
          $log->usuario = $user->id;
          $log->save();
          
        } else if (strtoupper($type) == "U"){
            
            if (sizeof($request) > 0){
                foreach($request as $chave => $valor){
                    $vl = "";
                    $comando = "\$vl = \$object->".$chave.";";
                    eval($comando);
                    if ($vl != $valor){
                        $log = new Log();
                        $log->data = self::formatDate(date('d/m/Y H:m:i'), "I");
                        $log->processo = "UPDATE";
                        $log->operacao = "ALTERFIELD";
                        $log->table = strtoupper($table);
                        $log->registro = strtoupper($registro);
                        $log->descricao = "ALTER FIELD: ".$chave." -> '".$vl."' FOR '".$valor;
                        if ($user->produtor_id != ""){
                            $log->produtores_id = $user->produtor_id;
                        }
                        $log->empresas_id = $user->empresa_id;
                        $log->usuarios_id = $user->id;
                        $log->usuario = $user->id;
                        $log->save();
                    }
                    unset($valor);
                }
            }
            
        }
        
    }
    
    public static function copyDirectory ($source, $dest)
    {
        // COPIA UM ARQUIVO
        if (is_file($source)) {
            return copy($source, $dest);
        }
    
        // CRIA O DIRETÓRIO DE DESTINO
        if (! is_dir($dest)) {
            mkdir($dest);
            echo "DIRET&Oacute;RIO $dest CRIADO<br />";
        }
    
        // FAZ LOOP DENTRO DA PASTA
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
        // PULA "." e ".."
        if ($entry == '.' || $entry == '..') {
            continue;
        }
    
        // COPIA TUDO DENTRO DOS DIRETÓRIOS
        if ($dest !== "$source/$entry") {
        copyr("$source/$entry", "$dest/$entry");
        }
        }
    
        $dir->close();
        return true;
        }
    
    /**
     * RETORNA OS TIPO DE MASCARAS DISPONIVEIS PARA OS CAMPOS
     * @param String $type
     * @return String;
     */
    public static function getMask($type){
        
//         $mascara = $type;
//         if($type){
//             if ($type == "VARCHAR"){
//                 $mascara = "text";
//             } else if ($type == "cnpj"){
//                 $mascara = "cnpj";
//             } else if ($type == "data"){
//                 $mascara = "data";
//             } else if ($type == "cpf"){
//                 $mascara = "cnpj";
//             } else if ($type == "moeda"){
//                 $mascara = "cnpj";
//             }
            
//         }
//         return $mascara;
    }
    
    
}