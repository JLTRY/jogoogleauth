<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\OAuth2\Client;
JLoader::register('JGoogleController', JPATH_COMPONENT . '/controller.php');
JLoader::import('components.com_jgoogle.helpers.jgoogle', JPATH_SITE);
/**
 * Registration controller class for Users.
 *
 * @since  1.6
 */
class JGoogleControllerUser extends JGoogleController
{
	private $oauth_client;
	private $Itemid;
	private $log = false;
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                          'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   3.5
	 */
	public function __construct($config = array())
	{
		$this->log = false;
		$this->log("construct: JGoogleControllerUser:". print_r($config, true));
		$app =  JFactory::getApplication();
		$ItemId = $app->input->getInt('Itemid', null);
		$this->log("construct:Itemid new client". print_r($ItemId, true));
		$oauth_client = new Client([], null, null, $app, $this);
		$this->log("construct:client OK");
		$oauth_client->setOption('sendheaders',true);
		$oauth_client->setOption('client_id','token');
		$oauth_client->setOption('scope',array('email','profile'));
		$oauth_client->setOption('requestparams',
							   array('state'=>'jauth',
							  'task'=> $app->input->get('task', 'login'),
							  'access_type'=>'offline'));
		jimport('joomla.application.component.helper'); // Import component helper library
		$params = $app->getParams('com_jgoogle');
		$this->log("params:". print_r($params, true));
		$this->log("itemid2 set:". print_r($this->ItemIds, true));
		$this->ItemId = $params->get('login_redirect_menuitem', '');
		$this->log("itemid2:". print_r($this->ItemIds, true));
		if (empty($this->ItemId))
			$this->ItemId = $ItemId;
		$oauth_client->setOption('clientid',	$params->get('clientid',''));
		$oauth_client->setOption('clientsecret', $params->get('clientsecret',''));
		$oauth_client->setOption('redirecturi', $params->get('redirecturi',''));
		$oauth_client->setOption('authurl','https://accounts.google.com/o/oauth2/v2/auth');
		$oauth_client->setOption('tokenurl','https://www.googleapis.com/oauth2/v4/token');
		$this->log("construct:end before oauth_client" . $this->ItemId);
		$this->oauth_client = $oauth_client; 
		$this->log("construct:end after oauth_client" . $this->ItemId);
		parent::__construct($config);
		$this->log("construct:end" . $this->ItemId);
	}
	
	
	public function log($str)
	{
		if ($this->log) {
			JGoogleHelper::my_log($str);
		}
	}
	
	/**
	 * Method to log in a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function login()
	{
		JGoogleHelper::my_log("authentificate");
		$app = JFactory::getApplication();
		JGoogleHelper::my_log("getapplication OK");
		$app->setUserState( 'Itemid', $this->ItemId);
		JGoogleHelper::my_log("userstate" . $this->ItemId);
		JGoogleHelper::my_log("oauth_client" . print_r(get_class($this->oauth_client), true));
		$this->oauth_client->authenticate();
		JGoogleHelper::my_log("authentificate end");
	}
	
	
	public function auth()
	{
		JGoogleHelper::my_log("google auth");
		$this->oauth_client->setOption('sendheaders',false);
		$this->oauth_client->authenticate();
		if ($this->oauth_client->isAuthenticated())
		{
			JGoogleHelper::my_log("isauthentificated");
			$this->credentials = $credentials = json_decode($this->oauth_client->query('https://www.googleapis.com/oauth2/v1/userinfo?alt=json')->body,true);
			$response = new stdClass();
			// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
			$app = JFactory::getApplication();
			$params = $app->getParams('com_jgoogle');
			$options= array();
			$options['autoregister'] = true;
			JGoogleHelper::my_log("resgistration allowed ");
			$response->username = str_replace('.', '-', preg_split("/@/", $credentials['email'])[0]);
			$response->name = $response->username;
			$response->email = $credentials['email'];
			$response->type = 'GMail';
			$pwd = $params->get('secretpassword','JLT');
			$response->password_clear = $response->email . $pwd;
			$response->password = $response->password_clear;
			$response->siteurl = JUri::base();
			$config = JFactory::getConfig();
			$response->fromname = $config->get('fromname');
			$response->mailfrom = $config->get('mailfrom');
			$response->sitename = $config->get('sitename');

			JGoogleHelper::my_log("on user login " . print_r($options, true) . print_r($response, true));
			$user =  JGoogleHelper::getUser((array)$response);
			if ($user == null) {
				JGoogleHelper::my_log("registerUSer" . print_r($results, true));
				$user = JGoogleHelper::registerUser((array)$response);
				$user = JGoogleHelper::sendregisteredUserMail((array)$response);
			}
			else {
				JGoogleHelper::my_log("user is not null 1" . print_r($user, true));
			}
			$user =  JGoogleHelper::getUser((array)$response);
			JGoogleHelper::my_log("user is not null 2" . print_r($user, true));
			$results = $app->login((array)$response, $options);
			$user =  JGoogleHelper::getUser((array)$response);
			JGoogleHelper::my_log("user is not null 3" . print_r($user, true));
			JGoogleHelper::my_log("on user login end" . print_r($results, true));
		}
		if ($this->ItemId)
			$app->redirect(JRoute::_('index.php?Itemid=' . $this->ItemId, false));
		elseif ($app->getUserState( 'Itemid'))
			$app->redirect(JRoute::_('index.php?Itemid=' . $app->getUserState( 'Itemid'), false));
		else
			$app->redirect(JRoute::_('index.php'), false);
	}
	/**
	 * Method to log out a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function logout()
	{
		//JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		// Perform the log out.
		$error  = $app->logout();
		$input  = $app->input;
		$method = $input->getMethod();

		// Check if the log out succeeded.
		if ($error instanceof Exception)
		{
			$app->redirect(JRoute::_('index.php?option=com_jgoogle&view=login', false));
		}

		// Get the return url from the request and validate that it is internal.
		$return = $input->$method->get('return', '', 'BASE64');
		$return = base64_decode($return);

		// Check for a simple menu item id
		if (is_numeric($return))
		{
			if (JLanguageMultilang::isEnabled())
			{

				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('language')
					->from($db->quoteName('#__menu'))
					->where('client_id = 0')
					->where('id =' . $return);

				$db->setQuery($query);

				try
				{
					$language = $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					return;
				}

				if ($language !== '*')
				{
					$lang = '&lang=' . $language;
				}
				else
				{
					$lang = '';
				}
			}
			else
			{
				$lang = '';
			}

			$return = 'index.php?Itemid=' . $return . $lang;
		}
		else
		{
			// Don't redirect to an external URL.
			if (!JUri::isInternal($return))
			{
				$return = '';
			}
		}

		// Redirect the user.
		$app->redirect(JRoute::_($return, false));
	}


}
