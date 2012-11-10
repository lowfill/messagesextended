<?php

	/**
	 * Elgg send a message action page
	 *
	 * @package ElggMessages
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Curverider Ltd <info@elgg.com>
	 * @copyright Curverider Ltd 2008-2009
	 * @link http://elgg.com/
	 */

	 // Make sure we're logged in (send us to the front page if not)
		if (!isloggedin()) forward();

	// Saving attacks
        action_gatekeeper();

	// Get input data
		$title = get_input('title'); // message title
		$message_contents = get_input('message'); // the message
		$send_to = explode(',', get_input('send_to')); // this is the user guid to whom the message is going to be sent
		$reply = get_input('reply',0); // this is the guid of the message replying to

	// Cache to the session to make form sticky
		$_SESSION['msg_to'] = $send_to;
		$_SESSION['msg_title'] = $title;
		$_SESSION['msg_contents'] = $message_contents;

		if (empty($send_to[0])) {
			register_error(elgg_echo("messages:user:blank"));
			forward("mod/messages/send.php");
		}
	// Make sure the message field, send to field and title are not blank
		if (empty($message_contents) || empty($title)) {
			register_error(elgg_echo("messages:blank"));
			forward("mod/messages/send.php");
		}

		foreach($send_to as $guid) {
		  if (!empty($guid)) {
    		$recipient = get_entity($guid);
    		if(!empty($recipient) && ($recipient instanceof ElggUser)){
    		  $result = messages_send($title,$message_contents,$guid,0,$reply);

    		}
    		else if (!empty($recipient) && ($recipient instanceof ElggGroup)){
    		  $members = $recipient->getMembers(9999);
    		  $title2 = "[".$recipient->name."] ".$title;
              if(!empty($members)){
                foreach($members as $member){
                  if($member->guid!=get_loggedin_userid()){
                    $result = messages_send($title2,$message_contents,$member->guid,0,$reply);
                  }
                }
              }
    		}
    		else{
    			register_error(elgg_echo("messages:user:nonexist"));
    			forward("mod/messages/send.php");
    		}
		  }
		}

	// Otherwise, 'send' the message

	// Save 'send' the message
		if (!$result) {
			register_error(elgg_echo("messages:error"));
			forward("mod/messages/send.php");
		}

	// successful so uncache form values
		unset($_SESSION['msg_to']);
		unset($_SESSION['msg_title']);
		unset($_SESSION['msg_contents']);

	// Success message
		system_message(elgg_echo("messages:posted"));

	// Forward to the users inbox
		forward('pg/messages/' . $_SESSION['user']->username);

?>
