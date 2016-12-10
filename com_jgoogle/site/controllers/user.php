<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
		JGoogleHelper::my_log("construct:". print_r($config, true));
		$app =  JFactory::getApplication();
		$ItemId = $app->input->getInt('Itemid', null);		
		jimport('joomla.oauth2.client');
		$oauth_client = new JOAuth2Client();
		$oauth_client->setOption('sendheaders',true);
		$oauth_client->setOption('client_id','token');
		$oauth_client->setOption('scope',array('email','profile'));
		$oauth_client->setOption('requestparams',
								array('state'=>'jauth',
								'task'=> $app->input->get('task', 'login'),
								'access_type'=>'offline'));
		jimport('joomla.application.component.helper'); // Import component helper library
		$params = $app->getParams('com_jgoogle');
		JGoogleHelper::my_log("params:". print_r($params, true));
		$this->Itemid = $params->get('login_redirect_menuitem', '');
		if (empty($this->Itemid))
			$this->Itemid = $ItemId;
		$oauth_client->setOption('clientid',
						//'106194179834-d798gpdipv6sqahvdg1sh77o3gdkt10u.apps.googleusercontent.com');
						$params->get('clientid',''));
		$oauth_client->setOption('clientsecret',
						//'-TsLcoIDeuzVk9us8_ROUzjl');
						$params->get('clientsecret',''));
		$oauth_client->setOption('redirecturi',
						//'http://www.jltryoen.fr/index.php?option=com_jgoogle&task=user.auth');
						$params->get('redirecturi',''));
		$oauth_client->setOption('authurl','https://accounts.google.com/o/oauth2/v2/auth');
		$oauth_client->setOption('tokenurl','https://www.googleapis.com/oauth2/v4/token');
		$this->oauth_client = $oauth_client; 
		parent::__construct($config);
		JGoogleHelper::my_log("construct:end");
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
		$app->setUserState( 'Itemid', $this->Itemid);
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
			print_r($credentials);
			$response = new stdClass();			
			// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
			$app = JFactory::getApplication();
			$params = $app->getParams('com_jgoogle');
			$options= array();
			$options['autoregister'] = true;
			JGoogleHelper::my_log("resgistration allowed ");
			$response->username = str_replace('.', '-', split('@', $credentials['email'])[0]);
			$response->email = $credentials['email'];
			$response->type = 'GMail';
			$pwd = $params->get('secretpassword','JLT');
			$response->password_clear = $response->email . $pwd;
			$response->password = $response->password_clear;
			JGoogleHelper::my_log("on user login " . print_r($options, true) . print_r($response, true));
			$user =  JGoogleHelper::getUser((array)$response);
			$results = $app->login((array)$response, $options);			
			JGoogleHelper::my_log("on user login end" . print_r($results, true));			
		}
		$app->redirect(JRoute::_('index.php?Itemid=' . $app->getUserState( 'Itemid'), false));
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
