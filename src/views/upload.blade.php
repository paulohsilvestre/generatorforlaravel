<link type='text/css' href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600' rel='stylesheet'>
<link type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet">
<link type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.4.0/dropzone.css" rel="stylesheet">
<link type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/gridforms/1.0.10/gridforms.min.css" rel="stylesheet">


<div class="container-fluid">
<div data-widget-group="group1">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default" data-widget='{"draggable": "false"}'>
                <div class="panel-heading">
                    <h2>Envio de Arquivo, Arraste o arquivo abaixo</h2>
                    <div class="panel-ctrls" data-actions-container="" data-action-collapse='{"target": ".panel-body"}'></div>
                    <div class="options">
                        @if($exist == true)
                            <h3 class="text-danger">Já existe arquivo gravado, se enviar novo será sobrescrito</h3>
                        @endif
                    </div>
                </div>
                <div class="panel-body">
                    <form action="/upload" class="dropzone grid-form">
                                {{ csrf_field() }}
                    </form>
                </div>
                <div class="panel-footer">
                <form name="frmbase" id="frmbase" method="post" action="/generation" class="grid-form">
                <fieldset>
                    <div data-row-span="1">
                        <div data-field-span="1">
                            O arquivo será salvo em  storage/der/process.sql.
                            Crie todo seu relacionamento, tipos, campos no MySqlWorkbench como um DER e exporte como "SQL Create Script" o resto o GeneratorForLaravel cria para você
                            a estrutura básica de arquivo necessário para o laravel conseguir gerar relatórios, incluir, alterar, remover, migration,
                            o Sistema não remove nem altera os dados em formulários apenas adiciona os novos e gera os migrate.
                            Tabelas que contenham "S" no final Ex: Alunos será criado o objeto "Aluno"
                        </div>
                    </div>    
                </fieldset>
                <fieldset>
                    <div data-row-span="3">
                        <div data-field-span="1">
                            <label>Namespace Principal</label>                     
                            <input type="text" required name="namespace" id="namespace" placeholder="Namespace da Aplicação Ex: MeuSistema" value="App" />
                        </div>
                        <div data-field-span="1">
                            <label>Modelos em app/</label>                     
                            <input type="text" required name="directory" id="directory" placeholder="Salvar modelo no Diretório Ex: Entities" value="Entities">
                        </div>  
                        <div data-field-span="1">
                            <label>Nome Arquivo Rotas em routes/ não utilize web.php</label>                     
                            <input type="text" required name="fileroutes" id="fileroutes" placeholder="Nome do Arquivo de Rotas Ex: generated" value="generated.php">
                        </div> 
                        
                    </div>            
                </fieldset> 
                <fieldset>
                    <div data-row-span="1">
                        <div data-field-span="1">
                            <label>Criar nome dos model como nome das tabelas</label>                     
                            <input type="checkbox" name="namemodel" id="namemodel" value="Y" />
                        </div>
                        <!-- <div data-field-span="1">
                            <label>Formulário Edição/Inclusão único</label>                     
                            <input type="checkbox" name="form" id="form" value="Y" />
                        </div> -->
                    </div>            
                </fieldset> 
                <fieldset>
                    <div data-row-span="1">
                        <div data-field-span="1">
                            <label>Criar Route usando Resource</label>                     
                            <input type="checkbox" name="resource" id="resource" value="Y" />
                            Se marcado a rota ficará Route::resource() senão será criado get/put/delete/post
                        </div>   
                    </div>            
                </fieldset> 
                    <fieldset>
                        <div data-row-span="3">
                            <div data-field-span="1">
                                <label>Criar Controllers em app/Http/Controllers/</label> 
                                <input type="text" readonly="false" name="controller" id="controller" value="app/Http/Controllers/" placeholder="Caminho para salvar Controllers" />
                            </div>
                            <div data-field-span="1">
                                <label>Salvar Validators em app/Validators</label> 
                                <input type="text" readonly="false" name="validators" id="validators" value="app/Validators/" placeholder="Caminho para salvar Validators" />
                            </div>
                            <div data-field-span="1">
                                <label>Criar Services em app/Services</label> 
                                <input type="text" readonly="false" name="services" id="services" value="app/Services/" placeholder="local para Criação dos Services" />
                            </div>       
                    </fieldset> 
                    <fieldset>
                        <div data-row-span="1">
                            <!-- <div data-field-span="1">
                                <label>Tabela de Usuários</label> 
                                <input type="text" name="users" id="users" value="User" />
                            </div> -->
                            <div data-field-span="1">
                                <label>Arquivo para Tradução</label> 
                                <input type="checkbox" checked name="translate" id="translate" value="Y"> &nbsp;Será Criado arquivo de Tradução em config/translate.php
                            </div>       
                    </fieldset> 
                    <fieldset>
                        <div data-row-span="2">
                            <div data-field-span="1">
                                <label>Conexão Padrão</label> 
                                <input type="text" name="connection" id="connection" value="mysql" placeholder="Nome da Conexão padrão" />
                            </div>
                            <div data-field-span="1">
                                <label>Incluir Conexão nos Models/Migrations</label> 
                                <input type="checkbox" name="addcon" id="addcon" value="Y"> &nbsp;SIM
                            </div>
                            <!-- <div data-field-span="1">
                                <label>Definir protected para Datas no modelo</label> 
                                <input type="checkbox" name="adddate" id="adddate" value="Y"> &nbsp;destaca campos na váriavel date no modelo
                            </div>   -->
                    </fieldset> 
                    <!-- <fieldset>
                        <div data-row-span="3">
                            <div data-field-span="1">
                                <label>Host Conexão, alterado no .env</label> 
                                <input type="text" name="dbhost" id="dbhost" value="127.0.0.1" placeholder="Host de Conexão" />
                            </div>
                            <div data-field-span="1">
                                <label>Usuário Banco, alterado no .env</label> 
                                <input type="text" name="dbuser" id="dbuser" value="root" placeholder="Usuário Banco" />
                            </div>
                            <div data-field-span="1">
                                <label>Senha Banco, alterado no .env</label> 
                                <input type="text" name="dbsenha" id="dbsenha" value="" placeholder="Senha do Banco" />
                            </div>
                    </fieldset>  -->
                    <fieldset>
                        <div data-row-span="1">
                            <div data-field-span="1">
                                <label>SEND FORM</label>                     
                                <button type="submit" class="btn btn-danger margin pull-right">
                                    <i class="ti ti-settings">PROCESSAR ARQUIVO</i></button>
                            </div>        
                    </fieldset>    
                </form>    
                </div>    
            </div>
        </div>
    </div>
</div>
    
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.4.0/min/dropzone.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/gridforms/1.0.10/gridforms.min.js"></script>
