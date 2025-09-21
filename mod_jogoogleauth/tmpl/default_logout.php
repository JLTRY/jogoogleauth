<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2025 JL TRYOEN, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

HtmlHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="login-form" class="form-vertical">
	<div class="logout-button">
		<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo Text::_('JLOGOUT'); ?>" />
		<input type="hidden" name="option" value="com_jogoogleauth" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo HtmlHelper::_('form.token'); ?>
	</div>
</form>
