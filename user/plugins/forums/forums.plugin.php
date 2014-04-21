<?php
namespace Habari;

class ForumsPlugin extends Plugin
{
	public function filter_autoload_dirs($dirs) {
		$dirs[] = __DIR__ . '/classes';
		return $dirs;
	}

	public function action_init() {
		DB::register_table( 'forums' );
		DB::register_table( 'topics' );
		DB::register_table( 'alerts' );
		$this->add_templates();
	}

	public function action_plugin_activation( $plugin_file ) {
		Post::add_new_type( 'forum' );
		Post::add_new_type( 'topic' );
		
		$group = UserGroup::get_by_name( _t( 'authenticated' ));
		$group->grant( 'post_forum', 'read');
		$group->grant( 'post_topic', 'read');
	}

	public function action_plugin_activated( $plugin_file ) {
		$this->create_forums_table();
		$this->create_topics_table();
	}

	public function filter_post_type_display($type, $g_number)	{
		switch($type) {
			case 'forum':
				switch($g_number) {
					case 'singular':
						return _t('Forum');
					case 'plural':
						return _t('Forums');
				}
			break;
			case 'topic':
				switch($g_number) {
					case 'singular':
						return _t('Topic');
					case 'plural':
						return _t('Topics');
				}
			break;
		}
		
		return $type;
	}

	private function add_templates() {
		$this->add_template( 'forum.multiple', dirname(__FILE__) . '/templates/forum.multiple.php' );
		$this->add_template( 'forum.single', dirname(__FILE__) . '/templates/forum.single.php' );
		$this->add_template( 'forum.thread.single', dirname(__FILE__) . '/templates/forum.thread.single.php' );
		$this->add_template( 'forum.new', dirname(__FILE__) . '/templates/forum.new.php' );
		$this->add_template( 'forum.dropzone', dirname(__FILE__) . '/templates/forum.dropzone.php' );
	}

	private function create_forums_table() {
		$sql = "CREATE TABLE " . DB::table('forums') . " (
			  id int(11) unsigned NOT NULL AUTO_INCREMENT,
			  post_id int(11) unsigned DEFAULT 0,
			  forum_permissions int(11) unsigned DEFAULT 0,
			  forum_category int(11) unsigned DEFAULT 0,
			  forum_group int(11) unsigned DEFAULT 0,
			  forum_lms int(11) unsigned DEFAULT 0,
 			  forum_order int(11) unsigned DEFAULT 0,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			
		return DB::dbdelta( $sql );
	}

