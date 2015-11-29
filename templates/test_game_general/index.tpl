<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>{$site_info->site_name_mb}</title>
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
    <script type="text/javascript" src="js/site.min.js"></script>
  </head>
  <body style="background-color: #f1f2f6;">
    <div class="container documents">
    <h2 class="timeline-title">オレ的ゲーム情報タイムライン</h2>
    <div class="row">
        <div class="col-md-12">
            <div class="timeline">
                <dl>
                {foreach from=$feeds key=date item=feed_days}
                    <dt>{$date}</dt>
                    {foreach from=$feed_days key=key item=feed_data name=feed_loop}
                    {if ($smarty.foreach.feed_loop.iteration % 2) == 1}
                    <dd class="pos-right clearfix">
                    {else}
                    <dd class="pos-left clearfix">
                    {/if}
                        <div class="circ"></div>
                        <div class="time">{$feed_data->feed->date|date_format:"%H:%M"}</div>
                        <div class="events">
                            <div class="pull-left">
                            {if $feed_data->feed->image_url}
                                <img class="events-object img-rounded" src="{$feed_data->feed->image_url}">
                            {/if}
                            </div>
                            <div class="events-body">
                                <h4 class="events-heading">{$feed_data->feed->mb_name}</h4>
                                <p><a href="{$feed_data->link}" target="_blank">{$feed_data->feed->title}</a></p>
                            </div>
                        </div>
                    </dd>
                    {/foreach}
                {/foreach}

              </dl>
          </div>
      </div>
    </div>
    </div>

  </body>
</html>
