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
                            <h3 class="text-danger">FILE process.sql exists, if proccess continue file update</h3>
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
                            Attention the Archive will be saved in the storage / der / process.sql directory, and will always be rewritten to every upload.
                        </div>
                    </div>    
                </fieldset>
                <fieldset>
                    <div data-row-span="3">
                        <div data-field-span="1">
                            <label>Namespace</label>                     
                            <input type="text" required name="namespace" id="namespace" placeholder="Namespace for Application Ex: App or MySyst" />
                        </div>
                        <div data-field-span="1">
                            <label>Directory Model</label>                     
                            <input type="text" required name="directory" id="directory" placeholder="Directory for save Models Ex: Model or Entities">
                        </div>  
                        <div data-field-span="1">
                            <label>File for routes</label>                     
                            <input type="text" required name="fileroutes" id="fileroutes" placeholder="File for routes Ex: import or web or routes">
                        </div> 
                        
                    </div>            
                </fieldset> 
                    <fieldset>
                        <div data-row-span="3">
                            <div data-field-span="1">
                                <label>Create Controller</label> 
                                <input type="checkbox" name="controller" id="controller" value="Y"> &nbsp;Create in app/Http/Controllers/automatic
                            </div>
                            <div data-field-span="1">
                                <label>Create Validators</label> 
                                <input type="checkbox" name="validators" id="validators" value="Y"> &nbsp;Create in app/validators
                            </div>
                            <div data-field-span="1">
                                <label>Use Services</label> 
                                <input type="checkbox" name="services" id="services" value="Y"> &nbsp;Create in app/services
                            </div>       
                    </fieldset> 
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
