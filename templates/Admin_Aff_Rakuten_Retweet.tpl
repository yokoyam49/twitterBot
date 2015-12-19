<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>楽天アフェリエイト リツイート予約</title>

    <link rel="stylesheet" href="/bootflat/css/site.min.css">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,800,700,400italic,600italic,700italic,800italic,300italic" rel="stylesheet" type="text/css">
    <!-- <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'> -->
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript" src="/bootflat/js/site.min.js"></script>
    <script type="text/javascript" src="/bootflat/js/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="/bootflat/js/jquery.tmpl.min.js"></script>
<style type="text/css">
<!--
.loadingMsg{
    text-align:center;
    padding-top:100px;
    width:100px;
    background-image:url("/img/loading.gif");
    background-position: center top;
    background-repeat: no-repeat;
}

#lean_overlay  {
    position: fixed; z-index:100;
    top: 0px;
    left: 0px;
    height: 100%;
    width: 100%;
    background: #000;
    display: none;
}

#modal-window {
    background-color: #FFFFFF;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.7);
    display: none;
    padding: 30px;
    width: 500px;
    height: 300px;
}

.modal_close {
    position: absolute;
    top: 12px;
    right: 12px;
    display: block;
    /* widthとheightは閉じるボタンの画像の幅を指定 */
    width: 28px;
    height: 28px;
    /* 閉じるボタンの画像は自分で用意 */
    background: url('../images/close-popup.png') no-repeat;
    z-index: 2;
}
-->
</style>

<script type="text/javascript">
var max_page = 0;
var now_page = 0;

$(window).load(function(){
    api_change();
});

function api_change()
{
//var select_val = $("#api_select > div > .selecter-options > .selecter-item selected").html();
//var select_val = document.forms.form1.api_select_id.selectIndex;
//alert($("#api_select_id").val());
    $("#search_api_parms").empty();
    $("#search_item_submit").hide();
    $("#search_api_parms").addClass("loadingMsg");
    $.ajax({
        type: "POST",
        url: "/admin/AffRakutenRetweet/ajax_api_select/",
        data: {
            "api_select_id": $("#api_select_id").val()
        }
    }).done(function(data){
        $("#search_api_parms").removeClass("loadingMsg");
        for(index in data['search_api_parms_list']){
            parms_html = {
                "parm_name" : data['search_api_parms_list'][index]
            };
            $( "#api_parms" ).tmpl(parms_html).appendTo('#search_api_parms');
        }
        $("#search_item_submit").show();
    });
}

function item_search()
{
    $("#item_result_pager").hide();
    $("#search_item_result").empty();
    $("#search_item_result").addClass("loadingMsg");
    var params = $("#form_search_api_parms").serializeArray();
    var post_data = {};
    for(index in params){
        if(params[index]["value"].length){
            post_data[params[index]["name"]] = params[index]["value"];
        }
    }

    $.ajax({
        type: "POST",
        url: "/admin/AffRakutenRetweet/ajax_search_items/",
        data: post_data
    }).done(function(data){
        $("#search_item_result").removeClass("loadingMsg");
        max_page = data['search_item_result_max_page'];
        now_page = data['search_item_result_now_page'];
        show_item_result(data);
        $("#item_result_pager").show();
        pager_cont();
    });
}

function next_page()
{
    if(now_page < max_page){
        move_pages(now_page + 1);
    }
}
function pre_page()
{
    if(now_page > 1){
        move_pages(now_page - 1);
    }
}

function move_pages(page)
{
    $("#item_result_pager").hide();
    $("#search_item_result").empty();
    $("#search_item_result").addClass("loadingMsg");
    $.ajax({
        type: "POST",
        url: "/admin/AffRakutenRetweet/ajax_move_pages/",
        data: {
            'page' : page
        }
    }).done(function(data){
        $("#search_item_result").removeClass("loadingMsg");
        max_page = data['search_item_result_max_page'];
        now_page = data['search_item_result_now_page'];
        show_item_result(data);
        $("#item_result_pager").show();
        pager_cont();
    });
}

function pager_cont()
{
    if(now_page >= max_page){
        $("#result-next").addClass('disabled');
    }else{
        $("#result-next").removeClass('disabled');
    }
    if(now_page <= 1){
        $("#result-pre").addClass('disabled');
    }else{
        $("#result-pre").removeClass('disabled');
    }
}

