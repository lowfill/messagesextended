<?php
/**
 * JavaScript link prettifier
 *
 * Convert to links all the URLs found on the message body
 *
 * @package ElggMessagesExtended
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Diego Andrés Ramírez Aragón <diego@somosmas.org>
 * @copyright Corporación Somos más - 2008
 * @link http://www.somosmas.org
*/
//@todo Enable this only when the message view is called
?>
jQuery(document).ready(function(){
	jQuery(".messagebody").children(":not(:has(a))").each(function(){
		var re = /(http[s]?:\/?\/?[\w+/\?.~]*[=\&amp;\w]*[#\w]+)/gm;
		var replace = jQuery(this).html().replace(re,"<a href=\"$&\">$&</a>");
		jQuery(this).html(replace);
		});
});
