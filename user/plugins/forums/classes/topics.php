<?php
/**
 * Forums Class
 *
 */
namespace Habari;
class Topics extends Posts
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('topic'),
			'fetch_class' => 'Topic',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}
}
?>