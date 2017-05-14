<?php
return array(

    //enable or disable api requests
    'enabled' => true,

    //fixme redo 0 is forever
    // enables caching of API results, leave 0 to disable caching
    'cache_minutes' => 60,

    // use api data to fill in title & description
    'set_data' => true,

    'video_existence_error' => "Не удалось найти видео на {type} с идентификатором: {id}",

    'youtube' => array(

        //check if video exists on youtube. works without api_key
        'video_existence_url' => 'https://www.youtube.com/oembed',

        //gets preview image of video in defined quality. works without api_key
        'preview_url' => 'https://img.youtube.com/vi/{id}/{quality}.jpg',

        //possible values: 0, 1, 2, 3, default, default, mqdefault, hqdefault, sddefault, maxresdefault
        'preview_quality' => 'maxresdefault',

        //https://developers.google.com/youtube/v3/docs/videos/list
        'api_url' => 'https://www.googleapis.com/youtube/v3/videos',

        'api_part' => 'snippet,statistics',

        //https://developers.google.com/youtube/v3/getting-started
        'api_key' => 'AIzaSyDWPTTGEANYwXwAk8QMg9bQTzzBatmhxbc',
    ),

    'vimeo' => array(

        //check if video exists on vimeo. works without api_key
        'video_existence_url' => 'https://vimeo.com/api/oembed.json',

        //gets preview image of video in defined quality. works without api_key
        //note: this is an image {id} not video {id} which is retrieved via video_existence_url
        'preview_url' => 'https://i.vimeocdn.com/video/{id}_{quality}.jpg',

        //possible values: 100, 200, 295, 640, 960, 1280
        'preview_quality' => '1280',

        //https://developer.vimeo.com/api/start
        'api_url' => 'https://api.vimeo.com/videos/',

        //leave empty to get everything
        'api_part' => 'name,description,duration, stats.plays, metadata.connections.comments.total, metadata.connections.likes.total',

        //https://developer.vimeo.com/apps/new -> Generate a new Access Token
        'api_key' => 'fdac2059a4a4dabba7e882089608a7e1',
    ),
);
