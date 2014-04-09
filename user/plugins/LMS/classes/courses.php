<?php
/**
 * Forums Class
 *
 */
namespace Habari;
class Courses extends Posts
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('course'),
			'fetch_class' => 'Course',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}
}
?>