var nextPageToken, prevPageToken;
var firstPage=true;
var GAPI_KEY = 'AIzaSyAXH9REWSw5U1RqWDYyCQ3Uzf-3DcN0evs';

jQuery(document).ready(function($) {
  $('.searchbutton').click(function(e) {
    e.preventDefault();
    nextPageToken = '';
    prevPageToken = '';
    $('.snipp').html('');
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
    if (!searchText || searchText === '') {
      searchText = 'memories';
    }
    $('.youtube-loader').html("<div id=\"searching\"><b>Searching for "+searchText+"</b></div>");

    var request = gapi.client.youtube.search.list({
      part: 'snippet',
      q:searchText,
      maxResults:10,
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
      // $('.count').replaceWith("<div id=count><b>Found "+resultCount+" Results.</b></div>");
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

        $('.snipp').append(
          videoIframe + title
        );
      }
    } else {
      $('.youtube-loader').html("<div id=\"searching\"><b>no videos found for keyword: "+searchText+"</b></div>");
    }
  }

});