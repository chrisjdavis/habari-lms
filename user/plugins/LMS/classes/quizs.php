<?php
/**
 * Forums Class
 *
 */
namespace Habari;
class Quizs extends Posts
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('quiz'),
			'fetch_class' => 'Quiz',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}
}
?>