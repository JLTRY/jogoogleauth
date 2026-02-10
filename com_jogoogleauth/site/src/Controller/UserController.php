<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_jogoogleauth
 *
 * @copyright   Copyright (C) 2016 - 2025 JL TRYOEN, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
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
        JOGoogleAuthHelper::addLogger();
        $uri = Uri::getInstance();
        $this->log("construct: JGoogleControllerUser:". $uri->toString());
        $app = Factory::getApplication();
        $return = $app->input->getString('return', '');
        $session = Factory::getSession();
        if ($return) {
            $this->log("construct: JGoogleControllerUser: session redirecturi". base64_decode($return));
            $session->set('redirecturi', base64_decode($return));
        }
        $oauth_client = new Client([], null, null, $app, $this);
        $oauth_client->setOption('sendheaders',true);
        $oauth_client->setOption('client_id','token');
        $oauth_client->setOption('scope',array('email','profile'));
        $oauth_client->setOption('requestparams',
                               array('state'=>'jauth',
                              'task'=> $app->input->get('task', 'login'),
                              'access_type'=>'offline'));
        $params = $app->getParams('com_jogoogleauth');
        $oauth_client->setOption('clientid', $params->get('clientid',''));
        $oauth_client->setOption('clientsecret', $params->get('clientsecret',''));
        $oauth_client->setOption('redirecturi', $params->get('redirecturi',''));
        $this->log("construct:oauth_client redirecturi:". $params->get('redirecturi',''));
        $oauth_client->setOption('authurl','https://accounts.google.com/o/oauth2/v2/auth');
        $oauth_client->setOption('tokenurl','https://www.googleapis.com/oauth2/v4/token');
        $this->oauth_client = $oauth_client; 
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
        $this->log("login");
        $session = Factory::getSession();
        if (!$session->get('form_processed')) {
            $app = Factory::getApplication();
            $app->setUserState( 'Itemid', $this->ItemId);
            try {
                $this->oauth_client->authenticate();
            }
            catch (\InvalidArgumentException $ex) {
                $app->enqueueMessage(sprintf("Error: %s<br> Please fill parameters for component", $ex->getMessage()), 'error');
            }
        }
        $this->log("login end");
    }

    public function auth()
    {
        $this->log("com_jogoogleauth.auth");
        $this->oauth_client->setOption('sendheaders',false);
        //sleep to avoid issue with OVH
        sleep(3);
        if (!$this->oauth_client->isAuthenticated()) {
            $this->log("com_jogoogleauth.auth not authenticated" );
            $this->oauth_client->authenticate();
        }
        if ($this->oauth_client->isAuthenticated()) {
            $this->log("com_jogoogleauth.auth isauthentificated"  );
            $this->credentials = $credentials = 
                json_decode($this->oauth_client->query('https://www.googleapis.com/oauth2/v1/userinfo?alt=json')->getBody(),true);
            $response = new \stdClass();
            // OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
            $app = Factory::getApplication();
            $params = $app->getParams('com_jogoogleauth');
            $options= array();
            $options['autoregister'] = true;
            $this->log("resgistration allowed ");
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

            $this->log("on user login " . print_r($options, true) . print_r($response, true));
            $user =  JOGoogleAuthHelper::getUser((array)$response);
            if ($user == null) {
                $this->log("registerUSer" . print_r($results, true));
                $user = JOGoogleAuthHelper::registerUser((array)$response);
                //$user = JOGoogleAuthHelper::sendregisteredUserMail((array)$response);
            }
            else {
                $this->log("user is not null 1");
            }
            $user =  JOGoogleAuthHelper::getUser((array)$response);
            $app     = Factory::getApplication();
            $session = Factory::getSession();
            $redirecturi = $session->get('redirecturi',"");
            //see Jokovlog implementation
            $session->fork();
            $session->set('user', $user);
            $session->set('user', $user);
            // Traitement du POST
            $session->set('form_processed', true);
            $this->log("on user login end");
            $return = $app->input->getString('return', '');
            if ($return != '')
            {
                
            }
            elseif ($redirecturi != "") {
                $this->log("on user login end redirect:" . $redirecturi);
                $app->redirect($redirecturi);
            }
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
        $session = Factory::getSession();
        $session->set('form_processed', false);
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
