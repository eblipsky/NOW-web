 var Files = {
        init: function() {

        },
        Import: function() {
            $("#ImportLoading").html("<img src='/laravel/public/img/loader.gif'>");
            $.ajax({
                type:"GET",
                url: '/laravel/public/hpc/files/import'
            }).done(function(retData) {                
                $("#ImportLoading").html('');
                jQuery.each(retData, function(idx,itm){
                    $("#ImportLoading").append(itm + "</br>");
                });
            }).error(function() {
                $("#ImportLoading").html('something bad happened!!');
            });
        }
};

