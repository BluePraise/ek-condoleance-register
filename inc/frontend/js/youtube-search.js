var nextPageToken, prevPageToken;
var firstPage = true;
var GAPI_KEY = window.tahlil_youtube_api_key;
var videoSelected = false;
var searchbuttonType = '';

jQuery(document).ready(function($) {
  /** 
    * UI STUFFS
    */
  $('.choose-reminder').on('click', function(e) {
    e.preventDefault();
    $('#pmg_comment_type').val($(this).attr('data-type'));
    $('.pmg_comment_content').fadeIn();
    $('.comment-form').addClass('reactie_selected_type');
  })

  /**
   *  Fix issue when searching videos then user hit enter button 
   */
  $('#attachmentForm').keydown((e) => {
    if (e.keyCode === 13) {
      e.preventDefault()
      return false
    }
  })

  $('.btn-cancel-choose-type').click(function(e) {
    e.preventDefault();
    $('.comment-form').removeClass('reactie_selected_type');
    $('.comment-form').removeClass('video-selected');

    $('.comment-form').removeClass('reactie_selected_type_word');
    $('.comment-form').removeClass('reactie_selected_type_photos');
    $('.comment-form').removeClass('reactie_selected_type_poetry');
    $('.comment-form').removeClass('reactie_selected_type_music');
    $('.comment-form').removeClass('reactie_selected_type_video');
    $('.comment-form').removeClass('reactie_selected_type_quote');

    $('#selected_video_audio_holder').html('');
  })

  $('#tahlil-word').on('click', function(e) {
    $('.comment-form').toggleClass('reactie_selected_type_word');
  });

  $('#tahlil-photos').on('click', function(e) {
    $('.comment-form').toggleClass('reactie_selected_type_photos');
  });

  $('#tahlil-poetry').on('click', function(e) {
    $('.comment-form').toggleClass('reactie_selected_type_poetry');
  });

  $('#tahlil-music').on('click', function(e) {
    $('.comment-form').toggleClass('reactie_selected_type_music');
  });

  $('#tahlil-video').on('click', function(e) {
    $('.comment-form').toggleClass('reactie_selected_type_video');
  });

  $('#tahlil-quote').on('click', function(e) {
    $('.comment-form').toggleClass('reactie_selected_type_quote');
  });


  /**
   * ensure the video selected
   */
  $('#submit').click((e) => {
    let type = $('#pmg_comment_type[name="pmg_comment_type"]').val()
    let selectedAudioVideo = $('#selected_video_audio[name="selected_video_audio"]').val()
    if ( type === 'video' || type === 'music') {
      if (selectedAudioVideo === '' || !selectedAudioVideo) {
        e.preventDefault()
        $('.search-videos').append('<div class="error">Select video/music</div>')
      } else {
        $('#attachmentForm').submit()
      }
    }
  })

  /**
   * Youtube stuffs
   */
  $('.searchbutton').click(function(e) {
    e.preventDefault();
    nextPageToken = '';
    prevPageToken = '';
    $('.snipp').html('');
    searchbuttonType = $(this).attr('data-type');
    gapi.client.load('youtube', 'v3', onYouTubeApiLoad);
  });
    
  $('.nextPageButton').click(function(e) {
    e.preventDefault();
    searchYouTubeApi(nextPageToken);
  });
    
  $('.prevPageButton').click(function(e) {
    e.preventDefault();
    searchYouTubeApi(prevPageToken);
  });

  /**
   * passing token to each request
   */
  function onYouTubeApiLoad() {
    gapi.client.setApiKey(GAPI_KEY);
    searchYouTubeApi();
  }

  /**
   * search youtube api
   */
  function searchYouTubeApi(PageToken)
  {
    var searchText = $('.searchtext').val();
    if (searchText == '') {
      searchText = 'memories';
    }
    $('.youtube-loader').html("<div id=\"searching\"><b>Searching for "+searchText+"</b></div>");

    var request = gapi.client.youtube.search.list({
      part: 'snippet',
      q:searchText,
      maxResults:15,
      pageToken:PageToken,
      type: 'video',
      videoEmbeddable: true
    });

    request.execute(onSearchResponse);
  }

  /**
   * handle response from api
   */
  function onSearchResponse(response) 
  {
    var resultCount = response.pageInfo.totalResults;
    if (resultCount > 0) {
      $('.youtube-loader').html("");
      $('.nextPageButton').fadeIn();
      
      nextPageToken = response.nextPageToken;
      prevPageToken = response.prevPageToken;

      for (var i=0; i<response.items.length; i++)
      {
        var title = '<div class="video-title">' + response.items[i].snippet.title + '</div>';
        var thumbnails_default = response.items[i].snippet.thumbnails.default.url;
        var thumbnails_medium = response.items[i].snippet.thumbnails.medium.url;
        var thumbnails_high = response.items[i].snippet.thumbnails.high.url;
        var videoID = response.items[i].id.videoId;
        var videoIframe = '<iframe width="240" height="auto" src="https://www.youtube.com/embed/'+videoID+'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        var buttonSelectVideo = '<a href="#" id="btn-select-video-'+videoID+'" class="btn-select-video btn-sm btn-info" data-video="'+videoID+'" data-title="'+response.items[i].snippet.title+'">Select this video</a>';

        $('.snipp').append('<div class="search-video-item">'+videoIframe+title+buttonSelectVideo+'</div>');

        $('#btn-select-video-'+videoID).click(function(e) {
          e.preventDefault();
          $('#selected_video_audio').val($(this).attr('data-video'));
          $('#selected_video_audio_title').val($(this).attr('data-title'));
          $('.comment-form').addClass('video-selected');
          $('#selected_video_audio_holder').html('<div><iframe width="300" height="200" src="https://www.youtube.com/embed/'+$('#selected_video_audio').val()+'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><div class="selected_video_title">'+$('#selected_video_audio_title').val()+'</div></div>');
          videoSelected = true;
        });
      }
    } else {
      $('.youtube-loader').html("<div id=\"searching\"><b>no videos found</b></div>");
    }
  }

});