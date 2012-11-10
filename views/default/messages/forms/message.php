<?php

    /**
	 * Elgg send a message page. Overwrites mod/messages/views/default/messages/forms/message.php
	 *
	 * This view add the user groups to the list of available recipients
	 *
	 * @package ElggMessagesExtended
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Curverider Ltd <info@elgg.com>
	 * @author Diego Andrés Ramírez Aragón <diego@somosmas.org>
	 * @copyright Curverider Ltd 2008-2009
	 * @copyright Corporación Somos más 2008-2009
	 * @link http://elgg.com/
	 * @link http://www.somosmas.org/
	 *
	 * @uses $vars['friends'] This is an array of a user's friends and is used to populate the list of
	 * people the user can message
	 *
	 */

	//grab the user id to send a message to. This will only happen if a user clicks on the 'send a message'
	//link on a user's profile or hover-over menu
	$send_to = (int) get_input('send_to');
	if ($send_to === "")
		$send_to = $_SESSION['msg_to'];

	$send_to = explode(',', $send_to);

	$msg_title = $_SESSION['msg_title'];
	$msg_content = $_SESSION['msg_contents'];

	// clear sticky form cache in case user browses away from page and comes back
	unset($_SESSION['msg_to']);
	unset($_SESSION['msg_title']);
	unset($_SESSION['msg_contents']);


$to_label = elgg_echo("messages:to");
if(!empty($send_to[0])){
  // Check for multiple recipients
  foreach ($send_to as $guid) {
    if (!empty($guid)) {
      $user = get_entity($guid);
      $to_field.= "<div class=\"messages_single_icon\">";
      $to_field.= elgg_view("profile/icon",array('entity' => $user, 'size' => 'tiny'));
      $to_field.= "</div>".$user->name."<br class=\"clearfloat\" />";
    }
  }
  $to_field.= elgg_view("input/hidden",array("internalname"=>"send_to","value"=>implode(',',$send_to)));
} else {
  // Check for MultiSuggest Plugin
  if (is_plugin_enabled("libform_utils") && get_plugin_setting('multiple', 'messagesextended') == "true") {
    $to_field = elgg_view('input/autosuggest', array("internalname" => "send_to",'suggest'=>'messages_users','style'=>'-facebook'));
  } else {
    $options = array();
    $options[0] = " ";
    if(is_array($vars["friends"])){
      foreach($vars['friends'] as $friend){
        $options[$friend->guid]=$friend->name;
      }
    }
    $groups = get_entities_from_relationship('member', page_owner(), false, "group", "", 0, "", 9999, 0, false, 0);
    if(is_array($groups)){
      foreach($groups as $group){
        $options[$group->guid]=$group->name . " (".elgg_echo("messagesextended:group").")";
      }
    }

    if(count($options)>1){
      $to_field = elgg_view("input/pulldown",array("internalname"=>"send_to","options_values"=>$options));
    }
    else{
      $to_field = elgg_echo("messagesextended:nocontacts");
    }
  }

}
$title_label = elgg_echo("messages:title");
$title_field = elgg_view("input/text", array("internalname" => "title","value" => $msg_title));

$body_label = elgg_echo("messages:message");

// Check if support for TinyMCE is true or false
if (get_plugin_setting('tinymce', "messagesextended") == "true") {
  $body_field = elgg_view("input/longtext", array("internalname" => "message","value" => $msg_content));
} else {
  $body_field = elgg_view("input/plaintext", array("internalname" => "message","value" => $msg_content));
}


$submit_input = elgg_view('input/submit', array('internalname' => 'submit', 'value' => elgg_echo('messages:fly')));

$form_body=<<<EOT
    <p><label>$to_label</label> $to_field</p>

	<p><label>$title_label: <br />$title_field</label></p>

	<p class="longtext_editarea"><label>$body_label: <br />$body_field</label></p>

	<p>$submit_input</p>
EOT;
$form = elgg_view('input/form', array('action' => "{$vars['url']}action/messagesextended/send", 'body' => $form_body, 'internalid' => 'messageForm'));

?>
	<div class="contentWrapper">
	<?php echo $form;?>
	</div>