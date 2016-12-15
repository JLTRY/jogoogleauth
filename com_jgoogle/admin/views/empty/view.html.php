<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jcoaching
 *
 * @copyright   Copyright (C) 2005 - 2015 JL Tryoen, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$language = JFactory::getLanguage();
$language->load('joomla', JPATH_ADMINISTRATOR);
/**
 * JCoaching View
 *
 * @since  0.0.1
 */
class JGoogleViewempty extends JViewLegacy
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
		// Set the document
		$this->setDocument();
		// Display the template
		parent::display($tpl);	
		
	}
	
	protected function addToolBar()
	{
			
		//if ($this->canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_jgoogle');
		}
	}
	
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->addScript(JURI::root() . "/administrator/components/com_jgoogle"
		                                  . "/views/empty/params.js");
	}	

	
}
