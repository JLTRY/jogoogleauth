<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jogoogleauth
 *
 * @copyright   Copyright (C) 2005 - 2015 JL Tryoen, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace JLTRY\Component\Jogoogleauth\Administrator\Controller;
use Joomla\CMS\MVC\Controller\BaseController;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * General Controller of JCoaching component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_jogoogleauth
 * @since  4.0.0
 */
class DisplayController extends BaseController
{

    /**
     * The default view.
     *
     * @var    string
     * @since  1.0.0
     */
    protected $default_view = 'empty';
    public function display($cachable = false, $urlparams = false)
    {
        $input = $this->app->getInput();
        // Set the default view (if not specified)
        $input->set('view', $input->getCmd('view', 'Empty'));

        // Call parent to display
        parent::display($cachable);
    }
}
