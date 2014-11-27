jQuery(document).ready(function ( $){
    $("#recurring").change( function() {
        if( $(this).is(":checked")){
            $("#rec_tr").show();
            $("#rec_tr1").show();
            $("#rec_tr2").show();
        }else{
            $("#rec_tr").hide();
            $("#rec_tr1").hide();
            $("#rec_tr2").hide();
        }
    });
});