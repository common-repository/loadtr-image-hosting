$(document).ready(function(){
	if($("#tags").attr('value')=='') {$("#tags").attr('value',__loadtr_dil_1)}
	
	
   $("#btn_yukle").click(function() {
		var hata='';
		var loadtr_userfile = $("#userfile").attr('value');
		var loadtr_dilkod = $("#dilkod").attr('value');
		var loadtr_tags = $("#tags").attr('value');
		var loadtr_boyut = $("input[name='boyut']:checked").attr('value');
		
 		if(loadtr_userfile=='') {
			hata = __loadtr_dil_3;
		} else if (loadtr_dilkod=='') {
			hata = __loadtr_dil_5;
		} else if (loadtr_tags == '' || loadtr_tags==__loadtr_dil_1) {
			hata = __loadtr_dil_2;
		} else if ($("input[name='boyut']").is(":checked")===false) {
			hata = __loadtr_dil_4;
		}
		
		if(hata!='') {
			$('.yukleme').html('<div class="hata">'+ hata +'</div>');
		} else {
			$('.yukleme').html('<div class="yukleniyor"><img src="'+__loadtr_eklenti_url+ '/images/ajax-loader.gif"><br />'+__loadtr_dil_6+'</div>');
			$('#image-form').submit();
		}
		
		
	});	


});