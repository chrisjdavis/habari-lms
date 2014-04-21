<?php
namespace Habari;

class LMSPlugin extends Plugin
{
	private $output;
 
	public function filter_autoload_dirs($dirs) {
		$dirs[] = __DIR__ . '/classes';
		return $dirs;
	}

	public function action_init() {
		DB::register_table( 'tracks' );
		DB::register_table( 'courses' );
		DB::register_table( 'units' );
		DB::register_table( 'alerts' );
		DB::register_table( 'progress' );
		DB::register_table( 'quizs' );
		DB::register_table( 'questions' );
		DB::register_table( 'answers' );
		DB::register_table( 'user_quizs' );		
		
		$this->add_support_files();
		$this->add_templates();
	}

	public function action_plugin_activation( $plugin_file ) {
		Post::add_new_type( 'track' );
		Post::add_new_type( 'course' );
		Post::add_new_type( 'unit' );
		Post::add_new_type( 'quiz' );
		
		$group = UserGroup::get_by_name( _t( 'authenticated' ));
		$group->grant( 'post_track', 'read');
		$group->grant( 'post_course', 'read');
		$group->grant( 'post_unit', 'read');
		$group->grant( 'post_quiz', 'read');
	}

	public function action_plugin_activated( $plugin_file ) {
		$this->create_tracks_table();
		$this->create_courses_table();
		$this->create_units_table();
		$this->create_alerts_table();
		$this->create_quizs_table();
		$this->create_questions_table();
		$this->create_answers_table();
	}

	public function filter_post_type_display($type, $g_number)	{
		switch($type) {
			case 'course':
				switch($g_number) {
					case 'singular':
						return _t('Course');
					case 'plural':
						return _t('Courses');
				}
			break;
			case 'unit':
				switch($g_number) {
					case 'singular':
						return _t('Unit');
					case 'plural':
						return _t('Units');
				}
			break;
			case 'track':
				switch($g_number) {
					case 'singular':
						return _t('Track');
					case 'plural':
						return _t('Tracks');
				}
			break;
			case 'quiz':
				switch($g_number) {
					case 'singular':
						return _t('Quiz');
					case 'plural':
						return _t('Quizes');
				}
			break;
		}
		
		return $type;
	}

	private function add_support_files() {
		Stack::add('template_stylesheet', array($this->get_url('/templates/lms.css'), 'screen, projection'), 'lms_styles');
		Stack::add('template_header_javascript', $this->get_url('/templates/js/jquery.knob.js'), 'jquery.knob', 'jquery');
	}

	private function add_templates() {
		$this->add_template( 'track.multiple', dirname(__FILE__) . '/templates/track.multiple.php' );
		$this->add_template( 'course.multiple', dirname(__FILE__) . '/templates/course.multiple.php' );
		$this->add_template( 'course.single', dirname(__FILE__) . '/templates/course.single.php' );
		$this->add_template( 'course.single.syllabus', dirname(__FILE__) . '/templates/course.single.syllabus.php' );
		$this->add_template( 'course.single.nav', dirname(__FILE__) . '/templates/course.single.nav.php' );
		$this->add_template( 'course.unit.single', dirname(__FILE__) . '/templates/course.unit.single.php' );
		$this->add_template( 'course.new', dirname(__FILE__) . '/templates/course.new.php' );
		$this->add_template( 'course.dropzone', dirname(__FILE__) . '/templates/course.dropzone.php' );
		$this->add_template( 'unit.new', dirname(__FILE__) . '/templates/unit.new.php' );
		$this->add_template( 'unit.dropzone', dirname(__FILE__) . '/templates/unit.dropzone.php' );
		$this->add_template( 'quiz.new', dirname(__FILE__) . '/templates/quiz.new.php' );
		$this->add_template( 'unit.quiz.single', dirname(__FILE__) . '/templates/unit.quiz.single.php' );
	}

	private function create_tracks_table() {
		$sql = "CREATE TABLE " . DB::table('tracks') . " (
			id int(11) unsigned NOT NULL AUTO_INCREMENT,
			post_id int(11) unsigned DEFAULT '0',
			track_permissions int(11) unsigned DEFAULT '0',
			track_hero varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			
		return DB::dbdelta( $sql );
	}

