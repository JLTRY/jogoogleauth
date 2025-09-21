<?php

/**
* @copyright Copyright (C) 2025 Jean-Luc TRYOEN. All rights reserved.
* @license GNU/GPL
*
* Version 1.0.0
*
* @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* @link        https://www.jltryoen.fr
*/

use JLTRY\Plugin\User\JOGoogleAuth\Extension\JOGoogleAuth;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;


// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.3.0
     */
     public function register(Container $container)
        {
            $container->set(
                PluginInterface::class,
                function (Container $container) {
    
                    $config = (array)PluginHelper::getPlugin('user', 'jogoogleauth');
                    $subject = $container->get(DispatcherInterface::class);
                    $app = Factory::getApplication();
                    $plugin = new JOGoogleAuth($subject, $config);
                    $plugin->setApplication($app);
                    // Show an error message if the plugin is not available
                    $lang = Factory::getApplication()->getLanguage();
                    $lang->load('plg_content_jogoogleauth', JPATH_PLUGINS . '/user/jogoogleauth');
                    return $plugin;
                }
            );
    }
};