function show_item_result(data)
{
    for(index in data['search_item_result']){
        small_img_tag = [];
        wi = Math.floor(12 / data['search_item_result'][index]['smallImageUrls'].length);
        if(wi > 4){wi = 4};
        for(img_index in data['search_item_result'][index]['smallImageUrls']){
            img_url_splits = data['search_item_result'][index]['smallImageUrls'][img_index]['imageUrl'].split('?');
            small_img_tag.push("<div class=\"col-md-" + wi + "\"><img src=" + img_url_splits[0] + " class=\"img-responsive\"></div>");
        }
        middle_img_tag = [];
        wi = Math.floor(12 / data['search_item_result'][index]['mediumImageUrls'].length);
        if(wi > 4){wi = 4};
        for(img_index in data['search_item_result'][index]['mediumImageUrls']){
            img_url_splits = data['search_item_result'][index]['mediumImageUrls'][img_index]['imageUrl'].split('?');
            middle_img_tag.push("<div class=\"col-md-" + wi + "\"><img src=" + img_url_splits[0] + " class=\"img-responsive\"></div>");
        }

        serach_item_result = {
            "index" : index,
            "itemName" : data['search_item_result'][index]['itemName'],
            "itemCaption" : data['search_item_result'][index]['itemCaption'],
            "shopName" : data['search_item_result'][index]['shopName'],
            "itemPrice" : data['search_item_result'][index]['itemPrice'],
            "affiliateRate" : data['search_item_result'][index]['affiliateRate'],
            "affiliateUrl" : data['search_item_result'][index]['affiliateUrl'],
            "small_img_tag" : small_img_tag.join(""),
            "middle_img_tag" : middle_img_tag.join("")
        };
        $( "#serach_item_result_parts" ).tmpl(serach_item_result).appendTo('#search_item_result');
    }
}

function item_select(item_index)
{
    $("#tweet-modal-content").empty();
    $("#tweet-modal-content").addClass("loadingMsg");
    $.ajax({
        type: "POST",
        url: "/admin/AffRakutenRetweet/ajax_item_select/",
        data: {
            'item_index' : item_index
        }
    }).done(function(data){
        $("#tweet-modal-content").removeClass("loadingMsg");
        wi = Math.floor(12 / data['search_item_result'][item_index]['mediumImageUrls'].length);
        if(wi > 4){wi = 4};
        select_item_data = {
            "item_index" : item_index,
            "itemName" : data['search_item_result'][item_index]['itemName'],
            "itemCaption" : data['search_item_result'][item_index]['itemCaption'],
            "shopName" : data['search_item_result'][item_index]['shopName'],
            "itemPrice" : data['search_item_result'][item_index]['itemPrice'],
            "affiliateRate" : data['search_item_result'][item_index]['affiliateRate'],
            "affiliateUrl" : data['search_item_result'][item_index]['affiliateUrl'],
            "mediumImageUrls" : data['search_item_result'][item_index]['mediumImageUrls'],
            "wi" : wi,
        };
        $( "#tweet-modal_content" ).tmpl(select_item_data).appendTo('#tweet-modal-content');
        // $(function () {
        //     $('#datetimepicker1').datetimepicker({
        //         format : 'yyyy/MM/dd hh:mm:ss',
        //         pickTime : false
        //     }).data('datetimepicker');
        // });
    });
}

function item_tweet(item_index)
{
    var params = $("#form-retweet-data").serializeArray();
    var post_data = {};
    for(index in params){
        if(params[index]["value"].length){
            post_data[params[index]["name"]] = params[index]["value"];
        }
    }
    post_data['select_item_index'] = item_index;
    post_data['aff_rakuten_account_id'] = $("#aff_rakuten_account_id").val();
    $.ajax({
        type: "POST",
        url: "/admin/AffRakutenRetweet/ajax_reserve_retweet/",
        data: post_data
    }).done(function(data){

    });
}

</script>

</head>
<body>

<div class="container documents">
<div class="row">
<div class="col-md-9">



    <input type="hidden" name="mode" value="">
    <div class="row">
        <div class="col-md-3">
            <!--{html_options id=aff_rakuten_account_id name=aff_rakuten_account_id options=$rakuten_account}-->
        </div>
    </div>
    <div class="row"><div id="reserve_datetime"></div></div>
    <div class="row">
        <div class="col-md-3" id="api_select">
            <!--{html_options id=api_select_id name=api_select_id options=$api_select onChange="api_change();"}-->
        </div>
    </div>

    <form id="form_search_api_parms" name="form_search_api_parms">
        <div id="search_api_parms"></div>
    </form>

    <div id="search_item_submit" style="display: none;">
        <div class="row">
            <div class="col-md-2">
                <button type="button" class="btn btn-primary btn-block" onclick="item_search();">商品検索</button>
            </div>
        </div>
    </div>

    <div id="search_item_result"></div>

    <div id="item_result_pager" style="display: none;">
        <div class="col-md-9">
            <ul class="pager">
                <li id="result-pre" class="previous" onclick="pre_page();"><a href="javascript:void(0)">前ページ</a></li>
                <li id="result-next" class="next" onclick="next_page();"><a href="javascript:void(0)">次ページ</a></li>
            </ul>
        </div>
    </div>


