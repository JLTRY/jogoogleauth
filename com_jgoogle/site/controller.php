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
JLoader::import('components.com_jgoogle.helpers.jgoogle', JPATH_SITE);

/**
 * JGoogle Component Controller
 *
 * @since  0.0.1
 */
class JGoogleController extends JControllerLegacy
{
	protected $default_view = 'jgoogle';
	
}
