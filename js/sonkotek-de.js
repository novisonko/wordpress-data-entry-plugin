/**
*Process Sonkotek SDE plugin forms
*
*@document name: Sonkotek Data Entry CSS Javascript
*
*@author Novi Sonko
*@email hello@sonkotek.com
*@url https://sonkotek.com
*
*/
jQuery(document).ready( function($){

  $("#sde-form-1").submit(function(event){

    event.preventDefault();

    var config= sonkotekSdeSaveForm;
    var value='';
    var $values= $('#sde-form-1 input[type="text"]');
    var submitted={};

    $values.each(function(index){
      var value= $(this).val();
      if (
        $(this).attr("name")
        && $(this).val()
        ){
      submitted[$(this).attr("name")]= $(this).val();
      }
    });

  	$.ajax({
  		 type : "post",
  		 dataType : "json",
  		 url : config.ajaxURL,
  		 data : {action:config.ajaxAction, nonce:config.ajaxNonce, submitted:submitted},
       statusCode:{
         200 : function(data){
           //console.log("SDE plugin response status code is 200");
           $(".sde-form-msg").hide().append($(data.msg)).fadeIn('slow');
           clearMsg();
        },
         503 : function(data){
           console.log("Sorry, an error occurred in SDE plugin. Response status code is 503");
           $(".sde-form-msg").hide().append($(data.msg)).fadeIn('slow');
           clearMsg();
         },
         404 : function(data){
           console.log("Sorry, resource not found in SDE plugin. Response status code is 404");
           $(".sde-form-msg").hide().append($(data.msg)).fadeIn('slow');
           clearMsg();
         }
       }
  	});// ajax
  });// form > submit()

  /**
  *Clear message box
  */
  var clearMsg= function(){
      window.setTimeout(function(){
        $(".sde-form-msg p").fadeOut('slow').remove();
      }, 10000);
  }
})
