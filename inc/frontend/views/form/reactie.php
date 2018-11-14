<?php
?>

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
	<div class="tahlil-input-wrapper" id="tahlil-word-input">
		<label for="pmg_comment_content">Words</label>
		<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type">Cancel</a>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-photos-input">
		<label for="pmg_comment_content">Photos</label>
		<p>
			<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type">Cancel</a>
		</p>
		<p>
        	<input id="attachment" name="attachment" type="file" />
        </p>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-poetry-input">
		<label for="pmg_comment_content">Poetry</label>
		<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type">Cancel</a>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-music-input">
		<label for="pmg_comment_content">Music</label>
		<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type">Cancel</a>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-video-input">
		<label for="pmg_comment_content">Video</label>
		<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type">Cancel</a>
	</div>

	<div id="youtube-searchbox">
		<div class="search-videos">
	  		<input type="text" value="" placeholder="type a keyword" class="searchtext">
	  		<button type="button" class="searchbutton">Search</button>
	  	</div>
	    
	    <div class="youtube-loader"></div>
	  	<div class="count"></div>
	    <div class="snipp"></div>
	    <button class="btn btn-primary btn-sm nextPageButton" style="display: none; margin-bottom: 42px;">load more</button>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-quote-input">
		<label for="pmg_comment_content">Quote</label>
		<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type">Cancel</a>
	</div>

	
	<div id="selected_video_audio_wrapper">
		<p id="selected_video_audio_holder"></p>
	</div>

	<p class="comment-form-title">
		<label for="pmg_comment_title">Title</label>
    	<input type="text" aria-required="true" placeholder="Title..." name="pmg_comment_title" id="pmg_comment_title" />
	</p>

	<p class="comment-form-desc">
		<label for="pmg_comment_content">Content</label>
		<textarea class="pmg_comment_content" name="pmg_comment_content" placeholder="Your reaction here.."></textarea>
	</p>

	<input type="hidden" name="comment_type" value="reactie">
	<input type="hidden" id="pmg_comment_type" name="pmg_comment_type">
	<input type="hidden" id="selected_video_audio" name="selected_video_audio">
	<input type="hidden" id="selected_video_audio_title" name="selected_video_audio_title">
</div>

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
