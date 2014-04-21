<?php namespace Habari; ?>
<?php $theme->title = 'Forums - '; ?>
<?php $theme->display('header'); ?>
<style>
@import url( '<?php Site::out_url('theme'); ?>/css/redactor.forum.css' );

.author {
	padding: 5px;
	margin-top: -20px;
	margin-bottom: 20px;
	background:#eee;
	font-weight:bold;
}

h2 a {
	text-decoration: none;
}

#reply {
	background: #333;
	margin-top: 30px;
	padding-bottom: 30px;
	border-radius: none !important;
	min-height: 300px;
}
	#reply h4 {
		font-family: 'Oswald', sans-serif;
		text-transform: uppercase;
		color: #666;
		margin-bottom:20px;
		margin-top:20px;
		color:#fff;
	}
	
	#reply .pageContent {
		background: #333;
		padding:0px;
		min-height: 350px;
	}

	#reply button {
		margin-top:10px;
		margin-left:0px;
	}

	.redactor_editor {
		min-height: 200px;
	}
	
	.spacer {
		margin-top:25px;
	}
	
	.owner {
		color: #fff;
		background: gold;
	}
	
#files {
	background:#eee;
	padding:10px;
	float:right;
	margin-right:0px;
}
</style>
	<div class="topModule clearfix" style="margin-top:0px;">
		<div class="container pageTitle">
			<div class="eight columns">
				<h1><?php echo $topic->title_out; ?></h1>
				<?php if( $forum->forum_lms == 1 ) { ?>
					<?php 
						$course = Course::get( array('title' => $topic->title) );
						$track = Track::get( array('id' => $course->parent) );
					?>
					<h2><a href="<?php URL::out('display_course', array('slug' => $track->slug, 'course' => $course->slug)); ?>" title="Back to <?php echo $course->title; ?>">&laquo; Back to the <?php echo $course->title_out; ?> Course</a></h2>
				<?php } else { ?>
					<h2><a href="<?php URL::out('display_forums'); ?>" title="Back to Forums">&laquo; <?php echo $forum->title_out; ?></a></h2>
				<?php } ?>
			</div>
			<?php if( $user->can('edit') ) { ?>
			<div class="eight columns">
				<a style="margin-right:0px;" class="right button-small rounded5 brown" href="<?php URL::out('display_forum_edit', array('forum' => $forum->slug, 'id' => $topic->id)); ?>" title="Edit <?php echo $topic->title; ?>"><?php echo strtoupper('Edit ' . $topic->title_out); ?></a>
			</div>
			<?php } ?>
		</div>		
		<div class="container pageContent clearfix">
			<span class="owner author columns sixteen"><?php echo $topic->author->displayname; ?></span>
			<div id="videoWrapper" class="sixteen columns">
				<div class="two columns">
					<img src="<?php echo $theme->avatar( $topic->author->email ); ?>">
				</div>
				<?php if( $topic->list_files() != FALSE ) { ?>
				<div class="columns ten">
					<?php echo $topic->content_out; ?>
				</div>
				<div id="files" class="columns three">
					<h4>Attached Files</h4>
					<ul class="attached_files">
					<?php foreach( $topic->list_files() as $file ) { ?>
						<?php if( !in_array($file, array('.', '..')) ) { ?>
							<li><a href="<?php echo Site::get_url('user') . '/files/uploads/forums/' . $topic->id . '/' . $file; ?>"><?php echo $file; ?></a></li>
						<?php } ?>
					<?php } ?>
					</ul>
				</div>
				<?php } else { ?>
				<div class="columns thirteen">
					<?php echo $topic->content_out; ?>
				</div>
				<?php } ?>
			</div>
			<div id="responses">
				<?php foreach( $topic->comments->approved as $comment ) { ?>
				<div class="clear"></div>
				<span class="spacer author columns sixteen"><?php echo $comment->name; ?></span>
				<div class="sixteen columns">
					<div class="two columns">
						<img src="<?php echo $theme->avatar( $comment->email ); ?>">
					</div>
					<div class="columns thirteen">
						<?php echo $comment->content; ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<div id="reply" class="topModule clearfix" style="margin-top:0px;">
	<div class="container pageContent">
		<div class="sixteen columns">
			<?php if( $topic->locked == 0 ) { ?>
			<h4>Reply to this Discussion</h4>
			<div class="one columns">
				<img src="<?php echo $theme->avatar( $user->email ); ?>">
			</div>
			<div class="columns fourteen">
				<div id="redactor_content" class="redactor_editor" contenteditable="true" dir="ltr"></div>
				<p><button id="add_response" class="ajax left button-big rounded5 green">ADD YOUR REPLY</button></p>
			</div>
			<?php } else { ?>
				<?php if( $user->can('admin') ) { ?>
				<h4>Reply to this Discussion</h4>
				<div class="one columns">
					<img src="<?php echo $theme->avatar( $user->email ); ?>">
				</div>
				<div class="columns fourteen">
					<div id="redactor_content" class="redactor_editor" contenteditable="true" dir="ltr"></div>
					<p><button id="add_response" class="ajax left button-big rounded5 green">ADD YOUR REPLY</button></p>
				</div>
				<?php } else { ?>
					<h4>Replies to this Discussion are locked.</h4>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
</div>

<script src="<?php Site::out_url('theme'); ?>/js/pace.min.js"></script>
<script type="text/javascript">
$(document).ready(
	function() {
		$('#redactor_content').redactor({ 
			toolbarFixedBox: true,
			uploadFields: {
				'nonce': SCH.WSSE.nonce,
				'timestamp': SCH.WSSE.timestamp,
				'digest': SCH.WSSE.digest
			},
			imageUpload: SCH.url + '/auth_ajax/image_upload',
		});
	});

	$('#add_response').click(function() {
		var url = SCH.url + '/auth_ajax/add_comment';
		var data = {};
		data['id'] = <?php echo $topic->id; ?>;
		data['response'] = $('#redactor_content').html();
		$('.pace').addClass('pace-active');
		$.post(url, $.extend(data, SCH.WSSE), function(d) {
			if( d.response_code == 200 ) {
				setTimeout(function() {
					$('.pace').removeClass('pace-active');
					
				}, 1500);
				
				$('#redactor_content').html('');
 				$('#responses').load( window.location.href + ' #responses' );
			} else {
				setTimeout(function() {
					$('.pace').removeClass('pace-active');
					
				}, 500);				
			}
		});
		
		return false;
	});
</script>
	
<?php $theme->display( 'footer' ); ?>