	private function create_courses_table() {
		$sql = "CREATE TABLE " . DB::table('courses') . " (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `post_id` int(11) unsigned DEFAULT '0',
		  `parent` int(11) unsigned DEFAULT '0',
		  `locked` int(11) unsigned DEFAULT NULL,
		  `course_permissions` int(11) unsigned DEFAULT '0',
		  `course_category` int(11) unsigned DEFAULT '0',
		  `course_group` int(11) unsigned DEFAULT '0',
		  `course_type` varchar(255) DEFAULT NULL,
		  `course_badge` int(11) unsigned DEFAULT '0',
		  `course_hero` varchar(255) DEFAULT NULL,
		  `course_length` varchar(255) DEFAULT NULL,
		  `course_prereq` int(11) unsigned DEFAULT '0',
		  `course_meeting_dates` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;";
			
		return DB::dbdelta( $sql );
	}

	private function create_units_table() {
		$sql = "CREATE TABLE " . DB::table('units') . " (
			  id int(11) unsigned NOT NULL AUTO_INCREMENT,
			  post_id int(11) unsigned DEFAULT 0,
			  parent int(11) unsigned DEFAULT 0,
			  pinned int(11) unsigned DEFAULT 0,
			  locked int(11) unsigned DEFAULT 0,
			  views int(11) unsigned DEFAULT 0,
			  length VARCHAR(255) NULL,
			  video VARCHAR(255) NULL,
			  audio VARCHAR(255) NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			
		return DB::dbdelta( $sql );
	}
	
