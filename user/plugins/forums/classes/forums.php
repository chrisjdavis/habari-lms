<?php
/**
 * Forums Class
 *
 */
namespace Habari;
class Forums extends Posts
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('forum'),
			'fetch_class' => 'Forum',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}
}
?>