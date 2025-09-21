<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_jogoogleauth
 *
 * @copyright   Copyright (C) 2016 - 2025 JL TRYOEN, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JLTRY\Component\Jogoogleauth\Site\View\JOGoogleAuth;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$language = Factory::getLanguage();
$language->load('joomla', JPATH_ADMINISTRATOR);


/**
 * JCoaching View
 *
 * @since  0.0.1
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display the Hello World view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		// Assign data to the view
		$this->item = $this->get('Item');

		// Check for errors.
		if (is_array($errors) && count($errors = $this->get('Errors')))
		{
			Log::add(implode('<br />', $errors), Log::WARNING, 'jerror');
			return false;
		}

		// Display the view
		parent::display($tpl);
	}
}
