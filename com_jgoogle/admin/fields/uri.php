<?php
/**
 * @package    JCoaching
 * @author     
 * @copyright  
 * @license    
 */
defined('_JEXEC') or die();
JLoader::import('libraries.joomla.form.fields.url', JPATH_SITE);

class JFormFieldURI extends JFormFieldUrl
{
	protected $type = 'uri';
	
	/**
		* Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   11.1
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);
		
	}
	
	public function getInput ()
	{
		$root = str_replace("http", "https", JURI::root());
		$this->value = $root . $this->default;
		$this->readonly = true;
		return  parent::getInput();
	}
	
}
