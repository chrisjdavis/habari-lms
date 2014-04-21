<?php
/**
 * @package Habari
 *
 */
namespace Habari;
class Course extends Post
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('course'),
			'fetch_fn' => 'get_row',
			'limit' => 1,
			'fetch_class' => 'Course',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}

	public function update($minor = true) {
		parent::update($minor);
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
	
	public function available($user) {
		$available = true;
				
		if( $this->locked == 1 ) {
			$available = false;
		}

		if ( $this->course_prereq != 0 && $this->completed($user) == false ) {
			$available = false;
		}
				
		return $available;
	}
	
	public function start($user) {
		$args = array( 'post_id' => $this->id, 'user_id' => $user->id, 'started' => DateTime::date_create( date(DATE_RFC822)) );
		DB::insert( DB::table('progress'), $args );
	}
	
	public function complete($user) {
		$args = array( 'post_id' => $this->id, 'user_id' => $user->id, 'completed' => DateTime::date_create( date(DATE_RFC822)) );
		DB::update( DB::table('progress'), $args, array('post_id' => $this->id, 'user_id' => $user->id) );
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
	
	public function students() {
		$count = DB::get_results("SELECT count(id) as count FROM {progress} WHERE post_id = ? AND completed = 0", array($this->id) );
				
		return $count[0]->count;
	}
	
	public function list_files() {
		$dir = Site::get_path('user') . '/files/uploads/courses/' . $this->id;
				
		$files = array();

		$iterator = new \DirectoryIterator( $dir );
		
		foreach( $iterator as $fileinfo ) {
			if( $fileinfo->isFile() ) {
				$files[] = $fileinfo->getFilename();
			}
		}
		
		return $files;
	}

	public function started($user) {
		$course_check = DB::get_column( "SELECT id, started, completed from {progress} WHERE user_id = ? AND post_id = ? AND completed = ''", array($user->id, $this->id) );
		
		if( !empty($course_check) ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function passed($user) {
		$this->check_for_completeness( $user );
		
		$check = DB::get_column( "SELECT id FROM {progress} WHERE post_id = ? AND user_id = ? AND completed != 0", array($this->id, $user->id) );

		if( empty($check) ) {
			return false;
		} else {
			return true;
		}
	}

	public function check_for_completeness($user) {
		$units = $this->units();
		$ids = array();		

		if( isset($units[0]) ) {
			foreach( $units as $unit ) {
				$ids[] = $unit->id;
			}
			
			$count = count( $units );
			$nums = implode(', ', $ids);
	
			$passed = DB::get_results( "SELECT count(id) as count FROM {progress} WHERE post_id IN(" . $nums . ") AND user_id = ? AND completed != 0", array($user->id) );
						
			if( $passed[0]->count == $count ) {
				$this->complete( $user );
			}
		}
	}

	public function completed($user) {
		$check = DB::get_column( "SELECT id FROM {progress} WHERE post_id = ? AND user_id = ? AND completed != 0", array($this->course_prereq, $user->id) );

		if( empty($check) ) {
			return false;
		} else {
			return true;
		}
	}
	
	public function units() {
		$units = Units::get( array('parent' => $this->id, 'nolimit' => true) );
		return $units;
	}
	
	public function forum() {
		$forum = Topic::get( array('id' => $this->course_group) );
		return URL::get( 'display_forum_thread', array('slug' => 'lms', 'thread' => $forum->slug) );
	}
	
	public function course_start_url($user) {
		return URL::get( 'start_course', array('course' => $this->slug) );
	}
}
?>
