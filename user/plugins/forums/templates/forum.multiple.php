<?php namespace Habari; ?>
<?php $theme->title = 'Forums - '; ?>
<?php $theme->display('header'); ?>
<style>

hr.hidden {
	background:none;
	border:none;
}

table {
 	border: 1px solid #ddd;
}

table tr + tr {
 	border-top: 1px solid #ddd;
}

table td {
	padding: 10px 10px;
	background:#eee;
}
	table td a {
		font-weight:bold;
		text-decoration: none;
	}

	table td p {
		margin-bottom:0px;
	}

.green-circle {
	color: green;
}

.grey-circle {
	color: #aaa;
}

.stats {
	color: #999;
	text-align: right;
}

.last_post {
	font-size: 12px;
	width:30%;
}
	.last_post img {
		float:left;
		width:33px;
		height:33px;
		margin-right:5px;
		margin-top:4px;
	}

	.last_post span {
		display:block;
	}
		.last_post span.date {
			margin-top:-5px;
		}
</style>
	<div class="topModule clearfix" style="margin-top:0px;">
		<div class="container pageTitle">
			<div class="eight columns">
				<h1>Forums</h1>
				<h2>Discussions for the Faithful</h2>
			</div>
			<div class="eight columns">
				<?php $theme->display('search.form'); ?>
			</div>
		</div>
		<div class="container pageContent clearfix">
			<?php foreach( $forums as $forum ) { ?>
				<?php if( $forum->forum_category == 1 && $user->in_group($forum->forum_group) || $user->can('admin') ) { ?>
					<h3 style="float:left;"><?php echo $forum->title_out; ?></h3>
					<div id="controls" style="float:right;">
						<?php if( $loggedin ) { ?>
							<a style="margin: 0px;" id="" class="left button-small rounded5 green" href="<?php URL::out('auth_ajax', Utils::WSSE(array('context' => 'create_forum', 'forum' => $forum->slug))); ?>" title=""><?php echo strtoupper('New Discussion'); ?></a>
						<?php } ?>
					</div>
					<table width="100%">
						<tbody>
							<?php foreach( $forum->topics() as $topic ) { ?>
							<tr>
								<td width="10px" style="text-align:center;border-right:1px solid #ddd;">
									<?php if( $topic->locked == 1 ) { ?><i class="fa fa-lock"></i><?php } ?>
									<?php echo $topic->have_seen( $user->id ); ?>
								</td>
								<td width="65%;">
									<a href="<?php URL::out('display_forum_thread', array('slug' => $forum->slug, 'thread' => $topic->slug)); ?>" title="<?php echo $topic->title; ?>"><?php echo $topic->title_out; ?></a>
									<p><small>Started by <?php echo $topic->author->displayname; ?>, <?php echo ucfirst($topic->pubdate->fuzzy()); ?></small></p>
								</td>
								<td class="stats" width="10%">
									<?php echo $topic->comments->count(); ?> replies<br>
									<?php echo $topic->views; ?> Views
								</td>
								<td class="last_post">
									<div>
										<img src="<?php echo $theme->last_activity( $topic )->image; ?>">
										<span class="name"><?php echo $theme->last_activity( $topic )->name; ?></span>
										<span class="date"><?php echo $theme->last_activity( $topic )->time; ?></span>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<hr class="hidden">
				<?php } else { ?>
					<?php if( $forum->forum_category != 1 ) { ?>
					<h3 style="float:left;"><?php echo $forum->title_out; ?></h3>
					<div id="controls" style="float:right;">
						<?php if( $loggedin ) { ?>
								<a style="margin: 0px;" id="" class="left button-small rounded5 green" href="<?php URL::out('auth_ajax', Utils::WSSE(array('context' => 'create_forum', 'forum' => $forum->slug))); ?>" title=""><?php echo strtoupper('New Discussion'); ?></a>
						<?php } ?>
					</div>
					<table width="100%">
						<tbody>
							<?php foreach( $forum->topics() as $topic ) { ?>
							<tr>
								<td width="10px" style="text-align:center;border-right:1px solid #ddd;">
									<?php if( $topic->locked == 1 ) { ?><i class="fa fa-lock"></i><?php } ?>
									<i class="fa fa-circle green-circle"></i>
								</td>
								<td width="65%;">
									<a href="<?php URL::out('display_forum_thread', array('slug' => $forum->slug, 'thread' => $topic->slug)); ?>" title="<?php echo $topic->title; ?>"><?php echo $topic->title_out; ?></a>
									<p><small>Started by <?php echo $topic->author->displayname; ?>, <?php echo ucfirst($topic->pubdate->fuzzy()); ?></small></p>
								</td>
								<td class="stats" width="10%">
									<?php echo $topic->comments->count(); ?> replies<br>
									<?php echo $topic->views; ?> Views
								</td>
								<td class="last_post">
									<div>
										<img src="<?php echo $theme->last_activity( $topic )->image; ?>">
										<span class="name"><?php echo $theme->last_activity( $topic )->name; ?></span>
										<span class="date"><?php echo $theme->last_activity( $topic )->time; ?></span>
									</div>
								</td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
					<hr class="hidden">
					<?php } ?>
				<?php } ?>
			<?php } ?>
			<div id="pagination_holder" class="sixteen columns">
				<div class="pagination pagination-info">
					<?php echo $theme->prev_page_link('PREVIOUS PAGE', array('button-small', 'rounded3', 'brown', 'left')); ?></div> <div style="float:right;"><?php echo $theme->next_page_link($forums, $theme->page, 'NEXT PAGE'); ?></div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $theme->display('footer'); ?>
