<?php
/**
 * Ssend a message action
 *
 * @package ElggMessages
 */


$subject = strip_tags(get_input('subject'));
$body = get_input('body');
$recipient_guid = get_input('recipient_guid');
$group_guid = get_input('group_guid');
$collection_guid = get_input('collection_guid'); // this is the collection to send the message to

// If adminsend switch $from guid (deault 0)
$from = 0;
$siteadmin_guid = (int) get_input('siteadmin_guid');
if ($siteadmin_guid && elgg_is_admin_logged_in()) {
	$from = $siteadmin_guid;
}

if (!$recipient_guid && !$collection_guid && !$group_guid) {
	register_error(elgg_echo("messages:user:blank"));
	forward("messages/compose");
}

// Make sure the message field, send to field and title are not blank
if (!$body || !$subject) {
	register_error(elgg_echo("messages:blank"));
	forward("messages/compose");
}

elgg_make_sticky_form('messages');

if(get_plugin_setting('multiple', 'messagesextended') == "true"){
	$recipients = explode(',',$recipient_guid);
	if(!empty($recipients)){
		foreach($recipients as $recipient_guid){
			$recipient = get_entity($recipient_guid);
			if(!empty($recipient)){ // it is an entity (user || group)
				if(elgg_instanceof($recipient,'user')){
					$result = messages_send($subject, $body, $recipient->guid, $from, $reply, true, true);
				}
				elseif(elgg_instanceof($recipient,'group')){
					$members_count = $recipient->getMembers(10,0,true);
					$subject = "[{$recipient->name}] {$subject}";
					for($i=0;$i<$members_count;$i+=10){
						$members = $recipient->getMembers(10,$i);
						if(!empty($members)){
							foreach($members as $member){
								if(!$member->isBanned() && $member->guid != elgg_get_logged_in_user_guid()){
									$result += messages_send($subject, $body, $member->guid, $from, $reply, true, true);
								}
							}
						}
					}
				}
			}
			else{ //Try to get the collection
				if(strpos($recipient_guid,'c', $needle) ===0){
					$recipient_guid = substr($recipient_guid, 1);
					$collection = get_members_of_access_collection($recipient_guid, false);
					if(!empty($collection)){
						foreach ($collection as $member) {
							if (!$member->isBanned()) {
								$result += messages_send($subject, $body, $member->guid, $from, $reply, true, true);
							}
						}
					}						
				}
			}
		}
	}
}
else{
	// Send to collection of friends
	if (!empty($collection_guid)) {
		$collection = get_members_of_access_collection($collection_guid, false);
		foreach ($collection as $member) {
			if (!$member->isBanned()) {
				$result += messages_send($subject, $body, $member->guid, $from, $reply, true, true);
			}
		}
	}

	if(!empty($group_guid)){
		$group = get_entity($group_guid);
		$count_members = $group->getMembers(10, 0,true);
		for($i=0;$i<$count_members;$i+=10){
			$members = $group->getMembers(10,$i);
			if(!empty($members)){
				foreach($members as $member){
					if(!$member->isBanned() && $member->guid != elgg_get_logged_in_user_guid()){
						$result += messages_send($subject, $body, $member->guid, $from, $reply, true, true);
					}
				}
			}
		}
	}

	if (!empty($recipient_guid)) {
		$user = get_user($recipient_guid);
		if (!$user) {
			register_error(elgg_echo("messages:user:nonexist"));
			forward("messages/compose");
		}

		$result = messages_send($subject, $body, $recipient_guid, $from, $reply, true, true);

	}
}

// Save 'send' the message
if (!$result) {
	register_error(elgg_echo("messages:error"));
	forward("messages/compose");
}

elgg_clear_sticky_form('messages');

system_message(elgg_echo("messages:posted"));

forward('messages/inbox/' . elgg_get_logged_in_user_entity()->username);