	private function create_alerts_table() {
		$sql = "CREATE TABLE " . DB::table('alerts') . " (
		id int(11) NOT NULL AUTO_INCREMENT,
		post_id int(11) NOT NULL,
		user_id int(11) DEFAULT NULL,
		message varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `post` (`post_id`,`user_id`)
		) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	}

	private function create_quizs_table() {
		$sql = "CREATE TABLE " . DB::table('quizs') . " (
			id int(11) unsigned NOT NULL AUTO_INCREMENT,
			post_id int(11) unsigned DEFAULT '0',
			course_id int(11) unsigned DEFAULT '0',
			unit_id int(11) unsigned DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			
		return DB::dbdelta( $sql );
	}

	private function create_user_quizs_table() {
		$sql = "CREATE TABLE " . DB::table('user_quizs') . " (
			id int(11) unsigned NOT NULL AUTO_INCREMENT,
			user_id int(11) unsigned DEFAULT '0',
			quiz_id int(11) unsigned DEFAULT '0',
			result int(11) unsigned DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			
		return DB::dbdelta( $sql );
	}
	
	private function create_questions_table() {
		$sql = "CREATE TABLE " . DB::table('questions') . " (
			id int(11) unsigned NOT NULL AUTO_INCREMENT,
			quiz_id int(11) unsigned DEFAULT '0',
			question varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			choices varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			correct_choice int(11) unsigned DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			
		return DB::dbdelta( $sql );
	}
	
	private function create_answers_table() {
		$sql = "CREATE TABLE " . DB::table('answers') . " (
			id int(11) unsigned NOT NULL AUTO_INCREMENT,
			quiz_id int(11) unsigned DEFAULT '0',
			question_id int(11) unsigned DEFAULT '0',
			answer varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			passed int(11) unsigned DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			
		return DB::dbdelta( $sql );
	}

	private function temp_id($prefix = '') {
		$user = User::identify();

		$ids = $user->info->ids;
			
		if(!is_array($ids)) {
			$ids = array();
		}
		
		if(!isset($ids[$prefix])) {
			$ids[$prefix] = 0;
		}
		
		$ids[$prefix] = $ids[$prefix] + 1;
		$user->info->ids = $ids;
		$user->info->commit();

		$id = sprintf('%05d-', $user->id) . $prefix . sprintf('%0' . (7 - strlen($prefix)) . 'd', $ids[$prefix]);
		return $id;
	}

	private function increment_views($topic) {
		$sample_rate = 1;
		
		if( mt_rand(1, $sample_rate) == 1 ) {
			$views = $topic->views + 1;
			$topic->views = $views;
			$topic->update();
		}
	}

	private function create_dir($path) {
		if ( !is_dir( $path ) ){
			mkdir( $path, 0777 );
		}
	}

	private function make_safe_filename( $safe_file ) {
		$safe_file = str_replace( "#", "No.", $safe_file );
		$safe_file = str_replace( "$", "Dollar", $safe_file );
		$safe_file = str_replace( "%", "Percent", $safe_file );
		$safe_file = str_replace( "^", "", $safe_file );
		$safe_file = str_replace( "&", "and", $safe_file );
		$safe_file = str_replace( "*", "", $safe_file );
		$safe_file = str_replace( "?", "", $safe_file );
		$safe_file = str_replace( " ", "", $safe_file );
		return $safe_file;
	}
	
	private function make_safe( $file ) {
		// check that this is an image, and not a file.
		$safe_file = $file['files']['name'][0];
		$safe_file = $this->make_safe_filename( $safe_file );
		
		return $safe_file;
	}

	private function upload($file, $upload_dir) {
		$return = new \stdClass();
		if( $file != '' ) {
			$cleaned = $this->make_safe( $file );
			$this->create_dir( $upload_dir );
			$path = $upload_dir . '/' . $cleaned;
						
			$finfo = new \finfo;
			$fileinfo = $finfo->file($file['files']['tmp_name'][0], FILEINFO_MIME);
			$mime = explode( ';', $fileinfo );
			
			if( copy($file['files']['tmp_name'][0], $path) ) {
				$file_name = $file['files']['name'][0];
				$file_size = $file['files']['size'][0];
				
				if( $file_size > 999999 ) {
					$div = $file_size / 1000000;
					$file_size = round( $div, 1 ) . ' MB';
				} else {
					$div = $file_size / 1000;
					$file_size = round( $div, 1 ) . ' KB';
				}
				
				$return->document = $path;
			}
		}
				
		return $return;
	}

	private function create_forum($course) {
		$forum = Forum::get( array('slug' => 'lms') );
				
		$postdata = array(
				'content_type'	=>	Post::type('topic'),
				'title' 		=>	$course->title,
				'slug'			=>	Utils::slugify( 'Forum ' . $course->title ),
				'content'		=>	'',
				'parent'		=>	$forum->id,
				'pinned'		=>	0,
				'locked'		=>	0,				
				'user_id'		=>	User::identify()->id,
				'status'		=>	Post::status('published'),
				'pubdate'		=>	DateTime::date_create( date(DATE_RFC822) ),
			);
		
		$forum = Topic::create( $postdata );
		$course->course_group = $forum->id;
		$course->update();
	}

	public function filter_user_get($out, $name, $user) {
		switch($name) {
			case 'gravatar' :
				$image = Site::get_url('theme') . '/images/default_avatar.png';
				$out = '<img src="https://www.gravatar.com/avatar/' . md5( strtolower( trim( $user->email ) ) ) . '?d=' . urlencode( $image ) . '" alt="personal avatar" class="gravatar">';
			break;
		}
		
		return $out;
	}

	public function filter_posts_get_paramarray($paramarray) {
		$queried_types = Posts::extract_param($paramarray, 'content_type');

		if($queried_types && in_array(Post::type('track'), $queried_types)) {
			$paramarray['post_join'][] = '{tracks}';
			$default_fields = isset($paramarray['default_fields']) ? $paramarray['default_fields'] : array();
			$default_fields['{tracks}.track_permissions'] = '';
			$default_fields['{tracks}.track_hero'] = '';

			$paramarray['default_fields'] = $default_fields;
		}
				
		if($queried_types && in_array(Post::type('course'), $queried_types)) {
			$paramarray['post_join'][] = '{courses}';
			$default_fields = isset($paramarray['default_fields']) ? $paramarray['default_fields'] : array();
			$default_fields['{courses}.course_permissions'] = '';
			$default_fields['{courses}.course_category'] = '';
			$default_fields['{courses}.course_group'] = '';
			$default_fields['{courses}.locked'] = '';
			$default_fields['{courses}.course_type'] = '';
			$default_fields['{courses}.course_badge'] = '';
			$default_fields['{courses}.course_hero'] = '';
			$default_fields['{courses}.course_length'] = '';
			$default_fields['{courses}.course_prereq'] = '';
			$default_fields['{courses}.course_meeting_dates'] = '';

			$paramarray['default_fields'] = $default_fields;
		}

		if($queried_types && in_array(Post::type('unit'), $queried_types)) {
			$paramarray['post_join'][] = '{units}';
			$default_fields = isset($paramarray['default_fields']) ? $paramarray['default_fields'] : array();
			$default_fields['{units}.parent'] = '';
			$default_fields['{units}.pinned'] = '';
			$default_fields['{units}.locked'] = '';
			$default_fields['{units}.views'] = '';
			$default_fields['{units}.length'] = '';
			$default_fields['{units}.video'] = '';

			$paramarray['default_fields'] = $default_fields;
		}
		
		if($queried_types && in_array(Post::type('quiz'), $queried_types)) {
			$paramarray['post_join'][] = '{quizs}';
			$default_fields = isset($paramarray['default_fields']) ? $paramarray['default_fields'] : array();
			$default_fields['{quizs}.course_id'] = '';
			$default_fields['{quizs}.unit_id'] = '';

			$paramarray['default_fields'] = $default_fields;
		}
				
		return $paramarray;
	}

	public function filter_post_schema_map_track($schema, $post) {
		$schema['tracks'] = $schema['*'];
		$schema['tracks']['post_id'] = '*id';
		
		return $schema;
	}

	public function filter_post_schema_map_course($schema, $post) {
		$schema['courses'] = $schema['*'];
		$schema['courses']['post_id'] = '*id';
		
		return $schema;
	}
	
	public function filter_post_schema_map_unit($schema, $post) {
		$schema['units'] = $schema['*'];
		$schema['units']['post_id'] = '*id';
		
		return $schema;
	}

	public function filter_post_schema_map_quiz($schema, $post) {
		$schema['quizs'] = $schema['*'];
		$schema['quizs']['post_id'] = '*id';
		
		return $schema;
	}

	public function filter_default_rewrite_rules( $rules ) {
		$this->add_rule('"tracks"', 'display_tracks');
		$this->add_rule('"courses"', 'display_courses');
		
		// create a new course, under a specific track.
		$this->add_rule('"track"/track/"new"/id', 'display_course_new');
		$this->add_rule('"track"/track/"edit"/id', 'display_course_edit');
		
		// display a specific course, and its associated information
		$this->add_rule('"track"/slug/"course"/course/"syllabus"', 'display_course_syllabus');	
		$this->add_rule('"track"/slug/"course"/course', 'display_course');

		// create a specific unit, under a specific course.
		$this->add_rule('"course"/course/"new"/id', 'display_unit_new');
		$this->add_rule('"course"/course/"edit"/id', 'display_unit_edit');
		$this->add_rule('"start"/"course"/course', 'start_course');
		
		// display a specific course, and all its associate info.
		$this->add_rule('"course"/slug/"unit"/unit', 'display_course_unit');
		
		// create a new quiz, under a specific unit.
		$this->add_rule('"unit"/unit/"quiz"/quiz', 'display_unit_quiz');
		$this->add_rule('"unit"/unit/"new"/id', 'display_quiz_new');
		$this->add_rule('"unit"/unit/"edit"/id', 'display_quiz_edit');
		
		// authentication crap
		$this->add_rule('"partner"/"login"', 'lms_login');
		
		return $rules;
	}

	public function theme_route_lms_login($theme, $params) {		
		$name = $_POST['username'];
		$pass = $_POST['password'];
		
		if ( ( null != $name ) || ( null != $pass ) ) {
			$user = User::authenticate( $name, $pass );
			if ( ( $user instanceOf User ) && ( $user != false ) ) {
				// if there's an unused password reset token, unset it to make sure there's no possibility of a compromise that way
				if ( isset( $user->info->password_reset ) ) {
					unset( $user->info->password_reset );
				}
			
				/* Successfully authenticated. */
				// Timestamp last login date and time.
				$user->info->authenticate_time = DateTime::create()->format( 'Y-m-d H:i:s' );
				$user->update();
			
				// Remove left over expired session error message.
				if ( Session::has_errors( 'expired_session' ) ) {
					Session::remove_error( 'expired_session' );
				}
			
				$login_session = Session::get_set( 'login' );
				if ( ! empty( $login_session ) ) {
					/* Now that we know we're dealing with the same user, transfer the form data so he does not lose his request */
					if ( ! empty( $login_session['post_data'] ) ) {
						Session::add_to_set( 'last_form_data', $last_form_data['post'], 'post' );
					}
					
					if ( ! empty( $login_session['get_data'] ) ) {
						Session::add_to_set( 'last_form_data', $last_form_data['get'], 'get' );
					}
			
					// don't bother parsing out the URL, we store the URI that was requested, so just append that to the hostname and we're done
					$login_dest = Site::get_url('host') . $login_session['original'];
				} else {
					$login_session = null;
					$login_dest = Site::get_url( 'admin' );
				}
			
				// filter the destination
				$login_dest = Plugins::filter( 'login_redirect_dest', $login_dest, $user, $login_session );
				
				// finally, redirect to the destination
				Utils::redirect( $login_dest );
				
				return true;
			}
		}
	}

	public function theme_route_start_course($theme, $params) {	
		$course = Course::get( array('slug' => $params['course']) );
		$units = $course->units();
		$user = User::identify();
		$started = false;
		$found_complete = array();
		$total_units = array();
		
		$course_check = DB::get_column( "SELECT id, started, completed from {progress} WHERE user_id = ? AND post_id = ?", array($user->id, $course->id) );
		
		foreach( $units as $unit ) {
			$unit_check = DB::get_results( "SELECT id, started, completed, post_id from {progress} WHERE user_id = ? AND post_id = ? AND started != 0 AND completed = 0", array($user->id, $unit->id) );

			if( !empty($unit_check) ) {
				$started = true;
				$last_unit = $unit_check[0]->post_id;
			}
		}
				
		if( empty($course_check) && $started == false ) {
			$unit = $units[0];
			$course->start( $user );
			$unit->start( $user );
			Utils::redirect( URL::get('display_course_unit', array('slug' => $course->slug, 'unit' => $unit->slug)) );
		} elseif( $started == true ) {
			$return = Unit::get( array('id' => $last_unit) );
			Utils::redirect( URL::get('display_course_unit', array('slug' => $course->slug, 'unit' => $return->slug)) );
		} else {
			if( !empty($course_check) && $started == false ) {
				foreach( $units as $unit ) {
					$complete_check = DB::get_results( "SELECT id, started, completed, post_id from {progress} WHERE user_id = ? AND post_id = ? AND started != 0 AND completed != 0", array($user->id, $unit->id) );
					
					$total_units[] = $unit->id;
					
					if( !empty($complete_check)) {
						$found_complete[] = $complete_check[0]->post_id;
					}
				}
			}
						
			$units_left = array_diff($total_units, $found_complete);
									
			if( count($units_left) > 1 ) {
				array_pop( $units_left );
			}
			
			foreach( $units_left as $key => $value ) {
				$return = Unit::get( array('id' => $value) );
				$return->start( $user );
			}
			
			Utils::redirect( URL::get('display_course_unit', array('slug' => $course->slug, 'unit' => $return->slug)) );
		}
	}

	public function theme_route_display_tracks($theme, $params) {
		$theme->tracks = Tracks::get( array('orderby' => 'id ASC') );
		$theme->display( 'track.multiple' );
	}

	public function theme_route_display_courses($theme, $params) {
		$theme->forums = Courses::get( array('orderby' => 'forum_order ASC') );
		$theme->display( 'course.multiple' );
	}
	
	public function theme_route_display_course($theme, $params) {
		$theme->track = Track::get( array('slug' => $params['slug']) );
		$theme->course = Course::get( array('slug' => $params['course']) );
		$theme->prereq = Course::get( array('id' => $theme->course->course_prereq) );
		
		$theme->display( 'course.single' );
	}

	public function theme_route_display_course_syllabus($theme, $params) {
		$theme->track = Track::get( array('slug' => $params['slug']) );
		$theme->course = Course::get( array('slug' => $params['course']) );
		$theme->prereq = Course::get( array('id' => $theme->course->course_prereq) );
		$theme->syllabi = Units::get( array('parent' => $theme->course->id) );
		
		$theme->display( 'course.single.syllabus' );
	}
		
	public function theme_route_display_course_unit($theme, $params) {
		$user = User::identify();
		
		$theme->course = Course::get( array('slug' => $params['slug']) );
		$theme->track = Track::get( array('id' => $theme->course->parent) );
		$theme->unit = Unit::get( array('slug' => $params['unit']) );
		$theme->quiz = Quiz::get( array('unit_id' => $theme->unit->id) );

		if( $user->can('admin') || $user->in_group($theme->forum->forum_group) ) {
			// continue
		} else {
			Utils::redirect(Site::get_url('habari')); exit();
		}

		$this->increment_views( $theme->unit );
		$theme->display('course.unit.single');
	}
	
	public function theme_route_display_course_new($theme, $params) {
		$theme->mode = 1;
		$theme->track = Track::get( array('slug' => $params['track']) );
		$theme->course = Course::get( array('id' => $params['id']) );
		$theme->courses = Courses::get( array('not:id' => $theme->course->id, 'parent' => $theme->track->id, 'nolimit' => true) );		
	
		$theme->display( 'course.new' );
	}

	public function theme_route_display_course_edit($theme, $params) {
		$theme->mode = 2;
				
		$theme->track = Track::get( array('slug' => $params['track']) );
		$theme->course = Course::get( array('id' => $params['id']) );
		$theme->courses = Courses::get( array('not:id' => $theme->course->id, 'parent' => $theme->track->id, 'nolimit' => true) );
		
		$theme->display( 'course.new' );
	}

	public function theme_route_display_unit_new($theme, $params) {
		$theme->mode = 1;

		$theme->course = Course::get( array('slug' => $params['course']) );
		$theme->unit = Unit::get( array('id' => $params['id']) );
	
		$theme->display( 'unit.new' );
	}

	public function theme_route_display_unit_edit($theme, $params) {
		$theme->mode = 2;
				
		$theme->course = Course::get( array('slug' => $params['course']) );
		$theme->unit = Unit::get( array('id' => $params['id']) );
		
		$theme->display( 'unit.new' );
	}

	public function theme_route_display_unit_quiz($theme, $params) {
		$theme->unit = Unit::get( array('unit' => $params['unit']) );
		$theme->course = Course::get( array('id' => $theme->unit->parent) );
		$theme->track = Track::get( array('id' => $theme->course->parent) );
		$theme->quiz = Quiz::get( array('id' => $params['quiz']) );
		
		if( $_GET['retake'] == 1 ) {
			$theme->quiz->retake( User::identify() );
		}

		$theme->display( 'unit.quiz.single' );
	}

	public function theme_route_display_quiz_new($theme, $params) {
		$theme->mode = 1;

		$theme->unit = Unit::get( array('unit' => $params['unit']) );
		$theme->course = Course::get( array('id' => $theme->unit->parent) );
		$theme->quiz = Quiz::get( array('id' => $params['id']) );

		$theme->display( 'quiz.new' );
	}

	public function theme_route_display_quiz_edit($theme, $params) {
		$theme->mode = 2;
				
		$theme->unit = Unit::get( array('unit' => $params['unit']) );
		$theme->course = Course::get( array('id' => $theme->unit->parent) );
		$theme->quiz = Quiz::get( array('id' => $params['id']) );
		
		$theme->display( 'quiz.new' );
	}
		
	public function action_auth_ajax_create_course() {
		$vars = $_GET;
				
		$track = Track::get( array('slug' => $vars['track']) );
		$title = $this->temp_id();

		$postdata = array(
				'content_type'	=>	Post::type('course'),
				'title' 		=>	$title,
				'slug'			=>	Utils::slugify($title),
				'content'		=>	'',
				'parent'		=>	$track->id,
				'user_id'		=>	User::identify()->id,
				'status'		=>	Post::status('draft'),
				'pubdate'		=>	DateTime::date_create( date(DATE_RFC822) ),
			);
				
		$p = Course::create( $postdata );

		$this->create_dir( Site::get_path('user') . '/files/uploads/courses/' . $p->id );
		
		$this->create_forum( $p );
		
		Utils::redirect( URL::get('display_course_new', array( 'track' => $track->slug, 'id' => $p->id)) );
	}
	
	public function action_auth_ajax_update_course() {
		$vars = $_POST;
		
		$t = Track::get( array('track' => $vars['track']) );
		$c = Course::get( array('id' => $vars['id']) );
				
		if( isset($vars['lock']) ) {
			$lock = 1;
		} else {
			$lock = 0;
		}
				
		$c->title = $vars['title'];
    	$c->slug = Utils::slugify( $vars['title'] );
		$c->content = $vars['content'];
		$c->tags = $vars['tags'];
		$c->locked = $lock;
		$c->status = Post::status('published');
		$c->course_prereq = $vars['prereq'] ? $vars['prereq'] : 0;
		$c->course_meeting_dates = $vars['meeting_dates'] ? $vars['meeting_dates'] : '';
				
		$c->update();
		
		Utils::redirect( URL::get('display_course', array('slug' => $t->slug, 'course' => $c->slug)) );
	}

	public function action_auth_ajax_create_unit() {
		$vars = $_GET;

		$course = Course::get( array('slug' => $vars['course']) );
		$title = $this->temp_id();

		$postdata = array(
				'content_type'	=>	Post::type('unit'),
				'title' 		=>	$title,
				'slug'			=>	Utils::slugify($title),
				'content'		=>	'',
				'parent'		=>	$course->id,
				'user_id'		=>	User::identify()->id,
				'status'		=>	Post::status('draft'),
				'pubdate'		=>	DateTime::date_create( date(DATE_RFC822) ),
			);
				
		$u = Unit::create( $postdata );
		
		$this->create_dir( Site::get_path('user') . '/files/uploads/courses/' . $course->id . '/' . $u->id );
		Utils::redirect( URL::get('display_unit_new', array( 'course' => $course->slug, 'id' => $u->id)) );
	}
	
	public function action_auth_ajax_update_unit() {
		$vars = $_POST;
				
		$course = Course::get( array('id' => $vars['course']) );		
		$unit = Unit::get( array('id' => $vars['id']) );
		
		$this->create_dir( Site::get_path('user') . '/files/uploads/courses/' . $course->id . '/' . $unit->id );
		
		if( isset($vars['lock']) ) {
			$lock = 1;
		} else {
			$lock = 0;
		}

		if( $vars['media_link'] ) {
			$video_l = explode( '?v=', $vars['media_link'] );
			
			if( count($video_l) > 2 ) {
				$video_l = '//youtube.com/embed/' . $video_l[1];
			} else {
				$video_l = $unit->video;
			}
		}

		$unit->title = $vars['title'];
    	$unit->slug = Utils::slugify( $vars['title'] );
		$unit->content = $vars['content'];
		$unit->tags = $vars['tags'];
		$unit->locked = $lock;
		$unit->status = Post::status('published');
		$unit->length = $vars['length'];
		$unit->video = $vars['media_link'] ? $video_l : '';
		
		$unit->update();
		
		Utils::redirect( URL::get('display_unit_edit', array( 'course' => $course->slug, 'id' => $unit->id)) );
		
	}

	public static function format_tags( $terms, $between = ', ', $between_last = null, $sort_alphabetical = false ) {
		$array = array();
		
		if ( !$terms instanceof Terms ) {
			$terms = new Terms( $terms );
		}

		foreach ( $terms as $term ) {
			$array[$term->term] = $term->term_display;
		}

		if ( $sort_alphabetical ) {
			ksort( $array );
		}

		if ( $between_last === null ) {
			$between_last = _t( ' and ' );
		}

		$fn = function($a, $b) {
			return $a;
		};
		
		$array = array_map( $fn, $array, array_keys( $array ) );
		$last = array_pop( $array );
		$out = implode( $between, $array );
		$out .= ( $out == '' ) ? $last : $between_last . $last;
		
		return $out;
	}
	
	public function action_comment_insert_before($comment) {
		$comment->status = Comment::status('approved');
		return $comment;
	}
	
	public function action_auth_ajax_add_lms_comment() {
		$vars = $_POST;
		
		$user = User::identify();
		$return = array();
		$name = $user->displayname;
		$email = $user->email;
		$url = Site::get_url('habari');
		
		$unit = Unit::get( array('id' => $vars['id']) );
		$content = $vars['response'];

		$comment = new Comment( array(
			'post_id'	=> $vars['id'],
			'name'		=> $name,
			'email'		=> $email,
			'url'		=> $url,
			'ip'		=> sprintf( "%u", ip2long( Utils::get_ip() ) ),
			'content'	=> $content,
			'status'	=> Comment::status('approved'),
			'date'		=> DateTime::date_create(),
			'type'		=> Comment::type('comment'),
		) );

		try {
			$comment->insert();
			$status = 200;
			$data = array();
			$message = 'Response Added.';
		} catch( Exception $e ) {
			$status = 401;
			$data = array();
			$message = 'Response could not be added.';
		}
		
		$ar = new AjaxResponse( $status, $data, $message );
		$ar->out();
	}
	
	public function action_auth_ajax_lms_upload() {
	    $file = $_FILES;
		$message = '';
		$data = array();
		$course = Course::get( array('slug' => $_POST['name']) );
		$unit = Unit::get( array('id' => $_POST['id']) );
		
	    $dir = Site::get_path('user') . '/files/uploads/courses/' . $course->id . '/' . $unit->id;
	    
	    foreach( $_FILES['files']['name'] as $index => $value ) {
			$file = $this->upload( $file, $dir, $value );

			if( $file->document ) {
				$status = 200;
			} else {
				$status = 401;
			}
			
			$ar = new AjaxResponse( $status, $message, $data );
			$ar->out();
		}
    }
  
	public function action_auth_ajax_add_quiz() {
		$vars = $_GET;
		
		$title = $this->temp_id();
		$unit = Unit::get( array('id' => $vars['unit']) );
		
		$postdata = array(
				'content_type'	=>	Post::type('quiz'),
				'title' 		=>	$title,
				'slug'			=>	Utils::slugify( $title ),
				'content'		=>	'',
				'course_id'		=>	$vars['course'],
				'unit_id'		=>	$vars['unit'],
				'user_id'		=>	User::identify()->id,
				'status'		=>	Post::status('draft'),
				'pubdate'		=>	DateTime::date_create( date(DATE_RFC822) ),
			);		

		$q = Quiz::create( $postdata );
		$this->create_dir( Site::get_path('user') . '/files/uploads/courses/' . $vars['course'] . '/' . $vars['unit'] . '/' . $q->id );
		
		Utils::redirect( URL::get('display_quiz_new', array( 'unit' => $unit->slug, 'id' => $q->id)) );
	}
	
	public function action_auth_ajax_update_quiz() {
		$vars = $_POST;
		
		$unit = Unit::get( array('id' => $vars['unit']) );	
		$quiz = Quiz::get( array('id' => $vars['id']) );
		
		foreach( $vars['question_text'] as $key => $value ) {
			if( $vars['question_id'][$key] != '' ) {
				$check = DB::query( "SELECT id FROM {questions} WHERE id = ?", array($vars['question_id'][$key]) );
			} else {
				$check = false;
			}
						
			$postdata = array(
				'quiz_id'			=>	$quiz->id,
				'question'			=>	$value,
				'choices'			=>	$vars['question_answers'][$key],
				'correct_choice'	=>	$vars['question_answer'][$key],
			);

			if( $check ) {
				$insert = DB::update( DB::table('questions'), $postdata, array('id' => $vars['question_id'][$key]) );
			} else {
				$insert = DB::insert( DB::table('questions'), $postdata );	
			}
		}

		$quiz->content = $vars['content'];
		$quiz->status = Post::status('published');
		
		$quiz->update();
		
		Utils::redirect( URL::get('display_quiz_edit', array( 'unit' => $unit->slug, 'id' => $quiz->id)) );
	}

	public function action_auth_ajax_check_quiz() {
		$vars = $_POST;
		$passed = 0;
		$failed = 0;
		$pass = 0;
		
		$user = User::identify();
		$quiz = Quiz::get( array('id' => $vars['quiz_id']) );
		$unit = Unit::get( array('id' => $quiz->unit_id) );
				
		$total = count( $quiz->questions() );
		
		foreach( $vars['question'] as $key => $value ) {
			$given = $value;
			$correct = DB::get_results( "SELECT id, correct_choice FROM {questions} WHERE id = ?", array( $key ) );
			
			$data = array(
				'user_id'		=>	$user->id,
				'quiz_id'		=>	$quiz->id,
				'question_id'	=>	$correct[0]->id,
				'answer'		=>	$value
			);
			
			if( $given == $correct[0]->correct_choice ) {
				$data['passed'] = 1;
				$passed++;
			} else {
				$data['passed'] = 0;
				$failed++;
			}
			
			$processed = DB::insert( DB::table('answers'), $data );
		}
		
		$count1 = $passed / $total;
		$count2 = $count1 * 100;
		$percentage = number_format($count2, 0);
		
		if( $percentage >= 75 ) {
			$pass = 1;
			$unit->complete( $user );
		}

		$postdata = array( 'quiz_id' => $quiz->id, 'user_id' => $user->id, 'result' => $pass );
		DB::insert( DB::table('user_quizs'), $postdata );

		Utils::redirect( URL::get('display_unit_quiz', array('unit' => $unit->slug, 'quiz' => $quiz->id)) );
				
		exit();
	}
	
	public function action_auth_ajax_mark_unit_complete() {
		$vars = $_GET;
		$user = User::identify();
		$unit = Unit::get( array('id' => $vars['unit']) );
		$course = Course::get( array('id' => $unit->parent) );
		$track = Track::get( array('id' => $course->parent) );
		
		$unit->complete( $user );
		$course->check_for_completeness( $user );
		
		if( $unit->ascend() ) {
			Utils::redirect( $course->course_start_url( $user ) );
		} else {
			Utils::redirect( URL::get('display_tracks') );
		}
		
		exit();
	}
}
?>