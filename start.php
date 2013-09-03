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
 *
 *
 * Messages extended init function
 *
 * Register the notification handler and the configuration settings
 */
function messagesextended_init(){

	elgg_extend_view("js/elgg","messagesextended/js");

	if(!is_plugin_enabled("messages")){
		register_error(elgg_echo("messagesextended:nomessages"));
		return;
	}

	elgg_register_action("messages/send", dirname(__FILE__)."/actions/send.php");

	//@todo Make this configurable
	$plugin_version = get_plugin_setting("version","messagesextended");

	if(!$plugin_version || $plugin_version!="2.0"){
		messagesextended_configure_users($plugin_version);
	}

}

/**
 * Configures messages notification for all registered users
 *
 */
function messagesextended_configure_users($version){
	$options = array('types'=>'user');
	$users_count = elgg_get_entities(array_merge($options,array('count'=>true)));
	$options['limit']=50;
	if(!empty($users_count)){
		for($i=0;$i<$users_count;$i+=50){
			$options['offset']=$i;
			$users = elgg_get_entities($options);
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

			}
		}
		set_plugin_setting("version","2.0","messagesextended");
	}
}

function messages_users_suggest_hook($query){
	global $CONFIG;

	//Looking for users and groups
	$where = "(";
	$where.= "e.guid IN (SELECT distinct guid FROM {$CONFIG->dbprefix}users_entity ue JOIN {$CONFIG->dbprefix}entity_relationships r ON r.guid_two=ue.guid WHERE (ue.username like '%$query%' OR ue.name like '%$query%') AND r.relationship='friend' ) ";
	$where.= "OR e.guid IN (SELECT distinct guid FROM {$CONFIG->dbprefix}groups_entity ge JOIN {$CONFIG->dbprefix}entity_relationships r ON r.guid_two=ge.guid WHERE ge.name like '%$query%' AND r.relationship='member' ) ";
	$where.= ")";

	$options = array(
			'types'=>array('user','group'),
			'subtype'=>ELGG_ENTITIES_NO_VALUE,
			'wheres'=>array($where),
	);
	$entities = elgg_get_entities($options);
	$data = array();
	if(!empty($entities)){
		foreach($entities as $entity){
			$name = $entity->name;
			if(elgg_instanceof($entity,'group')){
				$name = "{$name} (group)";
			}
			$data[]=array('id'=>$entity->guid,'name'=>$name);
		}
	}
	// Looking for access_collections
	$query_str = "SELECT * FROM {$CONFIG->dbprefix}access_collections ";
	$query_str.= "WHERE owner_guid = ".elgg_get_logged_in_user_guid()." ";
	$query_str.= "AND name like '%$query%' ";
	$query_str.= "AND site_guid = " .elgg_get_site_entity()->guid;

	$entities = get_data($query_str);
	if(!empty($entities)){
		foreach($entities as $entity){
			$name = "{$entity->name} (".elgg_echo('friends:collections').")";
			$data[]=array('id'=>'c'.$entity->id,'name'=>$name);
		}
	}

	return $data;
}
register_action("messagesextended/send",false,dirname(__FILE__). "/actions/send.php");

register_elgg_event_handler('init','system','messagesextended_init');
?>