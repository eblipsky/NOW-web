

    var Overview = {
        
        pipeline: '',        
        init: function() {
            
        },
        getOverview: function() {
            $("#pipelineOverview").html("<img src='/laravel/public/img/loader.gif'>");            
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/pipeline/'+this.pipeline+'/overview/'               
            }).done(function(retData) {                
                $("#pipelineOverview").html(retData);    
            }).error(function() {                
                $("#pipelineOverview").html('something bad happened!!');    
            });
        },
        clearOverview: function() {
            $("#pipelineOverview").html('');    
        }      
    
    };
        

