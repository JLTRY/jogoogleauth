<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jgoogle
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JLoader::import('components.com_jgoogle.helpers.jgoogle', JPATH_SITE);
JFactory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR, null ,true);
JFactory::getLanguage()->load('com_users', JPATH_SITE, null ,true);


// Get an instance of the controller prefixed by JGoogle
$controller = JControllerLegacy::getInstance('JGoogle');

// Perform the Request task
$input = JFactory::getApplication()->input;
$task = $input->getCmd('task');
JGoogleHelper::my_log("task:" . $task);
if (empty($task)) {
	$task = "login";
}
JGoogleHelper::my_log("task:" . $task);
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
