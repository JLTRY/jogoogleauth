<?php

/**
 * @package	 Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */


use Joomla\CMS\Event\CoreEventAware;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Event\Event;
use Joomla\CMS\Router\Route;



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
class plgUserJGoogle extends CMSPlugin implements SubscriberInterface
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
		$document = Factory::getDocument();
		$document->addScriptDeclaration('var base = \''.URI::base().'\'');
		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = $document->getWebAssetManager();
		$wa->getRegistry()->addRegistryFile('plugins/user/jgoogle/joomla.asset.json');
		$wa->usePreset("plugin.jgoogle");
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


		// If you can't find the image then skip it
		$image =  JPATH_ROOT . '/images/joomla/google.png';

		// Extract image if it exists
		//$image = file_exists($image) ? file_get_contents($image) : '';

		$this->returnFromEvent($event, [
			[
				'label'			  => 'Google',
				'tooltip'			=> 'Google login',
				'id'				 => $randomId,
				'data-webauthn-form' => $form,
				//'image'				=> $image,
				'class'			  => 'plg_google_login_button btn-info w100',
				//'onclick'			=> '
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