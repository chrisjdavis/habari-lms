<?php
/**
 * @package Habari
 *
 */
namespace Habari;
class Topic extends Post
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('topic'),
			'fetch_fn' => 'get_row',
			'limit' => 1,
			'fetch_class' => 'Topic',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}

	public function jsonSerialize() {
		$array = array_merge( $this->fields, $this->newfields );
		$array['url'] = $this->permalink;	
		return json_encode($array);
	}
	
	public function seen($user_id) {
		$check = DB::get_column( "SELECT id FROM {alerts} WHERE post_id = ? AND user_id = ?", array($this->id, $user_id) );
		
		if( empty($check) ) {
			$args = array( 'post_id' => $this->id, 'user_id' => $user_id );
			DB::insert( DB::table('alerts'), $args );
		}
	}
	
	public function have_seen($user_id) {
		$check = DB::get_column( "SELECT id FROM {alerts} WHERE post_id = ? AND user_id = ?", array($this->id, $user_id) );
		
		if( empty($check) ) {
			$dot = '<i class="fa fa-circle green-circle"></i>';
		} else {
			$dot = '<i class="fa fa-circle grey-circle"></i>';
		}
		
		return $dot;
	}
	
	public function list_files() {
		$dir = Site::get_path('user') . '/files/uploads/forums/' . $this->id;
		$files = scandir( $dir );
		
		return $files;		
	}
}
?>
