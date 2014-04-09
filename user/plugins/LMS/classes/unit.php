<?php
/**
 * @package Habari
 *
 */
namespace Habari;
class Unit extends Post
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('unit'),
			'fetch_fn' => 'get_row',
			'limit' => 1,
			'fetch_class' => 'Unit',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}

	public function jsonSerialize() {
		$array = array_merge( $this->fields, $this->newfields );
		$array['url'] = $this->permalink;	
		return json_encode($array);
	}

	public function start($user) {
		$args = array( 'post_id' => $this->id, 'user_id' => $user->id, 'started' => DateTime::date_create( date(DATE_RFC822)) );
		DB::insert( DB::table('progress'), $args );
	}

	public function complete($user) {
		$ids = array();
		$args = array( 'post_id' => $this->id, 'user_id' => $user->id, 'completed' => DateTime::date_create( date(DATE_RFC822)) );
		
		DB::update( DB::table('progress'), $args, array('post_id' => $this->id) );
	}

	public function completed($user) {
		$check = DB::get_column( "SELECT id FROM {progress} WHERE post_id = ? AND user_id = ? AND completed != ''", array($this->id, $user->id) );
		
		if( empty($check) ) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	public function quiz_score($user) {
		$quiz = Quiz::get( array('unit_id' => $this->id) );
		$total = count( $quiz->questions() );
				
		$passed = DB::get_results( "SELECT count(id) as count FROM {answers} WHERE quiz_id = ? AND user_id = ? AND passed = ?", array($quiz->id, $user->id, 1) );
				
		$count1 = $passed[0]->count / $total;
		$count2 = $count1 * 100;
		
		return $percentage = number_format($count2, 0);
	}
	
	public function wrong_answers($user) {
		$quiz = Quiz::get( array('unit_id' => $this->id) );
		$failed = DB::get_results( "SELECT id, question_id FROM {answers} WHERE quiz_id = ? AND user_id = ? AND passed = ?", array($quiz->id, $user->id, 0) );
		$questions = '';
		
		$ids = array();
		
		foreach( $failed as $q_id ) {
			$ids[] = $q_id->question_id;
		}
		
		$id = implode( ',', $ids );
		
		if( $id ) {
			$questions = DB::get_results( "SELECT id, question, choices, correct_choice FROM {questions} WHERE id IN($id)");
		} else {
			$questions = array();
		}
		
		return $questions;		
	}
	
	public function have_completed($user_id) {
		$check = DB::get_column( "SELECT id FROM {alerts} WHERE post_id = ? AND user_id = ?", array($this->id, $user_id) );
		
		if( empty($check) ) {
			$dot = '<i class="fa fa-circle green-circle"></i>';
		} else {
			$dot = '<i class="fa fa-circle grey-circle"></i>';
		}
		
		return $dot;
	}
	
	public function course() {
		return Course::get( array('id' => $this->course) );
	}
	
	public function list_files() {
		$course = Course::get( array('id' => $this->parent) );
		$dir = Site::get_path('user') . '/files/uploads/courses/' . $course->id . '/' . $this->id;

		$files = array();
		$iterator = new \DirectoryIterator( $dir );
		
		foreach( $iterator as $fileinfo ) {
			if( $fileinfo->isFile() ) {
				$files[] = $fileinfo->getFilename();
			}
		}
				
		return $files;
	}
}
?>
