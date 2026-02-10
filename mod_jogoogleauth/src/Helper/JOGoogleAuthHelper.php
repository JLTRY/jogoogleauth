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
        $app  = Factory::getApplication();
        $menu = $app->getMenu();
        if ($menu && $params) {
            $item = $app->getMenu()->getItem($params->get($type));
        }
        // Stay on the same page
        $url = Uri::getInstance()->toString();

        if ($item)
        {
            $lang = '';
            if (LanguageMultilang::isEnabled() && $item->language !== '*')
            {
                $lang = '&lang=' . $item->language;
            }
            $url = 'index.php?Itemid=' . $item->id . $lang;
        }
        return base64_encode($url);
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
