<?php
// No direct access
defined('_JEXEC') or die;

// Include the login functions only once
JLoader::register('ModJGoogleHelper', __DIR__ . '/helper.php');

$params->def('greeting', 1);

$type             = ModJGoogleHelper::getType();
$return           = ModJGoogleHelper::getReturnUrl($params, $type);
$user             = JFactory::getUser();
$layout           = $params->get('layout', 'default');

// Logged users must load the logout sublayout
if (!$user->guest)
{
	$layout .= '_logout';
}

require JModuleHelper::getLayoutPath('mod_jgoogle', $layout);
?>