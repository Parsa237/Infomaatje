if(!$){
  $ = jQuery;
}
function IsEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
  }

  $(document).ready(function() {
    $('#dnSubmit').css({opacity: 0.3});
    $("input#dnAkkoord, input#vwAkkoord").on('click', function() {
      if($(this).prop("checked")) {
        $("input#dnEmail, input#vwEmail").attr("placeholder","Voer hier je e-mailadres in").prop("disabled", false);
        $("button#dnSubmit, button#vwSubmit").prop("disabled", false).css({opacity: 1});
      } else {
        $("input#dnEmail, input#vwEmail").attr("Vergeet niet eerst onze privacyverklaring te accoderen ...").prop("disabled", true);
        $("button#dnSubmit, button#vwSubmit").prop("disabled", true).css({opacity: 0.3});
      }
    });

    $("#dnAanmelden").on('click', '#dnSubmit', function() {
      try {
        if(!IsEmail($('#dnEmail').val())) throw new Error('Het e-mailadres is niet ingevuld of onjuist.');
          
        $('#dnSubmit').css({'background':'url(isend.png) no-repeat 142px center' , 'background-color':'#666666' , '-webkit-transition':'1s' , 'transition':'1s'});
        $.post("/wp-content/plugins/aanmelden/dnReqAanmelden.php", {
          wie: $('input[name="wie"]').val(),
          email: $('#dnEmail').val()
        }, function(oResult) {
          if(oResult.code == 1) {
            $('#message').html(oResult.message);
          } else {
            $('#message').html('<span class="error">' + oResult.message + '</span>');
          }
        }); 
      } catch(e) {
        alert(e.message);
      }
      return false;
    });

    $("#vwAanmelden").on('click', '#vwSubmit', function() { 
      if(IsEmail($('#vwEmail').val())){
        $('#vwSubmit').css({'background-color':'#666666' , '-webkit-transition':'1s' , 'transition':'1s'});
        $.post("/wp-content/plugins/aanmelden/vwReqAanmelden.php", {
          email: $('#vwEmail').val(),
        }, function(oResult) {
          if(oResult.code == 1){
            $('#message').html(oResult.message);
          } else {
            $('#message').html(oResult.message).addClass("error"); 
          }
        }); 
      } else {
        alert('Het e-mailadres is verplicht.');
      }
      return false;
    });
  }); 
