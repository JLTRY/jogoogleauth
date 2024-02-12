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
	 *
	 * @param	array	$response Holds the user data.
	 * @param	array	$options	Array holding options (remember, autoregister, group).
	 *
	 * @return	object	A JUser object	 
	 */	
	public static function getUser($response, $options = array())
	{		
		if ($id = intval(JUserHelper::getUserId($response['username'])))  {
			$instance = JUser::getInstance();
			$instance->load($id);
			//save password
			//$instance->set('password'		, JUserHelper::hashPassword($response['password']));
			//$instance->save();
			return $instance;
		}
		return null;
	}
	
	/**
	 * This method will return a user object
	 *
	 *
	 * @param	array	$response Holds the user data.
	 * @param	array	$options	Array holding options (remember, autoregister, group).
	 *
	 * @return	object	A JUser object	 
	 */	
	
	 
	public static function registerUser($response)
	{
		//TODO : move this out of the plugin
		jimport('joomla.application.component.helper');
		$config	= JComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);
		$instance = JUser::getInstance();
		$instance->set('id'			, 0);
		$instance->set('name'			, $response['username']);
		$instance->set('username'		, $response['username']);
		$instance->set('password'		, JUserHelper::hashPassword($response['password']));
		$instance->set('email'			, $response['email']);	// Result should contain an email (check)
		$instance->set('usertype'		, 'deprecated');
		$instance->set('groups'		, array($defaultUserGroup));

		if (!$instance->save()) {
			return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
		}
		return $instance;
	}	
	
	
	public static function sendregisteredUserMail($data)
	{
		$emailSubject = JText::sprintf(
			'COM_USERS_EMAIL_ACCOUNT_DETAILS',
			$data['name'],
			$data['sitename']
		);

		$emailBodyAdmin = JText::sprintf(
			'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
			$data['name'],
			$data['username'],
			$data['siteurl']
		);

		// Get all admin users
		$db =  JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->clear()
			->select($db->quoteName(array('name', 'email', 'sendEmail')))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('sendEmail') . ' = ' . 1);

		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

			return false;
		}

		// Send mail to all superadministrators id
		foreach ($rows as $row)
		{
			$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);

			// Check for an error.
			if ($return !== true)
			{
				$this->setError(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

				return false;
			}
		}
		

		// Check for an error.
		if ($return !== true)
		{
			$this->setError(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

			// Send a system message to administrators receiving system mails
			$db = $this->getDbo();
			$query->clear()
				->select($db->quoteName(array('name', 'email', 'sendEmail', 'id')))
				->from($db->quoteName('#__users'))
				->where($db->quoteName('block') . ' = ' . (int) 0)
				->where($db->quoteName('sendEmail') . ' = ' . (int) 1);
			$db->setQuery($query);

			try
			{
				$sendEmail = $db->loadColumn();
			}
			catch (RuntimeException $e)
			{
				$this->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

				return false;
			}

			if (count($sendEmail) > 0)
			{
				$jdate = new JDate;

				// Build the query to add the messages
				foreach ($sendEmail as $userid)
				{
					$values = array(
						$db->quote($userid),
						$db->quote($userid),
						$db->quote($jdate->toSql()),
						$db->quote(JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')),
						$db->quote(JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username']))
					);
					$query->clear()
						->insert($db->quoteName('#__messages'))
						->columns($db->quoteName(array('user_id_from', 'user_id_to', 'date_time', 'subject', 'message')))
						->values(implode(',', $values));
					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (RuntimeException $e)
					{
						$this->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

						return false;
					}
				}
			}

			return false;
		}
	}
	
	
	/**
	 * Checks if a folder exist and return canonicalized absolute pathname (long version)
	 * @param string $folder the path being checked.
	 * @return mixed returns the canonicalized absolute pathname on success otherwise FALSE is returned
	 */
	static function folder_exist($folder)
	{
		// Get canonicalized absolute pathname
		$path = realpath($folder);

		// If it exist, check if it's a directory
		if($path !== false AND is_dir($path))
		{
			// Return canonicalized absolute pathname
			return $path;
		}

		// Path/folder does not exist
		return false;
	}
	
	public static function my_log($msg){
		$LOGFILE = dirname(__FILE__) . "/../log/log.oauth.txt";
		$dir = dirname(__FILE__) . "/../log";
		if (!JGoogleHelper::folder_exist($dir))
			mkdir($dir);
		$fp = fopen($LOGFILE, "a");
		fwrite($fp, "[OAUTH] " . date("H:i:s").":" .  $msg ."\n");
		fclose($fp);
	}
}

