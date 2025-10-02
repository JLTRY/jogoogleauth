<?php

/**
 * @package	 Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2025 JL TRYOEN, Inc. <https://www.joomla.org>
 * @license	 GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace JLTRY\Plugin\User\JOGoogleAuth\Extension;
use Joomla\CMS\Event\CoreEventAware;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;



// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * WebAuthn Passwordless Login plugin
 *
 * The plugin features are broken down into Traits for the sole purpose of making an otherwise
 * supermassive class somewhat manageable. You can find the Traits inside the Webauthn/PluginTraits
 * folder.
 *
 * @since  4.0.0
 */
class JOGoogleAuth extends CMSPlugin implements SubscriberInterface
{
  /**
     * Have I already injected CSS and JavaScript? Prevents double inclusion of the same files.
     *
     * @var	 boolean
     * @since   4.0.0
     */
    private $injectedCSSandJS = false;
    
    private function returnFromEvent(Event $event, $value = null): void
    {
        $result = $event->getArgument('result') ?: [];

        if (!is_array($result)) {
            $result = [$result];
        }

        $result[] = $value;

        $event->setArgument('result', $result);
    }
    
    /**
     * Injects the WebAuthn CSS and Javascript for frontend logins, but only once per page load.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function addLoginCSSAndJavascript(): void
    {
        if ($this->injectedCSSandJS) {
            return;
        }

        // Set the "don't load again" flag
        $this->injectedCSSandJS = true;
        $params = $this->params;
        $choice = $params->get('loginredirectchoice', 0);
        $itemurl = $params->get('login_redirect_url', '');
        $itemid = $params->get('login_redirect_menuitem', '');
        $redirecturi = URI::base();
        if (($choice == 1) && ($itemid != '')) {
            $app = Factory::getApplication();
            $sitemenu = $app->getMenu(); 
            $menuitem = $sitemenu->getItem($itemid);
            $redirecturi = Uri::root() . $menuitem->link;
        } 
        if (($choice == 0) && ($itemid != '')) {
           $redirecturi = Uri::root() . $itemurl;
        }
        /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addRegistryFile('media/plg_jogoogleauth/joomla.asset.json');
        $wa->usePreset("plugin.jogoogleauth");
        $wa->addInlineScript('var base = \''. Uri::base() .'\' ; var redirecturi = \''. $redirecturi .'\'',
                            ['position' => 'before'], [], ['plugin.jogoogleauth']);
        
    }
    
    // Add JGoogle button
    /**
     * Creates additional login buttons
     *
     * @param   Event  $event  The event we are handling
     *
     * @return  void
     *
     * @see	 AuthenticationHelper::getLoginButtons()
     *
     * @since   4.0.0
     */
    public function onUserLoginButtons(Event $event): void
    {
        /** @var string $form The HTML ID of the form we are enclosed in */
        
        $form = $event->getArguments();


        // Load necessary CSS and Javascript files
        $this->addLoginCSSAndJavascript();

        // Unique ID for this button (allows display of multiple modules on the page)
        $randomId = 'plg_user_jgoggle-' .
            UserHelper::genRandomPassword(12) . '-' . UserHelper::genRandomPassword(8);

        $this->returnFromEvent($event, [
            [
                'label'			  => 'Google',
                'tooltip'			=> 'Google login',
                'id'				 => $randomId,
                'data-webauthn-form' => $form,
                'class'			  => 'plg_google_login_button btn-info w100',
            ],
            ]);
    }
    
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.2.0
     */
    public static function getSubscribedEvents(): array
    {
        try {
            $app = Factory::getApplication();
        } catch (\Exception $e) {
            return [];
        }

        if (!$app->isClient('site') && !$app->isClient('administrator')) {
            return [];
        }

        return [
            'onUserLoginButtons' => 'onUserLoginButtons'
        ];
    }

}	