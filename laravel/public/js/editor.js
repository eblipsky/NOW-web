

    var Editor = {
        pipeline: '',
        queue: '',
        init: function() {
            
        },
        getQueueList: function() {
            $("#QueueList").html("<img src='/laravel/public/img/loader.gif'>");
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/queues/'               
            }).done(function(retData) {                
                $("#QueueList").html(retData);    
            }).error(function() {                
                $("#QueueList").html('something bad happened!!');    
            });
        },
        clearQueueList: function() {
            $("#QueueList").html('');    
        },
        getFileLog: function (fq) {
            $("#FileLog").html("<img src='/laravel/public/img/loader.gif'>");
            $("#FileLogBox").show();
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/file/'+fq                
            }).done(function(retData) {                
                $("#FileLog").html(retData);    
            }).error(function() {                
                $("#FileLog").html('something bad happened!!');    
            });
        },
        getGlobalVars: function () {
            $("#GlobalVars").html("<img src='/laravel/public/img/loader.gif'>");
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/vars'                
            }).done(function(retData) {                
                $("#GlobalVars").html(retData);    
            }).error(function() {                
                $("#GlobalVars").html('something bad happened!!');    
            });
        },
        getTemplateCmds: function () {
            $("#TemplateCommands").html("<img src='/laravel/public/img/loader.gif'>");
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/templatecmds'                
            }).done(function(retData) {                
                $("#TemplateCommands").html(retData);    
            }).error(function() {                
                $("#TemplateCommands").html('something bad happened!!');    
            });
        },
        getNewFiles: function () {
            $("#NewFiles").html("<img src='/laravel/public/img/loader.gif'>");
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/newfiles'                
            }).done(function(retData) {                
                $("#NewFiles").html(retData);    
            }).error(function() {                
                $("#NewFiles").html('something bad happened!!');    
            });
        },
        getPipelineVars: function() {
            $("#PipelineVars").html("<img src='/laravel/public/img/loader.gif'>");
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/vars'            
            }).done(function(retData) {                
                $("#PipelineVars").html(retData);    
            }).error(function() {                
                $("#PipelineVars").html('something bad happened!!');    
            });
        },            
        getQueueEdit: function() {
            $("#QueueEdit").html("<img src='/laravel/public/img/loader.gif'>");
            $("#QueueEditBox").show();
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/queue/'+this.queue,                
            }).done(function(retData) {                
                $("#QueueEdit").html(retData);                 
            }).error(function() {                
                $("#QueueEdit").html('something bad happened!!');    
            });
        },
        clearQueueEdit: function() {
            $("#QueueEdit").html('');   
            $("#QueueEditBox").hide();
        },
        createPipeline: function(name) {    
            $.ajax({
                type:"POST",
                async: false,
                url: '/laravel/public/hpc/pipeline',
                data: { pipeline: name }
            }).done(function(retData) {                
            }).error(function() {                
            });    
        },
        deletePipeline: function(name) {
            $.ajax({
                type:"DELETE",
                async: false,
                url: '/laravel/public/hpc/pipeline/'+name             
            }).done(function(retData) {                                
            });
        },
        clearPipeline: function() {               
            $.ajax({
                type:"POST",
                async: false,
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/clear'            
            }).done(function(retData) {  
                alert("queues cleared");
            });
        },        
        createQueue: function(name) {
            $.ajax({
                type:"POST",
                async: false,
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/queue',
                data: { queue: name }
            }).done(function(retData) {                                
            });
        },
        deleteQueue: function(name) {
            $.ajax({
                type:"DELETE",
                async: false,
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/queue/'+name                
            }).done(function(retData) {                                
            });
        },
        updateQueue: function() {
            $.ajax({
                type:"POST",
                async: false,
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/queue/'+this.queue,
                data: $("#editqueue").serialize()
            }).done(function(retData) {    
                //alert(retData);
            });
        },
        updatePipelineVars: function() {
            $.ajax({
                type:"POST",
                async: false,
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/vars',
                data: $("#pipelinevars").serialize()
            }).done(function(retData) {                                
            });
        },
        getNodeList: function() {
            $("#NodeList").html("<img src='/laravel/public/img/loader.gif'>");
            $.ajax({
                type:"GET",                
                url: '/laravel/public/hpc/nodes'
            }).done(function(retData) {                
                $("#NodeList").html(retData);    
            }).error(function() {                
                $("#NodeList").html('something bad happened!!');    
            });
        },
        getFileList: function(hideStartButton,fqs) {
            $("#FileList").html("<img src='/laravel/public/img/loader.gif'>");                        
            
            $.ajax({
                type:"GET",                
                url: '/laravel/public/hpc/allfiles'
            }).done(function(retData) {                
                $("#FileList").html(retData);    
                if (hideStartButton) {
                    $('.startfiles').remove();
                }
                //move file from array passed in over
                if (fqs != null) {
                    fqs.forEach( function(fq) {
                        $("select[name='avail']").find("option[value='"+fq+"']").remove().appendTo("select[name='start\[\]']");
                    });
                }
                
            }).error(function() {                
                $("#FileList").html('something bad happened!!');    
            });
        },        
        setNodePipeline: function(node,pipeline) {    
            $.ajax({
                type:"POST",
                url: "/laravel/public/hpc/nodes/"+node+"/pipeline",
                data: { pipeline: pipeline }
            }).done(function(retData) {                
            }).error(function() {                
            });    
        },        
        setTemplateCmd: function(cmd) {              
            $.ajax({
                type:"POST",
                url: "/laravel/public/hpc/templatecmds",
                data: { name: this.pipeline+'_'+this.queue, cmd: cmd }
            }).done(function(retData) {  
                //alert(retData);
            }).error(function(e) {                
                //alert(JSON.stringify(e));
            });    
        }, 
        moveFiles: function(to,fqs) {  
            $("#CurrentFiles").html("<img src='/laravel/public/img/loader.gif'>");                        
            $.ajax({
                type:"POST",                  
                url: '/laravel/public/hpc/movefiles',
                data: { pipeline: this.pipeline, from: this.queue, to: to, fqs: fqs }
            }).done(function(retData) {                  
            });   
        },        
        setPriority: function(priority,fqs) {  
            $("#CurrentFiles").html("<img src='/laravel/public/img/loader.gif'>");              
            $.ajax({
                type:"POST",                  
                url: '/laravel/public/hpc/files/priority',
                data: { priority: priority, fqs: fqs }
            }).done(function(retData) {                  
                //alert(retData);
            });   
        },
        startFiles: function(fqs) {   
            $.ajax({
                type:"POST",
                async: false,
                url: '/laravel/public/hpc/startfiles',
                data: { pipeline: this.pipeline, fqs: fqs }
            }).done(function(retData) {   
                //alert(retData);
            }).error(function() {                                
            });  
        }
    
    };
        

