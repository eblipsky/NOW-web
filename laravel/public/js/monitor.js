$(function() {

    //load queue edit form
    $(document).on("click", "a.queue", function(e){
        e.preventDefault();   
        Editor.queue = $(this).text();
        Editor.getQueueEdit();
    });

    // update pipeline vars
    $(document).on("click", "input.updatepipelinevars", function(e){
        e.preventDefault();                                           
        Editor.updatePipelineVars();
        Editor.getPipelineVars();
    });

    //create a pipline
    $(document).on("click", "input.createpipeline", function(e){
        e.preventDefault();                                           
        Editor.createPipeline($("#pipeline").val())
        location.reload();            
    });

    // delete pipelines
    $(document).on("click", "a.deletepipeline", function(e){
        e.preventDefault();                                
        if (confirm("Are you sure want to delete " + $(this).attr('pipelinename'))) {
            Editor.deletePipeline($(this).attr('pipelinename'));
            location.reload();            
        } else {
            return false;
        }
    });
    // clear pipeline
    $(document).on("click", "a.clearpipeline", function(e){
        e.preventDefault();                                
        if (confirm("Are you sure want to clear " + $(this).attr('pipelinename'))) {
            Editor.clearPipeline();
            Editor.getQueueList();
        } else {
            return false;
        }
    });

    //create a queue
    $(document).on("click", "input.createqueue", function(e){
        e.preventDefault();                                           
        Editor.createQueue($("#queue").val());
        Editor.getQueueList();
    });

    // delete queue
    $(document).on("click", "a.deletequeue", function(e){
        e.preventDefault();                                
        if (confirm("Are you sure want to delete " + $(this).attr('queuename'))) {
            Editor.deleteQueue($(this).attr('queuename'));
            Editor.getQueueList();
        } else {
            return false;
        }
    }); 
    
    //show file log
    $(document).on("click", "a#showfilelog", function(e){
        e.preventDefault();                                        
        Editor.getFileLog($(this).attr('fq'));
    }); 

    // update a queue
    $(document).on("click", "input.updatequeue", function(e){
        e.preventDefault();                                                       
        Editor.updateQueue();        
        if (Editor.pipeline == "CommandTemplates") {
            //if we are a template save the template
            Editor.setTemplateCmd($(queue_cmd).val());
            Editor.getTemplateCmds();
        }
        Editor.getQueueEdit();
    });

    // move files
    $(document).on("click", "input.movefiles", function(e){
        e.preventDefault(); 

        var checkValues = $("input[name='move\[\]']:checked").map(function() {
            return $(this).val();
        }).get();                   

        Editor.moveFiles($("#toqueue").val(),checkValues);
        Editor.getQueueList();
        Editor.getQueueEdit();

    });

    // start files
    $(document).on("click", "input.startfiles", function(e){
        e.preventDefault();                                                        

        var checkValues = $("select[name='start\[\]'] option").map(function() {
            return $(this).val();
        }).get();            

        Editor.startFiles(checkValues);
        Editor.getFileList();
        Editor.getQueueList();

    });

    // update node pipeline
    $(document).on("change", "select.nodepipeline", function(e){
        //e.preventDefault();                                                       
        Editor.setNodePipeline($(this).attr('node'),$(this).val())
    });

    $( ".tabs" ).tabs();

    $('#accoridan').accordion({
            collapsible: true, 
            active: false, 
            animate: false,
            heightStyle: "content",
            beforeActivate: function( event, ui ) 
                    { 
                        if (ui.newHeader.text() == "") {
                            //closing
                            Editor.pipeline = "";
                            Editor.clearQueueEdit();
                            Editor.clearQueueList();
                        } else {
                            Editor.pipeline = ui.newHeader.text(); 
                            Editor.clearQueueEdit();
                            Editor.getPipelineVars();
                            Editor.getQueueList();
                            Editor.getFileList();                                                        
                            
                            if (Editor.pipeline == "CommandTemplates") {
                                $( "#FileList" ).hide();
                                $( "#PipelineVars" ).hide();
                                $( "#pipelinecontent" ).hide();
                                $( "#QueueList" ).appendTo( "#tabs-q-"+Editor.pipeline );
                            } else {
                                //$( "#pipelinecontent" ).appendTo( ui.newPanel );  
                                $( "#PipelineVars" ).appendTo( "#tabs-vars-"+Editor.pipeline );                                
                                $( "#QueueList" ).appendTo( "#tabs-q-"+Editor.pipeline );
                                $( "#FileList" ).appendTo( "#tabs-files-"+Editor.pipeline );
                                $( "#FileList" ).show();
                                $( "#PipelineVars" ).show();
                                $( "#pipelinecontent" ).show();
                            }
                        }
                    }
    });

    $(document).on('click','#selectall', function() {
        $('.selectedId').attr('checked', $(this).is(":checked"));
    });

    $(document).on('click','#filemoveright', function() {
        $("select[name='avail'] > option:selected").each(function(){
            $(this).remove().appendTo("select[name='start\[\]']");
        })
    });

    $(document).on('click','#filemoveleft', function() {
        $("select[name='start\[\]'] > option:selected").each(function(){
            $(this).remove().appendTo("select[name='avail']");
        })
    });


}); 