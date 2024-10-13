(function($){
	$(document).ready(function() {
		var url = new Url;
		$('.plg_google_login_button').click(function() {window.location = base +'index.php?option=com_jgoogle&task=user.login&XDEBUG_SESSION_START=test&return=' + btoa(url);});
	});
}(jQuery));