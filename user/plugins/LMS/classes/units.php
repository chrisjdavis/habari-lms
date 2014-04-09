<?php
/**
 * Forums Class
 *
 */
namespace Habari;
class Units extends Posts
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('unit'),
			'fetch_class' => 'Unit',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}
}
?>