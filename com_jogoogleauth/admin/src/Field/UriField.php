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

	public function getInput ()
	{
		$root = str_replace("http:", "https:", JURI::root());
		$this->value = $root . $this->default;
		$this->readonly = true;
		return  parent::getInput();
	}
	
}
