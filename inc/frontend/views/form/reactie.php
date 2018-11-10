<div id="comment-form-select-type">
	<label>Add your comment or reminder with:</label>
	<p>
    	<button class="btn btn-success choose-reminder button-active" data-type="words" id="tahlil-word">
   			Words
    	</button>
    	<button class="btn btn-success choose-reminder" data-type="photos" id="tahlil-photos">
   			Photos
    	</button>
    	<button class="btn btn-success choose-reminder" data-type="poetry" id="tahlil-poetry">
   			Poetry
    	</button>
    	<button class="btn btn-success choose-reminder" data-type="music" id="tahlil-music">
   			Music
    	</button>
    	<button class="btn btn-success choose-reminder" data-type="video" id="tahlil-video">
   			Video
    	</button>
    	<button class="btn btn-success choose-reminder" data-type="quote" id="tahlil-quote">
   			Quote
    	</button>
	</p>
</div>

<div id="second-reactie-step">
	<div class="tahlil-input-wrapper" id="tahlil-word-input" style="display: none;">
		<label for="pmg_comment_content">Words</label>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-photos-input" style="display: none;">
		<label for="pmg_comment_content">Photos</label>
		<input type="file" name="pmg_comment_photos[]">
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-poetry-input" style="display: none;">
		<label for="pmg_comment_content">Poetry</label>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-music-input" style="display: none;">
		<label for="pmg_comment_content">Music</label>
		<input type="file" name="pmg_comment_videos[]">
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-video-input" style="display: none;">
		<label for="pmg_comment_content">Video</label>
		<div id="buttons">
			<label>
				<input id="query" value='cats' type="text"/>
				<button id="search-button">
					Search
				</button>
			</label>
	    </div>
	    <div id="search-container"></div>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-quote-input" style="display: none;">
		<label for="pmg_comment_content">Quote</label>
	</div>

	<textarea class="pmg_comment_content" name="pmg_comment_content" placeholder="Type here.." style="display: none;"></textarea>
	<input type="hidden" id="pmg_comment_type" name="pmg_comment_type">
</div>

<!--Add buttons to initiate auth sequence and sign out-->
    <button id="authorize-button" style="display: none;">Authorize</button>
    <button id="signout-button" style="display: none;">Sign Out</button>

    <pre id="content-youtube"></pre>

<div style="margin-bottom: 20px;"></div>

<style type="text/css">
	/*#second-reactie-step {
		position: relative;
		height: 120px;
		padding-top: 40px;
	}
	.tahlil-input-wrapper {
		top: 0;
		left: 0;
		position: absolute;
		width: 100%;
	}*/
</style>

<script type="text/javascript">
	jQuery( document ).ready(function($) {
		$('.choose-reminder').on('click', function(e) {
			e.preventDefault();
			$('#pmg_comment_type').val($(this).attr('data-type'));
			$('.pmg_comment_content').fadeIn();
		})

		$('#tahlil-word').on('click', function(e) {
			$('#tahlil-photos-input').fadeOut();
			$('#tahlil-poetry-input').fadeOut();
			$('#tahlil-music-input').fadeOut();
			$('#tahlil-video-input').fadeOut();
			$('#tahlil-quote-input').fadeOut();
			$('#tahlil-word-input').fadeIn();
		});

		$('#tahlil-photos').on('click', function(e) {
			$('#tahlil-poetry-input').fadeOut();
			$('#tahlil-music-input').fadeOut();
			$('#tahlil-video-input').fadeOut();
			$('#tahlil-quote-input').fadeOut();
			$('#tahlil-word-input').fadeOut();
			$('#tahlil-photos-input').fadeIn();

			$('.pmg_comment_content').fadeOut();
		});

		$('#tahlil-poetry').on('click', function(e) {
			$('#tahlil-photos-input').fadeOut();
			$('#tahlil-music-input').fadeOut();
			$('#tahlil-video-input').fadeOut();
			$('#tahlil-quote-input').fadeOut();
			$('#tahlil-word-input').fadeOut();
			$('#tahlil-poetry-input').fadeIn();
		});

		$('#tahlil-music').on('click', function(e) {
			$('#tahlil-photos-input').fadeOut();
			$('#tahlil-poetry-input').fadeOut();
			$('#tahlil-video-input').fadeOut();
			$('#tahlil-quote-input').fadeOut();
			$('#tahlil-word-input').fadeOut();
			$('#tahlil-music-input').fadeIn();

			$('.pmg_comment_content').fadeOut();
		});

		$('#tahlil-video').on('click', function(e) {
			$('#tahlil-photos-input').fadeOut();
			$('#tahlil-poetry-input').fadeOut();
			$('#tahlil-music-input').fadeOut();
			$('#tahlil-quote-input').fadeOut();
			$('#tahlil-word-input').fadeOut();
			$('#tahlil-video-input').fadeIn();

			$('.pmg_comment_content').fadeOut();
		});

		$('#tahlil-quote').on('click', function(e) {
			$('#tahlil-photos-input').fadeOut();
			$('#tahlil-poetry-input').fadeOut();
			$('#tahlil-music-input').fadeOut();
			$('#tahlil-word-input').fadeOut();
			$('#tahlil-video-input').fadeOut();
			$('#tahlil-quote-input').fadeIn();
		});
	})
</script>

<script async defer src="https://apis.google.com/js/api.js"
  onload="this.onload=function(){};handleClientLoad()"
  onreadystatechange="if (this.readyState === 'complete') this.onload()">
</script>