<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><!--{$site_info->site_name_mb}--></title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <!--<meta name="smartaddon-verification" content="936e8d43184bc47ef34e25e426c508fe" />-->
    <meta name="keywords" content="ゲーム,タイムライン,ゲーム情報,ゲーム情報タイムライン,ゲーム速報,FF14,エオルゼア,DQ10">
    <meta name="description" content="ゲーム情報、速報、面白ネタまでタイムライン形式で見やすくお届け。暇つぶしにどうぞ。">
    <!--<link rel="shortcut icon" href="favicon_16.ico"/>-->
    <!--<link rel="bookmark" href="favicon_16.ico"/>-->
    <!-- site css -->
    <link rel="stylesheet" href="/bootflat/css/site.min.css">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,800,700,400italic,600italic,700italic,800italic,300italic" rel="stylesheet" type="text/css">
    <!-- <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'> -->
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript" src="/bootflat/js/site.min.js"></script>
    <script type="text/javascript" src="/bootflat/js/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="/bootflat/js/jquery.tmpl.min.js"></script>
    <script type="text/javascript" src="/bootflat/js/jquery.bottom-1.0.js"></script>
<script type="text/javascript">
$(function() {
    $('html,body').animate({ scrollTop: 0 }, '1');
});

var last_data_date = '<!--{$last_feed_time}-->';
var read_more_count = 0;
var reading_flg = false;
$(window).on("scroll", function() {
    if(read_more_count >= 3){
        return false;
    }
    var scrollHeight = $(document).height();
    var scrollPosition = $(window).height() + $(window).scrollTop();
    if ((scrollHeight - scrollPosition) / scrollHeight <= 0.05 && reading_flg === false) {
        reading_flg = true;
        read_more_count++;
        read_more_feeds('<!--{$auth_str}-->');
    }
});
</script>
    <script type="text/javascript" src="/bootflat/js/site.js"></script>

  </head>
  <body style="background-color: #f1f2f6;">


    <div class="docs-header">

        <!--header-->
        <div class="topic">
            <div class="container">
                <div class="col-md-8">
                    <h2>ゲーム情報タイムライン</h2>
                    <h4>ゲーム情報速報、ゲーム記事を最速速報</h4>
                </div>
                <div class="col-md-4">
                    <div class="advertisement">

                    </div>
                </div>
            </div>

        </div>

    </div>


    <div class="container documents">

        <div class="row">
            <div class="col-md-3">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">サイドバー</h3>
                    </div>
                    <div class="panel-body">
                        content
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="btn-group">
                  <button type="button" class="btn btn-info" onClick="location.href = 'http://games.cocoon.jp/';">HOME</button>
                  <button type="button" class="btn btn-info" onClick="location.href = 'http://games.cocoon.jp/ff14/';">FF14</button>
                  <button type="button" class="btn btn-info" onClick="location.href = 'http://games.cocoon.jp/dq10/';">DQ10</button>
                </div>

                <div class="timeline">
                    <dl id="timeline_dl">
                    <!--{foreach from=$feeds key=date item=feed_days}-->
                        <dt><!--{$date}--></dt>
                        <!--{foreach from=$feed_days key=key item=feed_data name=feed_loop}-->
                        <!--{if ($smarty.foreach.feed_loop.iteration % 2) == 1}-->
                        <dd class="pos-right clearfix">
                        <!--{else}-->
                        <dd class="pos-left clearfix">
                        <!--{/if}-->
                            <div class="circ hidden-xs"></div>
                            <div class="time"><!--{$feed_data->feed->date|date_format:"%H:%M"}--></div>
                            <div class="events">
                                <div class="pull-left">
                                <!--{if $feed_data->feed->image_url}-->
                                    <img class="events-object img-rounded" src="<!--{$feed_data->feed->image_url}-->">
                                <!--{/if}-->
                                </div>
                                <div class="events-body">
                                    <h4 class="events-heading"><!--{$feed_data->feed->mb_name}--></h4>
                                    <p><a href="<!--{$feed_data->link}-->" target="_blank" onClick="access_feed('<!--{$auth_str}-->', '<!--{$feed_data->feed->feed_id}-->')"><!--{$feed_data->feed->title}--></a></p>
                                </div>
                            </div>
                        </dd>
                        <!--{/foreach}-->
                    <!--{/foreach}-->

                    </dl>
                </div>
            </div>

        </div>

    </div>

<script id="timeline_Template" type="text/x-jquery-tmpl">
    <dd class="pos-${way} clearfix">
        <div class="circ hidden-xs"></div>
        <div class="time">${time}</div>
        <div class="events">
            <div class="pull-left">
                {{html image}}
            </div>
            <div class="events-body">
                <h4 class="events-heading">${mb_name}</h4>
                <p><a href="${link}" target="_blank" onClick="access_feed('<!--{$auth_str}-->', '${feed_id}')">${content}</a></p>
            </div>
        </div>
    </dd>
</script>

  </body>
</html>