</div>
</div>
</div>

<!--モーダル-->
<div class="modal" id="tweet-modal" tabindex="-1">
<div class="modal-dialog">
<div id="tweet-modal-content" class="modal-content">

</div>
</div>
</div>

<!--検索パラメーター入力欄-->
<script id="api_parms" type="text/x-jquery-tmpl">
<div class="row">
    <div class="col-md-6">
        <input type="text" name="${parm_name}" class="form-control" value="" placeholder="${parm_name}">
    </div>
</div>
</script>

<!--検索結果-->
<script id="serach_item_result_parts" type="text/x-jquery-tmpl">
<div class="row">
  <div class="col-md-12">
    <div class="panel">
      <ul id="item_result_${index}" class="nav nav-tabs nav-justified">
        <li class="active"><a href="#item_img_${index}" data-toggle="tab">画像</a></li>
        <li><a href="#item_info_${index}" data-toggle="tab">商品説明</a></li>
        <li><a href="#shop_info_${index}" data-toggle="tab">ショップ情報</a></li>
      </ul>
      <div id="myTabContent_${index}" class="tab-content">
        <div class="tab-pane fade active in" id="item_img_${index}">
          <!--
          <div class="row">
            {{html small_img_tag}}
          </div>
          -->
          <div class="row">
            {{html middle_img_tag}}
          </div>
          <div class="row">
            <div class="col-md-2">
              <button class="btn btn-primary" data-toggle="modal" data-target="#tweet-modal" onclick="item_select(${index});">この商品を選択</button>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="item_info_${index}">
        <div class="col-md-12">
          <div class="row">商品名：${itemName}</div>
          <div class="row">商品説明：${itemCaption}</div>
          <div class="row">リンク：<a href="${affiliateUrl}" target="_blank">${affiliateUrl}</a></div>
          <div class="row">
            <div class="col-md-2">
              <button class="btn btn-primary" data-toggle="modal" data-target="#tweet-modal" onclick="item_select(${index});">この商品を選択</button>
            </div>
          </div>
        </div>
        </div>
        <div class="tab-pane fade" id="shop_info_${index}">
        <div class="col-md-12">
          <div class="row">ショップ名：${shopName}</div>
          <div class="row">価格${itemPrice}円</div>
          <div class="row">アフェリエイト率：${affiliateRate}%</div>
          <div class="row">
            <div class="col-md-2">
              <button class="btn btn-primary" data-toggle="modal" data-target="#tweet-modal" onclick="item_select(${index});">この商品を選択</button>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
</script>

<!--モーダルテンプレート-->
<script id="tweet-modal_content" type="text/x-jquery-tmpl">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">ツイート＆リツイート予約</h4>
  </div>

  <div class="modal-body">
    <div class="panel">
      <ul id="item_retweet_infopanel" class="nav nav-tabs nav-justified">
        <li class="active"><a href="#retweet_content" data-toggle="tab">ツイート内容</a></li>
        <li><a href="#retweet_item_info" data-toggle="tab">商品情報</a></li>
      </ul>
      <div id="TabContent_itemretweet" class="tab-content">
        <div class="tab-pane fade active in" id="retweet_content">
        <form id="form-retweet-data" name="form-retweet-data">
          <input type="hidden" name="affiliateUrl" value="${affiliateUrl}">
          <div class="row">
            {{each mediumImageUrls}}
            <div class="col-md-${wi}">
              <input type="checkbox" name="select_img[${$index}]" value="${imageUrl}" />この画像を選択
            </div>
            {{/each}}
          </div>
          <div class="row">
            {{each mediumImageUrls}}
            <div class="col-md-${wi}"><img src="${imageUrl}" class="img-responsive"></div>
            {{/each}}
          </div>
          <div class="row">
            <div class="col-md-12">
              コメント：
              <textarea name="comment" class="form-control" rows="3"></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
                リツイート予約時間：
                <div class="input-append date" id="datetimepicker1">
                    <input name="retweet_time" type='text' ></input>
                    <span class="add-on">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
          </div>
        </form>
        </div>
        <div class="tab-pane fade" id="retweet_item_info">
          <div class="row"><div class="col-md-12">商品名：${itemName}</div></div>
          <div class="row"><div class="col-md-12">商品説明：${itemCaption}</div></div>
          <div class="row"><div class="col-md-12">リンク：<a href="${affiliateUrl}" target="_blank">${affiliateUrl}</a></div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-2">
        <button class="btn btn-primary" onclick="item_tweet(${item_index});">ツイート</button>
      </div>
    </div>
  </div>
</script>

</body>
</html>