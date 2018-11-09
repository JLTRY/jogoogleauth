

function googlelog()
{
	jQuery('#login-form input[name="option"]').val("com_jgoogle");
	jQuery('#login-form').attr('action', '/index.php?option=com_jgoogle&task=user.login');
	Joomla.submitbutton('user.login');
	//Joomla.submitform('user.login');
}