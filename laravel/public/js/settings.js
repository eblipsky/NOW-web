
$( document ).ready(function() {

    // save system settings
    $(document).on("click", "input.updatesystemsettings", function(e){
        e.preventDefault();

        $.ajax({
            type:"POST",
            async: false,
            url: '/laravel/public/hpc/settings',
            data: $("#SystemSettings").serialize()
            }).done(function(retData) {                                
                alert("settings saved");
            }).fail(function(jqXHR, textStatus, errorThrown) {                
                alert(textStatus + "::" + errorThrown);
            });
    });

});
