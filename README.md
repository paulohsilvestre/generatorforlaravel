# generatorforlaravel

## Quick Start

#### Install Through Composer

Se desejarem ajudar a melhorar o projeto, estou a disposição, quero deixar uma ferramenta mais completa, gerando para VueJs/Angular etc.

Crie um projeto Laravel

``` bash
composer create-project --prefer-dist laravel/laravel blog

```
Importe o framework para seu projeto instalando pelo composer

``` bash
$ composer require paulohsilvestre/generatorforlaravel:~1.0
```
    
Or add in the `require` key of `composer.json` file manually

``` json
"paulohsilvestre/generatorforlaravel": "~1.0"
```

no arquivo config/app.php registre a classe GeneratorForLaravel
``` php
Paulohsilvestre\GeneratorForLaravel\GenerationServiceProvider::class
```

Localize em seu projeto o arquivo de rotas do Laravel provavelmente está em routes/web.php
adicione onde você deseja que as rotas sejam incluídas a seguinte linha 

```
####ROUTEADD
```
Essa linha acima será usada para que o framework adicione as rotas que ainda não foram adicionadas, se desejar pode colocar entre seu middleware de validação de sessão etc.

Agora você pode criar um DER no MysqlWorkBench, esse modelo deve ser exportado como SQL CREATE SCRIPT, como no modelo abaixo exportado em .sql, nos comentários do WorkBench poderá ser adicionado tipos que vc deseja que seus formulários validem os dados "AINDA NÃO ESTÃO 100%, estou trabalhando neles"

``` bash

-- MySQL Script generated by MySQL Workbench
-- Thu Oct 25 16:37:21 2018
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`estados`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`estados` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL COMMENT '<placeholder>Nome do Estado</placeholder>',
  `abreviacao` VARCHAR(2) NULL,
  `status` VARCHAR(1) NULL COMMENT '<type>select</select>\n<options>S|Sim, N|Não</options>',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`cidades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`cidades` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL COMMENT '<min>5</min>\n<max>100</max>\n<type>input</type>',
  `dd` VARCHAR(2) NULL COMMENT '<max>2</max>\n<min>2</min>',
  `estados_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_cidades_estados_idx` (`estados_id` ASC),
  CONSTRAINT `fk_cidades_estados`
    FOREIGN KEY (`estados_id`)
    REFERENCES `mydb`.`estados` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

```
Agora que já temos o arquivo para processamento, acessa a url do seu projeto

```
http://SEUSERVIDOR/generation

```
Essa URL é a URL de inicio do processo e futuro reprocessamento, por exemplo hoje você processou seu DER, e amanhã precisa adicionar um novo campo, faz no DER e exporta ele novamente, nessa url você deve enviar novamente para que o sistema consiga gerar os dados necessários, quando um novo campo é criado o mesmo é adicionado um migration para update da tabela.

```
ATENÇÃO, POR SEGURANÇA SE O SISTEMA ENCONTRA UM ARQUIVO EXISTENTE ELE NÃO SOBRESCREVE, AI OS PASSOS SÃO ADICIONAR O CAMPO MANUALMENTE ONDE NECESSÁRIO COMO POR EXEMPLO EM UM FORMULÁRIO, SE DESEJAR QUE O SISTEMA RECRIE UM FORMULÁRIO, CONTROLLER, SERVICE OS MESMOS DEVEM SER EXCLUÍDOS.
```

O sistema no formulario de envio traz alguns campos padrões, podem ser mudados só que ainda não foram completamente testados, então podem ocorrer problemas :(

```
NÃO IMPLEMENTADO AINDA
DADOS DAS CONEXÕES COMO USUÁRIO E SENHA, DEVEM SER TROCADOS AINDA NO .ENV PARA EVITAR PROBLEMAS
```

#### Add run command
Após importar o arquivo, vai até o console do projeto e execute os comandos a seguir

``` bash
php artisan migration
composer dumpautoload
```

#### Add Service Provider

registre as seguintes arquivos em config.php

App\Providers\AppRepositoryProvider::class,


Agora adicione ao seu arquivo de rotas 
```
Route::get('/', function () {
    return view('layout.index');
});
```

Apenas acesse agora seu site e ele deve estar abrindo normalmente, basta acessar os menus existentes para que sejam listados os dados já com as funções incluir, alterar, excluir


Artisan Service Provider is an optional provider required only if you want `vendor:publish` command working.

And you're done! You can now start installing any Laravel Package out there.