(function($){
	$(document).ready(function() {
		var ret = $("input[name='return']").val();
		if (redirecturi != '') {
			ret = redirecturi;
		}
		$('.plg_google_login_button').click(function() {window.location = base +'index.php?option=com_jogoogleauth&task=user.login&XDEBUG_SESSION_START=test&return=' + btoa(ret);});
	});
}(jQuery));