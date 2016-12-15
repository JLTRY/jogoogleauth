<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JFactory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR, null ,true);
JFactory::getLanguage()->load('com_users', JPATH_SITE, null ,true);


// Set some global property
$document = JFactory::getDocument();

// Access check: is this user allowed to access the backend of this component?
if (!JFactory::getUser()->authorise('core.manage', 'com_jgoogle'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}


// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance('JGoogle');

// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
