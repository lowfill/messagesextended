<?php
/**
  * Edit Settings view for MessagesExtended Plugin
  *
  * @package ElggMessagesExtended
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
  * @author Daniel Aristizabal Romero <daniel@somosmas.org>
  * @copyright Corporación Somos más - 2012
  * @link http://www.somosmas.org
  */

$multisuggest = is_plugin_enabled("libform_utils");

?>
<p>
	<?php echo elgg_echo('messagesextended:settings:tinymce');?>

	<select name="params[tinymce]">
		<option value="true" <?php if ($vars['entity']->tinymce == "true") {echo " selected=\"yes\" "; }?>><?php echo elgg_echo('messagesextended:settings:true');?></option>
		<option value="false" <?php if ($vars['entity']->tinymce == "false") {echo " selected=\"yes\" "; }?>><?php echo elgg_echo('messagesextended:settings:false');?></option>
	</select>
</p>
<p>
	<?php if ($multisuggest) {?>

	<?php echo elgg_echo('messagesextended:settings:multiple'); ?>

	<select name="params[multiple]">
		<option value="true" <?php if ($vars['entity']->multiple == "true") {echo " selected=\"yes\" "; }?>><?php echo elgg_echo('messagesextended:settings:true'); ?></option>
		<option value="false" <?php if ($vars['entity']->multiple == "false") {echo " selected=\"yes\" "; }?>><?php echo elgg_echo('messagesextended:settings:false'); ?></option>
	</select>

	<?php } else {
	  echo elgg_echo('messagesextended:settings:nomultisuggest');
	}?>

</p>