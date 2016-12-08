<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_jgoogle
 *
 * @copyright   Copyright (C) 2005 - 2016 JL Tryoen, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


/**
 * JGoogle component helper.
 *
 * @param   string  $submenu  The name of the active view.
 *
 * @return  void
 *
 * @since   1.6
 */
abstract class JGoogleHelper
{
	/**
	 * This method will return a user object
	 *
	 * If options['autoregister'] is true, if the user doesn't exist yet he will be created
	 *
	 * @param	array	$response Holds the user data.
	 * @param	array	$options	Array holding options (remember, autoregister, group).
	 *
	 * @return	object	A JUser object	 
	 */	
	public static function getUser($response, $options = array())
	{
		$instance = JUser::getInstance();
		if ($id = intval(JUserHelper::getUserId($response['username'])))  {
			$instance->load($id);
			return $instance;
		}

		//TODO : move this out of the plugin
		jimport('joomla.application.component.helper');
		$config	= JComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);

		$acl = JFactory::getACL();

		$instance->set('id'			, 0);
		$instance->set('name'			, $response['username']);
		$instance->set('username'		, $response['username']);
		$instance->set('password'		, JUserHelper::hashPassword($response['password']));
		$instance->set('email'			, $response['email']);	// Result should contain an email (check)
		$instance->set('usertype'		, 'deprecated');
		$instance->set('groups'		, array($defaultUserGroup));

		//If autoregister is set let's register the user
		$autoregister = true;

		if ($autoregister) {
			if (!$instance->save()) {
				return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
			}
		}
		else {
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}

		return $instance;
	}
	
	
	
	public static function my_log($msg){		
		$LOGFILE = dirname(__FILE__) . "/../log/log.oauth.txt";
		mkdir(dirname(__FILE__) . "/../log");
		$fp = fopen($LOGFILE, "a");
		fwrite($fp, "[OAUTH] " . date("H:i:s").":" .  $msg ."\n");
		fclose($fp);

	}
}

