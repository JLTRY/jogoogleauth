<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_jogoogleauth
 *
 * @copyright   Copyright (C) 2005 - 2016 JL Tryoen, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace JLTRY\Component\Jogoogleauth\Site\Helper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * JGoogle component helper.
 *
 * @param   string  $submenu  The name of the active view.
 *
 * @return  void
 *
 * @since   1.6
 */
abstract class JOGoogleAuthHelper
{
    /**
     * This method will return a user object
     *
     *
     * @param	array	$response Holds the user data.
     * @param	array	$options	Array holding options (remember, autoregister, group).
     *
     * @return	object	A User object
     */	
    public static function getUser($response, $options = array())
    {
        if ($id = intval(UserHelper::getUserId($response['username'])))  {
            $instance = User::getInstance();
            $instance->load($id);
            //save password
            //$instance->set('password'		, UserHelper::hashPassword($response['password']));
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
     * @return	object	A User object
     */	
    
     
    public static function registerUser($response)
    {
        $config	= ComponentHelper::getParams('com_users');
        // Default to Registered.
        $defaultUserGroup = $config->get('new_usertype', 2);
        $instance = User::getInstance();
        $instance->set('id'			, 0);
        $instance->set('name'			, $response['username']);
        $instance->set('username'		, $response['username']);
        $instance->set('password'		, UserHelper::hashPassword($response['password']));
        $instance->set('email'			, $response['email']);	// Result should contain an email (check)
        $instance->set('usertype'		, 'deprecated');
        $instance->set('groups'		, array($defaultUserGroup));

        if (!$instance->save()) {
            Factory::getApplication()->enqueueMessage( $instance->getError(),'error');
        }
        return $instance;
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
    
    public static function Log($msg){
        $LOGFILE = dirname(__FILE__) . "/../log/log.oauth.txt";
        $dir = dirname(__FILE__) . "/../log";
        if (!JOGoogleAuthHelper::folder_exist($dir))
            mkdir($dir);
        $fp = fopen($LOGFILE, "a");
        fwrite($fp, "[OAUTH] " . date("H:i:s").":" .  $msg ."\n");
        fclose($fp);
    }
}

