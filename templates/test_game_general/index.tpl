<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><!--{$site_info->site_name_mb}--></title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <!--<meta name="smartaddon-verification" content="936e8d43184bc47ef34e25e426c508fe" />-->
    <meta name="keywords" content="Flat UI Design, UI design, UI, user interface, web interface design, user interface design, Flat web design, Bootstrap, Bootflat, Flat UI colors, colors">
    <meta name="description" content="The complete style of the Bootflat Framework.">
    <!--<link rel="shortcut icon" href="favicon_16.ico"/>-->
    <!--<link rel="bookmark" href="favicon_16.ico"/>-->
    <!-- site css -->
    <link rel="stylesheet" href="bootflat/css/site.min.css">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,800,700,400italic,600italic,700italic,800italic,300italic" rel="stylesheet" type="text/css">
    <!-- <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'> -->
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript" src="bootflat/js/site.min.js"></script>
    <script type="text/javascript" src="bootflat/js/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="bootflat/js/jquery.tmpl.min.js"></script>
    <script type="text/javascript" src="bootflat/js/jquery.bottom-1.0.js"></script>
<script type="text/javascript">
var last_data_date = '<!--{$last_feed_time}-->';

/*
function next_feeds()
{
    var feed_data = read_more_feeds('index.php', '<!--{$auth_str}-->', last_data_date);
    //read_more_feeds('index.php', '<!--{$auth_str}-->', last_data_date).done(function(feed_data){
        for(date in feed_data){
            $("#timeline").append('<dt>' + date + '</dt>');
            for(key in feed_data[date]){
                data = {
                    "way" : (key % 2) == 0 ? "right" : "left",
                    "date" : feed_data[date][key]['feed']['date'],
                    "image" : "<img class=\"events-object img-rounded\" src=\"" + feed_data[date][key]['feed']['image_url'] + "\">",
                    "mb_name" : feed_data[date][key]['feed']['mb_name'],
                    "link" : feed_data[date][key]['link'],
                    "content" : feed_data[date][key]['feed']['title']
                };
                $.tmpl( $( "#timeline_Template" ), data ).appendTo( "#timeline" );
                last_data_date = feed_data[date][key]['feed']['date'];
            }
        }
    //});

}
*/

/*
$(document).ready(function() {
    // オプションのproximityの値には、bottom.jsを発生する位置を指定します。
    //$("#timeline").bottom({proximity: 0.05});
    $("#timeline").bind("bottom", function() {
        var obj = $(this);

        //「loading」がfalseの時に実行する
        if (!obj.data("loading")) {

            //「loading」をtrueにする
            obj.data("loading", true);

            //「Loading...」というテキストを表示
            $('#timeline dl').append('<dd>Loading...</dd>');

            feed_data = read_more_feeds('index.php', '<!--{$auth_str}-->', last_data_date);
            //「Loading...」テキストを消す
            $('#timeline dl dd:last').remove();

            for(date in feed_data){
                $("#timeline").append('<dt>' + date + '</dt>');
                for(key in feed_data[date]){
                    data = {
                        "way" : (key % 2) == 0 ? "right" : "left",
                        "date" : feed_data[date][key]['feed']['date'],
                        "image" : "<img class=\"events-object img-rounded\" src=\"" + feed_data[date][key]['feed']['image_url'] + "\">",
                        "mb_name" : feed_data[date][key]['feed']['mb_name'],
                        "link" : feed_data[date][key]['link'],
                        "content" : feed_data[date][key]['feed']['title']
                    };
                    $.tmpl( $( "#timeline_Template" ), data ).appendTo( "#timeline" );
                    last_data_date = feed_data[date][key]['feed']['date'];
                }
            }
            //処理が完了したら「Loading...」をfalseにする
            obj.data("loading", false);

        }
    });
    $('html,body').animate({ scrollTop: 0 }, '1');
});
*/
</script>
    <script type="text/javascript" src="bootflat/js/site.js"></script>

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
                                    <p><a href="<!--{$feed_data->link}-->" target="_blank"><!--{$feed_data->feed->title}--></a></p>
                                </div>
                            </div>
                        </dd>
                        <!--{/foreach}-->
                    <!--{/foreach}-->

                    </dl>
                </div>
            </div>

        </div>

        <button type="button" class="btn btn-success btn-block" onclick="read_more_feeds('<!--{$auth_str}-->');">next</button>
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
                <p><a href="${link}" target="_blank">${content}</a></p>
            </div>
        </div>
    </dd>
</script>

  </body>
</html>
