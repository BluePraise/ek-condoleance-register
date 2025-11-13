<?php
?>

<div id="comment-form-select-type">
	<label style="text-transform: uppercase; font-size: 16px; font-weight: bold;">Voeg je reactie of herinnering toe met:</label>
	<p>
    	<button class="btn gem-button btn-success choose-reminder button-active" data-type="words" id="tahlil-word">
   			Tekst
    	</button>
    	<button class="btn gem-button btn-success choose-reminder" data-type="photos" id="tahlil-photos">
   			Foto
    	</button>
    	<button class="btn gem-button btn-success choose-reminder" data-type="music" id="tahlil-music">
   			Muziek
    	</button>
    	<button class="btn gem-button btn-success choose-reminder" data-type="video" id="tahlil-video">
   			Een Video
    	</button>
	</p>
</div>

<div id="second-reactie-step">
	<div class="tahlil-input-wrapper" id="tahlil-word-input">
		<label for="pmg_comment_content" style="text-transform: uppercase; font-size: 16px; font-weight: bold;">Wijs enkele woorden toe:</label>
		<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type" style="margin-top: -20px;">Annuleren</a>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-photos-input">
		<label for="pmg_comment_content" style="text-transform: uppercase; font-size: 16px; font-weight: bold;">Wijs een foto toe:</label>
		<p>
			<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type" style="margin-top: -10px;">Annuleren</a>
		</p>
		<p>
        	<input id="attachment" name="attachment" type="file" />
        </p>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-music-input">
		<label for="pmg_comment_content" style="text-transform: uppercase; font-size: 16px; font-weight: bold;">Voeg muziek toe:</label>
		<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type" style="margin-top: -10px;">Annuleren</a>
	</div>

	<div class="tahlil-input-wrapper" id="tahlil-video-input">
		<label for="pmg_comment_content" style="text-transform: uppercase; font-size: 16px; font-weight: bold;">Voeg video toe:</label>
		<a href="#" class="btn btn-sm btn-text btn-link btn-cancel-choose-type" style="margin-top: -10px;">Annuleren</a>
	</div>

	<div id="youtube-searchbox">
		<div class="search-videos">
	  		<input type="text" value="" placeholder="Zoek video of muziek..." class="searchtext">
	  		<button class="gem-button searchbutton" style="margin: 0 0 0 2px;">Zoeken</button>
	  	</div>
	    
	    <div class="youtube-loader"></div>
	  	<div class="count"></div>
	    <div class="snipp"></div>
	    <button class="btn btn-primary btn-sm nextPageButton gem-button" style="display: none; margin: 0 0 40px 8px;">Meer laden</button>
	</div>

	
	<div id="selected_video_audio_wrapper">
		<p id="selected_video_audio_holder"></p>
	</div>

	<p class="comment-form-title">
		<!--label for="pmg_comment_title">Onderwerpen</label-->
    	<input type="text" aria-required="true" placeholder="Type hier je onderwerp..." name="pmg_comment_title" id="pmg_comment_title" style="width: 100%;" />
	</p>

	<p class="comment-form-desc">
		<!--label for="pmg_comment_content">Inhoud</label-->
		<textarea class="pmg_comment_content" name="pmg_comment_content" placeholder="Schrijf je bericht hier..."></textarea>
	</p>

	<input type="hidden" name="comment_type" value="reactie">
	<input type="hidden" id="pmg_comment_type" name="pmg_comment_type">
	<input type="hidden" id="selected_video_audio" name="selected_video_audio">
	<input type="hidden" id="selected_video_audio_title" name="selected_video_audio_title">
</div>

<div style="margin-bottom: 20px;"></div>