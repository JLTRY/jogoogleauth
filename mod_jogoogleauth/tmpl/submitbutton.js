

function googlelog()
{
	jQuery('.mod-login input[name="option"]').val("com_jgoogle");
	jQuery('.mode-login').attr('action', '/index.php?option=com_jgoogle&task=user.login');
	Joomla.submitbutton('user.login');
}