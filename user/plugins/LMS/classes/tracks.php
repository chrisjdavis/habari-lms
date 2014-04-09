<?php
/**
 * Forums Class
 *
 */
namespace Habari;
class Tracks extends Posts
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('track'),
			'fetch_class' => 'Track',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}
}
?>