<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jogoogleauth
 *
 * @copyright   Copyright (C) 2005 - 2015 JL Tryoen, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JLTRY\Component\Jogoogleauth\Administrator\View\Empty;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

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
	protected $form;
	protected $item;
	protected $script;
	protected $canDo;

	/**
	 * Display the Calendar view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->addToolBar();
        $language = Factory::getLanguage();
		$language->load('com_jogoogleauth.sys', JPATH_ADMINISTRATOR, null, true);
		// Display the template
		parent::display($tpl);
		
	}
	
	protected function addToolBar()
	{
			ToolBarHelper::divider();
			ToolBarHelper::preferences('com_jogoogleauth');
	}
}
