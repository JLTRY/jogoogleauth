<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_jgoogleauth
 *
 * @copyright   Copyright (C) 2005 - 2025 JL TRYOEN, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace JLTRY\Module\JOGoogleAuth\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Multilanguage;

class JOGoogleAuthHelper
{
    /**
     * Retrieve the url where the user should be returned after logging in
     *
     * @param   \Joomla\Registry\Registry  $params  module parameters
     * @param   string                     $type    return type
     *
     * @return string
     */
    public static function getReturnUrl($params, $type)
    {
        $choice = $params->get('loginredirectchoice', 0);
        $itemurl = $params->get('login_redirect_url', '');
        $itemid = $params->get('login_redirect_menuitem', '');
        $redirecturi = '';
        if (($choice == 1) && ($itemid != '')) {
            $app = Factory::getApplication();
            $sitemenu = $app->getMenu(); 
            $menuitem = $sitemenu->getItem($itemid);
            $redirecturi = Uri::root() . $menuitem->link;
        } 
        if (($choice == 0) && ($itemurl != '')) {
           $redirecturi = Uri::root() . $itemurl;
        }
        return base64_encode($redirecturi);
    }

    /**
     * Returns the current users type
     *
     * @return string
     */
    public static function getType()
    {
        $user = Factory::getApplication()->getIdentity();
        return (!$user->get('guest')) ? 'logout' : 'login';
    }

}
