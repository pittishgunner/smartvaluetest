$( document ).ready(function() {
    if ($("#resultWrapper").length>0) {
        $(document).on("click","#search",function(e){
            e.preventDefault();
            var inp=$("#countryCode");
            var wait=$("#waiting");
            if (inp.val()=="") {
                inp.addClass("form-control-danger is-invalid").parents(".form-group").addClass("has-danger");
                inp.focus();
                return false;
            } else {
                inp.removeClass("form-control-danger is-invalid").parents(".form-group").removeClass("has-danger");
            }
            wait.show();
            $.post( "api/locations", {jsonrpc:'2.0',method:'getCountriesByCode', params:{countryCode:inp.val()},id:"jsonrpc"+Math.random()}, function(result) {
                wait.hide();
                if (result.error) {
                    $("#result").html('<div class="alert alert-danger" role="alert">Error message: ' + result.error.message + '<br>Error code: ' + result.error.code + '<br></div>');
                } else {
                    if (result.result==false) {
                        $("#result").html('<div class="alert alert-warning" role="alert">We found 0 results for Country Code: "'+inp.val()+'"</div>');
                    } else {
                        $("#result").html('<div class="alert alert-success" role="alert">Country Name: "'+result.result.name+'"<br>Country Prefix: '+result.result.prefix+'"</div>');
                    }
                }
            })
            .fail(function(response) {
                wait.hide();
                $("#result").html('<div class="alert alert-danger" role="alert">' + response.statusText + '</div>');
            });
        });
    }
});