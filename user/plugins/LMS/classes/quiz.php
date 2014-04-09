<?php
/**
 * @package Habari
 *
 */
namespace Habari;
class Quiz extends Post
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => Post::type('quiz'),
			'fetch_fn' => 'get_row',
			'limit' => 1,
			'fetch_class' => 'Quiz',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		
		return Posts::get( $paramarray );
	}

	public function jsonSerialize() {
		$array = array_merge( $this->fields, $this->newfields );
		$array['url'] = $this->permalink;	
		return json_encode($array);
	}
	
	public function questions() {
		$questions = DB::get_results( "SELECT id, quiz_id, question, choices, correct_choice FROM {questions} WHERE quiz_id = ?", array($this->id) );
		
		return $questions;
	}
	
	public function answers($user) {
		$answers = DB::get_results( "SELECT count(id) as count FROM {answers} WHERE quiz_id = ? AND user_id = ?", array($this->id, $user->id) );
		return $answers[0]->count;
	}

	public function tries($user) {
		return DB::get_column("SELECT count(id) as count FROM {user_quizs} WHERE quiz_id = ? AND user_id = ?", array($this->id, $user->id));
	}

	public function can_retake($user) {
		$tries = $this->tries( $user );
				
		if( $tries[0] <= 2 ) {
			return true;
		} else {
			return false;
		}
	}

	public function taken($user) {
		$pass = false;
		$tries = $this->tries( $user );
		$ques = count( $this->questions() );
		$answrs = $this->answers( $user );
				
		if( $tries[0] > 0 ) {
			$pass = true;
		} else {
			$pass = false;
		}
		
		if( $ques == $answrs ) {
			$pass = true;
		} else {
			$pass = false;
		}
				
		return $pass;		
	}

	public function retake($user) {
		DB::query( "DELETE FROM {answers} WHERE quiz_id = ? AND user_id = ?", array($this->id, $user->id) );
	}
	
	public function display_options($options, $num, $live = true) {
		$rows = explode( ';', $options );
		$ret = '';
				
		foreach( $rows as $row ) {
			$bits = explode( ':', $row );
			if( $live == true ) {
				$ret .= '<li><input type="radio" name="question[' . $num .']" value="' . $bits[0] . '"> ' . $bits[1] . '</li>' . "\n";
			} else {
				$ret .= '<li>' . $bits[1] . '</li>' . "\n";
			}
		}
		
		return $ret;
	}
}
?>
