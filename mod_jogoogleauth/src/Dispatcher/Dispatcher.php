<?php
/*------------------------------------------------------------------------
# mod_jogoogleauth - JO's Google Auth module
# ------------------------------------------------------------------------
# author    JL TRYOEN
# Copyright (C) 2025 www.jltryoen.fr All Rights Reserved.
# @license  http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
# Websites: http://www.jltryoen.fr
-------------------------------------------------------------------------*/

namespace JLTRY\Module\JOGoogleAuth\Site\Dispatcher;

// No direct access
defined('_JEXEC') or die;


use JLTRY\Module\JOGoogleAuth\Site\Helper\JOGoogleAuthHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;


// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_articles
 *
 * @since  5.2.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    protected function getLayoutData(): array
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];
        $type             = JOGoogleAuthHelper::getType();
        $return           = JOGoogleAuthHelper::getReturnUrl($params, $type);
        $layout           = $params->get('layout', 'default');
        $user             = Factory::getApplication()->getIdentity();
        // Logged users must load the logout sublayout
        if ($user->id != 0)
        {
            $params['layout'] .= $layout . '_logout';
        }
        $this->module->cacheTime = 0;
        return array(
                            'module'   => $this->module,
                            'app'      => $this->app,
                            'input'    => $this->input,
                            'params'   => new Registry($params),
                            'user'     => $user ,
                            'return'   => $return,
                            'type'     => $type);
    }
}
?>