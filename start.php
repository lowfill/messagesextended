<?php
/**
 * Messages extended
 *
 * @package ElggMessagesExtended
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Diego Andrés Ramírez Aragón <diego@somosmas.org>
 * @copyright Corporación Somos más - 2008
 * @link http://www.somosmas.org
 *
 */


/**
 * Messages extended init function
 *
 * Register the notification handler and the configuration settings
 */
function messagesextended_init(){

	elgg_extend_view("metatags","messages/js");

	if(!is_plugin_enabled("messages")){
		register_error(elgg_echo("messagesextended:nomessages"));
		return;
	}
	//@todo Make this configurable
	$plugin_version = get_plugin_setting("version","messagesextended");
	if(!$plugin_version || $plugin_version!="1.1"){
		messagesextended_configure_users($plugin_version);
	}
}

/**
 * Configures messages notification for all registered users
 *
 */
function messagesextended_configure_users($version){
	$users = get_entities("user","",0,"",null);
	if(!empty($users)){
		foreach($users as $user){
			if($version!="1.1"){
				$user->clearMetadata("notification:method:messages");
			}
			$result = set_user_notification_setting($user->guid, "site",true);
			if (!$result){
				register_error(elgg_echo('notifications:usersettings:save:fail'));
			}
		}
		set_plugin_setting("version","1.1","messagesextended");
	}
}

function messages_users_suggest_hook($query){
	global $CONFIG;
	$where = "(e.guid IN (SELECT guid FROM {$CONFIG->dbprefix}users_entity WHERE username like '%$query%' OR name like '%$query%') ";
	$where.= "OR e.guid IN (SELECT guid FROM {$CONFIG->dbprefix}groups_entity WHERE name like '%$query%'))";
	
	$options = array(
            'types'=>array('user','group'),
            'subtype'=>ELGG_ENTITIES_NO_VALUE,
        	'wheres'=>array($where),
			'relationship'=>''
	);
//TODO Refinar más para sólo tener los amigos y grupos de la persona que esta escribiendo el mensaje	
	$entities = elgg_get_entities($options);
	$data = array();
	if(!empty($entities)){
		foreach($entities as $entity){
			$data[]=array('id'=>$entity->guid,'name'=>$entity->name);
		}
	}
	return $data;
}
register_action("messagesextended/send",false,dirname(__FILE__). "/actions/send.php");

register_elgg_event_handler('init','system','messagesextended_init');
?>