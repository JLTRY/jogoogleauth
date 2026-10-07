<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2025 JL TRYOEN, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_jogoogleauth', 'media/mod_jogoogleauth/css/mod_jogoogleauth.css');

$moduleclass = $params->get('moduleclass', '');
?>
<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="login-form" class="form-inline">
    <div class="userdata <?php echo htmlspecialchars($moduleclass, ENT_QUOTES, 'UTF-8'); ?>">
        <div id="form-login-submit" class="control-group">
            <div class="controls">
                <button type="submit" tabindex="0" name="Submit" class="btn btn-info">
                    <i class="fa fa-sign-out" aria-hidden="true"></i> 
                    <span id="logout"><?php echo Text::_('JLOGOUT') ?></span>
                </button>
            </div>
        </div>
        <input type="hidden" name="option" value="com_jogoogleauth" />
        <input type="hidden" name="task" value="user.logout" />
        <input type="hidden" name="return" value="<?php echo $return; ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
