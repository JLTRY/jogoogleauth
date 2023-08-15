

$(document).ready(function() {
	//jQuery('.mod-login input[name="option"]').val("com_jgoogle");
	$('.plg_google_login_button').click(function() {window.location = '/index.php?option=com_jgoogle&task=user.login&XDEBUG_SESSION_START=test';});
	//Joomla.submitbutton('user.login');
	//Joomla.submitform('user.login');
});