	private function create_topics_table() {
		$sql = "CREATE TABLE " . DB::table('topics') . " (
			  id int(11) unsigned NOT NULL AUTO_INCREMENT,
			  post_id int(11) unsigned DEFAULT 0,
			  parent int(11) unsigned DEFAULT 0,
			  pinned int(11) unsigned DEFAULT 0,
			  locked int(11) unsigned DEFAULT 0,
			  views int(11) unsigned DEFAULT 0,
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
			$fileinfo = $finfo->file($file['file']['tmp_name'][0], FILEINFO_MIME);
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

	public function filter_posts_get_paramarray($paramarray) {
		$queried_types = Posts::extract_param($paramarray, 'content_type');
				
		if($queried_types && in_array(Post::type('forum'), $queried_types)) {
			$paramarray['post_join'][] = '{forums}';
			$default_fields = isset($paramarray['default_fields']) ? $paramarray['default_fields'] : array();
			$default_fields['{forums}.forum_permissions'] = '';
			$default_fields['{forums}.forum_category'] = '';
			$default_fields['{forums}.forum_group'] = '';
			$default_fields['{forums}.forum_order'] = '';
			$default_fields['{forums}.forum_lms'] = '';
			
			$paramarray['default_fields'] = $default_fields;
		}

		if($queried_types && in_array(Post::type('topic'), $queried_types)) {
			$paramarray['post_join'][] = '{topics}';
			$default_fields = isset($paramarray['default_fields']) ? $paramarray['default_fields'] : array();
			$default_fields['{topics}.parent'] = '';
			$default_fields['{topics}.pinned'] = '';
			$default_fields['{topics}.locked'] = '';
			$default_fields['{topics}.views'] = '';
						
			$paramarray['default_fields'] = $default_fields;
		}
		
		return $paramarray;
	}

	public function filter_post_schema_map_forum($schema, $post) {
		$schema['forums'] = $schema['*'];
		$schema['forums']['post_id'] = '*id';
		return $schema;
	}
	
	public function filter_post_schema_map_topic($schema, $post) {
		$schema['topics'] = $schema['*'];
		$schema['topics']['post_id'] = '*id';
		return $schema;
	}

	public function filter_default_rewrite_rules( $rules ) {
		$this->add_rule('"forums"', 'display_forums');
		$this->add_rule('"forum"/forum/"new"/id', 'display_forum_new');
		$this->add_rule('"forum"/forum/"edit"/id', 'display_forum_edit');
		$this->add_rule('"forum"/slug/"topic"/thread', 'display_forum_thread');
		
		return $rules;
	}

	public function theme_route_display_forums($theme, $params) {
		$theme->forums = Forums::get( array('orderby' => 'forum_order ASC', 'forum_lms' => 0) );
		$theme->display( 'forum.multiple' );
	}
		
	public function theme_route_display_forum_thread($theme, $params) {
		$user = User::identify();
		$theme->forum = Forum::get( array('slug' => $params['slug']) );
		$theme->topic = Topic::get( array('slug' => $params['thread']) );

		if( $user->can('admin') || $user->in_group($theme->forum->forum_group) ) {
			// continue
		} else {
			Utils::redirect(Site::get_url('habari')); exit();
		}

		$theme->topic->seen( User::identify()->id );		
		$this->increment_views( $theme->topic );
		
		$theme->display('forum.thread.single');
	}
	
	public function theme_route_display_forum_new($theme, $params) {
		$theme->mode = 1;
		$theme->forum = Forum::get( array('slug' => $params['forum']) );
		$theme->topic = Topic::get( array('id' => $params['id']) );
	
		$theme->display( 'forum.new' );
	}

	public function theme_route_display_forum_edit($theme, $params) {
		$theme->mode = 2;
		$theme->forum = Forum::get( array('slug' => $params['forum']) );
		$theme->topic = Topic::get( array('id' => $params['id']) );
	
		$theme->display( 'forum.new' );
	}
		
	public function action_auth_ajax_create_forum() {
		$vars = $_GET;
				
		$forum = Forum::get( array('slug' => $vars['forum']) );
		$title = $this->temp_id();
		
		$postdata = array(
				'content_type'	=>	Post::type('topic'),
				'title' 		=>	$title,
				'slug'			=>	Utils::slugify($title),
				'content'		=>	'',
				'parent'		=>	$forum->id,
				'pinned'		=>	0,
				'locked'		=>	0,				
				'user_id'		=>	User::identify()->id,
				'status'		=>	Post::status('draft'),
				'pubdate'		=>	DateTime::date_create( date(DATE_RFC822) ),
			);
		
		$p = Topic::create( $postdata );
		
		$this->create_dir( Site::get_path('user') . '/files/uploads/forums/' . $p->id );
		
		Utils::redirect( URL::get('display_forum_new', array( 'forum' => $forum->slug, 'id' => $p->id)) );
	}
	
	public function action_auth_ajax_update_forum() {
		$vars = $_POST;

    	$t = Topic::get( array('id' => $vars['id']) );
		$f = Forum::get( array('id' => $t->parent) );
		
		if( isset($vars['lock']) ) {
			$lock = 1;
		} else {
			$lock = 0;
		}

		if( isset($vars['pin']) ) {
			$pin = 1;
		} else {
			$pin = 0;
		}
    	
    	$t->title = $vars['title'];
    	$t->slug = Utils::slugify( $vars['title'] );
		$t->content = $vars['content'];
		$t->tags = $vars['tags'];
		$t->locked = $lock;
		$t->pinned = $pin;
		$t->status = Post::status('published');
		
		$t->update();
		
		$this->create_dir( Site::get_path('user') . '/files/uploads/forums/' . $t->id );
		Utils::redirect( URL::get('display_forum_thread', array('slug' => $f->slug, 'thread' => $t->slug)) );
		
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
	
	public function action_auth_ajax_add_comment() {
		$vars = $_POST;
		
		$user = User::identify();
		$return = array();
		$name = $user->displayname;
		$email = $user->email;
		$url = Site::get_url('habari');
		
		$topic = Topic::get( array('id' => $vars['id']) );
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
	
	public function action_auth_ajax_multiple_upload() {
	    $file = $_FILES;
		$message = '';
		$data = array();
		$topic = Topic::get( array('id' => $_POST['id']) );
		
	    $dir = Site::get_path('user') . '/files/uploads/forums/' . $topic->id;
	    
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
}
?>