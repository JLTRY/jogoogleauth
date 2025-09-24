<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jogoogleauth
 *
 * @copyright   Copyright (C) 2005 - 2015 JL Tryoen, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace JLTRY\Component\Jogoogleauth\Administrator\Field;
defined('_JEXEC') or die();
use Joomla\CMS\Form\Field\UrlField;
use Joomla\CMS\Uri\Uri;

class UriField extends UrlField
{
    protected $type = 'uri';
    public function getInput ()
    {
        $root = str_replace("http:", "https:", Uri::root());
        $this->value = $root . $this->default;
        $this->readonly = true;
        return  parent::getInput();
    }
}
