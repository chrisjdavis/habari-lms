<?php namespace Habari; ?>
<?php $theme->title = 'Create new Discussion - '; ?>
<?php $theme->display('header'); ?>

<style>
input[type=file] {
  width: auto;
}

.pledge_form input[type=checkbox] {
	width: 3%;
}

.pledge_form label {
	margin-top:0px;
}

.attachments ul {list-style:none;margin-left:0px;}

.attachments > ul > li, ul.files li { width: 100%; margin-bottom: 10px !important; }
.attachments > ul > li ul li, ul.files li ul li { display: inline; margin-right: 10px; }
.attachments ul li img { margin-bottom: -5px; }
.attachments ul li.meta, ul.files li.meta { color: #9a9a9a; font-size: 11px; float: right; margin: 0; }

.fileupload-buttonbar {
	display:none;
}

.dropzone {
	border: 2px dashed #bbb;
	border-radius: 5px;
	float:left;
	width: 93.5%;
	margin-left:0px;
	padding: 30px 0px 10px;
	text-align:center;
}

.template-download {
	display:none;
}

ul.files .attached_files {
	display:none;
}

</style>
	<div class="topModule clearfix" style="margin-top:0px;">
		<div class="container pageTitle">
			<div class="eight columns">
				<?php if( $mode == 1 ) { ?>
					<h1>Forums &raquo; New Discussion</h1>
					<h2>Create a new Discussion in the <?php echo $forum->title_out; ?> Forum</h2>
				<?php } else { ?>
					<h1>Forums &raquo; Edit Discussion</h1>
					<h2>Edit <?php echo $topic->title_out; ?></h2>
				<?php } ?> 
			</div>
			<div class="eight columns">
				<?php $theme->display('search.form'); ?>
			</div>
		</div>
		<div class="container pageContent clearfix">
			<form class="pledge_form" style="margin-top:-20px;" action="<?php URL::out('auth_ajax', array('context' => 'update_forum')); ?>" method="post">
				<input type="hidden" name="forum" value="<?php echo $forum->id; ?>">
				<input type="hidden" name="id" value="<?php echo $topic->id; ?>">
				<?php echo Utils::setup_wsse(); ?>
				<p><input type="text" name="title" value="<?php echo $topic ? $topic->title : ''; ?>" placeholder="Discussion Title"></p>
				<p><input type="text" name="tags" value="<?php echo $topic ? ForumsPlugin::format_tags( $topic->tags, ' ', ', ' ) : ''; ?>" placeholder="Discussion Tags"></p>
				<textarea name="content" id="" placeholder="What do you want to discuss?" style="clear:both;width:92.75%;"><?php echo $topic ? $topic->content : ''; ?></textarea>
				<p>
					<label><input type="checkbox" value="1" name="lock" <?php if( $topic->locked == 1 ) { echo 'checked'; }?>>Lock this Discussion?</label>
					<label><input type="checkbox" value="1" name="pin" <?php if( $topic->pinned == 1 ) { echo 'checked'; }?>>Pin this Discussion to the top?</label>
				</p>
				<p>
					<button class="left button-small rounded5 green">UPDATE DISCUSSION</button>
				</p>
				<div class="clear"></div>
			</form>
			<?php $theme->display('forum.dropzone'); ?>
			<hr class="hidden">
			<h4>Attached Files</h4>
			<div class="files">
				<ol class="attached_files">
				<?php foreach( $topic->list_files() as $file ) { ?>
					<?php if( !in_array($file, array('.', '..')) ) { ?>
						<li><a href="<?php echo Site::get_url('user') . '/files/uploads/forums/' . $topic->id . '/' . $file; ?>"><?php echo $file; ?></a></li>
					<?php } ?>
				<?php } ?>
				</ol>
			</div>
		</div>
	</div>
</div>

<script src="<?php Site::out_url('theme'); ?>/js/dnd/jquery.ui.widget.js"></script>
<script src="<?php Site::out_url('theme'); ?>/js/dnd/tmpl.min.js"></script>
<script src="<?php Site::out_url('theme'); ?>/js/dnd/jquery.iframe-transport.js"></script>
<script src="<?php Site::out_url('theme'); ?>/js/dnd/jquery.fileupload.js"></script>
<script src="<?php Site::out_url('theme'); ?>/js/dnd/jquery.fileupload-ui.js"></script>
<script src="<?php Site::out_url('theme'); ?>/js/dnd.js"></script>
<script src="<?php Site::out_url('theme'); ?>/js/dnd/cors/jquery.xdr-transport.js"></script>

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
				
		$('#save_changes').click(function() {
			var url = SCH.url + '/auth_ajax/save_content';
			var data = {};
			data['id'] = SCH.post_id;
			data['content'] = $('#redactor_content').html();
			$('.pace').addClass('pace-active');
			$.post(url, $.extend(data, SCH.WSSE), function(d) {
				setTimeout(function() {
					$('.pace').removeClass('pace-active');
					$('#results').fadeIn();
				}, 1500);
			});
			
			return false;
		});
	</script>

<?php $theme->display('footer'); ?>