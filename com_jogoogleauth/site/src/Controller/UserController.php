<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_jogoogleauth
 *
 * @copyright   Copyright (C) 2016 - 2025 JL TRYOEN, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JLTRY\Component\Jogoogleauth\Site\Controller;
use Joomla\OAuth2\Client;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use JLTRY\Component\Jogoogleauth\Site\Helper\JOGoogleAuthHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Registration controller class for Users.
 *
 * @since  1.6
 */
class UserController extends BaseController
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
		$this->log = true;
		$this->log("construct: JGoogleControllerUser:". print_r($config, true));
		$app = Factory::getApplication();
		$ItemId = $app->input->getInt('Itemid', null);
		$return = $app->input->getString('return', '');
		$session = Factory::getSession();
		if ($return) {
			$session->set('redirecturi', base64_decode($return));
		}
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
		$params = $app->getParams('com_jogoogleauth');
		$this->log("params:". print_r($params, true));
		$this->log("itemid2 set:". print_r($this->ItemIds, true));
		$this->ItemId = $params->get('login_redirect_menuitem', '');
		$this->log("itemid2:". print_r($this->ItemIds, true));
		if (empty($this->ItemId))
			$this->ItemId = $ItemId;
		$oauth_client->setOption('clientid',	$params->get('clientid',''));
		$oauth_client->setOption('clientsecret', $params->get('clientsecret',''));
		$oauth_client->setOption('redirecturi', $params->get('redirecturi',''));// . 'return=' . $return);
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
			JOGoogleAuthHelper::Log($str);
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
		JOGoogleAuthHelper::Log("authentificate");
		$app = Factory::getApplication();
		JOGoogleAuthHelper::Log("getapplication OK");
		$app->setUserState( 'Itemid', $this->ItemId);
		JOGoogleAuthHelper::Log("userstate" . $this->ItemId);
		JOGoogleAuthHelper::Log("oauth_client" . print_r(get_class($this->oauth_client), true));
		$this->oauth_client->authenticate();
		JOGoogleAuthHelper::Log("authentificate end");
	}
	
	
	public function auth()
	{
		JOGoogleAuthHelper::Log("google auth");
		$this->oauth_client->setOption('sendheaders',false);
		$this->oauth_client->authenticate();
		if ($this->oauth_client->isAuthenticated())
		{
			JOGoogleAuthHelper::Log("isauthentificated");
			$this->credentials = $credentials = json_decode($this->oauth_client->query('https://www.googleapis.com/oauth2/v1/userinfo?alt=json')->body,true);
			$response = new \stdClass();
			// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
			$app = Factory::getApplication();
			$params = $app->getParams('com_jogoogleauth');
			$options= array();
			$options['autoregister'] = true;
			JOGoogleAuthHelper::Log("resgistration allowed ");
			$response->username = str_replace('.', '-', preg_split("/@/", $credentials['email'])[0]);
			$response->name = $response->username;
			$response->email = $credentials['email'];
			$response->type = 'GMail';
			$pwd = $params->get('secretpassword','JLT');
			$response->password_clear = $response->email . $pwd;
			$response->password = $response->password_clear;
			$response->siteurl = Uri::base();
			$config = Factory::getConfig();
			$response->fromname = $config->get('fromname');
			$response->mailfrom = $config->get('mailfrom');
			$response->sitename = $config->get('sitename');

			JOGoogleAuthHelper::Log("on user login " . print_r($options, true) . print_r($response, true));
			$user =  JOGoogleAuthHelper::getUser((array)$response);
			if ($user == null) {
				JOGoogleAuthHelper::Log("registerUSer" . print_r($results, true));
				$user = JOGoogleAuthHelper::registerUser((array)$response);
				$user = JOGoogleAuthHelper::sendregisteredUserMail((array)$response);
			}
			else {
				JOGoogleAuthHelper::Log("user is not null 1" . print_r($user, true));
			}
			$user =  JOGoogleAuthHelper::getUser((array)$response);
			JOGoogleAuthHelper::Log("user is not null 2" . print_r($user, true));
			$results = $app->login((array)$response, $options);
			$user =  JOGoogleAuthHelper::getUser((array)$response);
			JOGoogleAuthHelper::Log("user is not null 3" . print_r($user, true));
			JOGoogleAuthHelper::Log("on user login end" . print_r($results, true));
		}
		$app =  Factory::getApplication();
		$session = Factory::getSession();
		$redirecturi = $session->get('redirecturi', "");
		if ($redirecturi) {
			JOGoogleAuthHelper::Log("on user login end" . $redirecturi);
			$app->redirect($redirecturi, false);
		}
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
		$app = Factory::getApplication();
		// Perform the log out.
		$error  = $app->logout();
		$input  = $app->input;
		$method = $input->getMethod();

		// Check if the log out succeeded.
		if ($error instanceof Exception)
		{
			$app->redirect(Route::_('index.php?option=com_jogoogleauth&view=login', false));
		}

		// Get the return url from the request and validate that it is internal.
		$return = $input->$method->get('return', '', 'BASE64');
		$return = base64_decode($return);

		// Check for a simple menu item id
		if (is_numeric($return))
		{
			if (LanguageMultilang::isEnabled())
			{

				$db = Factory::getDbo();
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
			if (!Uri::isInternal($return))
			{
				$return = '';
			}
		}

		// Redirect the user.
		$app->redirect(Route::_($return, false));
	}
}
