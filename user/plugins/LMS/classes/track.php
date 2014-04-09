<?php
/**
 * @package Habari
 *
 */
namespace Habari;
class Track extends Post
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('track'),
			'fetch_fn' => 'get_row',
			'limit' => 1,
			'fetch_class' => 'Track',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}

	public function jsonSerialize() {
		$array = array_merge( $this->fields, $this->newfields );
		$array['url'] = $this->permalink;	
		return json_encode($array);
	}
	
	public function courses() {
		$units = Courses::get( array('parent' => $this->id, 'orderby' => 'id ASC') );
		return $units;
	}
}
?>
