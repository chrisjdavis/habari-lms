<?php namespace Habari; ?>
<div class="attachments">
	<form id="fileupload" action="<?php echo Site::out_url('habari'); ?>/auth_ajax/multiple_upload" method="post" enctype="multipart/form-data">
		<?php echo Utils::setup_wsse(); ?>
		<input type="hidden" name="object" id="object" value="topic">
		<input type="hidden" name="name" id="name" value="<?php echo $topic->slug; ?>">
		<input type="hidden" name="id" id="id" value="<?php echo $topic->id; ?>">
		<div id="in_progress">
			<ul class="files">
			</ul>
		</div>
		<div class="dropzone">
			<p>Drag and drop files here to add them to this discussion.</p>
			<div class="span16 fileupload-buttonbar">
				<span class="btn success fileinput-button">
					<input type="file" name="files[]" multiple>
				</span>
			</div>
		</div>
	</form>
</div>

<script>
var fileUploadErrors = {
    maxFileSize: 'File is too big',
    minFileSize: 'File is too small',
    acceptFileTypes: 'Filetype not allowed',
    maxNumberOfFiles: 'Max number of files exceeded',
    uploadedBytes: 'Uploaded bytes exceed file size',
    emptyResult: 'Empty file upload result'
};

function slugify(text) {
	text = text.replace(/[^-a-zA-Z0-9,&\s]+/ig, '');
	text = text.replace(/-/gi, "_");
	text = text.replace(/\s/gi, "-");
	return text;
};

</script>

<script id="template-upload" type="text/html">
{% for (var i=0, files=o.files, l=files.length, file=files[0]; i<l; file=files[++i]) { %}
    <li data-name="{%=file.name%}" class="template-upload fade">
    	<ul>
			<li>{%=file.name%}</li>
        {% if (file.error) { %}
            <li class="error"><span class="label important">Error</span> {%=fileUploadErrors[file.error] || file.error%}</li>
        {% } else if (o.files.valid && !i) { %}
	        <li class="meta"><img style="float:right;margin-top:-20px;" src="<?php Site::out_url('theme'); ?>/images/ajax-loader.gif"></li>
        {% } else { %}
            <li></li>
        {% } %}
       </ul>
    </li>
{% } %}
</script>

<script id="template-download" type="text/html">
{% for (var i=0, files=o.files, l=files.length, file=files[0]; i<l; file=files[++i]) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td></td>
            <td class="name">{%=file.name%}</td>
            <td class="size">{%=o.formatFileSize(file.size)%}</td>
            <td class="error" colspan="2"><span class="label important">Error</span> {%=fileUploadErrors[file.error] || file.error%}</td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}">{%=file.name%}</a>
            </td>
            <td class="size">{%=o.formatFileSize(file.size)%}</td>
            <td colspan="2"></td>
        {% } %}
        <td class="delete">
            <button class="btn danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">Delete</button>
            <input type="checkbox" name="delete" value="1">
        </td>
    </tr>
{% } %}
</script>