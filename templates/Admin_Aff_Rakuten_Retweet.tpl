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
    <script type="text/javascript" src="/bootflat/js/jquery-1.10.1.min.js"></script>
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
-->
</style>

<script type="text/javascript">

// $("#api_select").change(function()
// {
//     $.ajax({
//         type: "POST",
//         url: "/admin/AffRakutenRetweet/ajax_api_select/",
//         data: {
//             "api_select_id": $("#api_select").val()
//         }
//     }).done(function(data){

//         for(index in data['search_api_parms_list']){
//             parms_html = {
//                 "parm_name" : data['search_api_parms_list'][index]
//             };
//             $( "#api_parms" ).tmpl(parms_html).appendTo('#search_api_parms');
//         }
//     });
// });

function api_change()
{
//var select_val = $("#api_select > div > .selecter-options > .selecter-item selected").html();
//var select_val = document.forms.form1.api_select_id.selectIndex;
//alert($("#api_select_id").val());
    $("#search_api_parms").empty();
    $("#search_item_submit").hide();
    $.ajax({
        type: "POST",
        url: "/admin/AffRakutenRetweet/ajax_api_select/",
        data: {
            "api_select_id": $("#api_select_id").val()
        }
    }).done(function(data){

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
    $("#search_item_result").empty();
    $("#search_item_result").addClass("loadingMsg");
    var params = $("#form_search_api_parms").serializeArray();
    var post_data = {};
    for(index in params){
//alert(params[index]["name"].length)
//alert(params[index]["value"].length)
        if(params[index]["value"].length){
            post_data[params[index]["name"]] = params[index]["value"];
        }
    }
//alert(post_data);
//return;

    $.ajax({
        type: "POST",
        url: "/admin/AffRakutenRetweet/ajax_search_items/",
        data: post_data
    }).done(function(data){
        $("#search_item_result").removeClass("loadingMsg");
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
                "small_img_tag" : small_img_tag.join(""),
                "middle_img_tag" : middle_img_tag.join("")
            };
            $( "#serach_item_result_parts" ).tmpl(serach_item_result).appendTo('#search_item_result');
        }
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
            <!--{html_options id=aff_rakuten_account name=aff_rakuten_account_id options=$rakuten_account}-->
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

    <div id="item_result_pager">
        <div class="row example-pagination">
            <div class="col-md-12">
                <ul class="pager">
                    <li class="previous disabled"><button type="button" onclick="item_search();">前ページ</button></li>
                    <li class="next"><button type="button" onclick="item_search();">次ページ</button></li>
                </ul>
            </div>
        </div>
    </div>


</div>
</div>
</div>

<script id="api_parms" type="text/x-jquery-tmpl">
<div class="row">
    <div class="col-md-6">
        <input type="text" name="${parm_name}" class="form-control" value="" placeholder="${parm_name}">
    </div>
</div>
</script>

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
          <div class="row">
            {{html small_img_tag}}
          </div>
          <div class="row">
            {{html middle_img_tag}}
          </div>
          <div class="row">
            <div class="col-md-2">
              <button type="button" class="btn btn-primary btn-block" onclick="item_select();">選択</button>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="item_info_${index}">
          <div class="row">商品名：${itemName}</div>
          <div class="row">商品説明：${itemCaption}</div>
          <div class="row">
            <div class="col-md-2">
              <button type="button" class="btn btn-primary btn-block" onclick="item_select();">選択</button>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="shop_info_${index}">
          <div class="row">ショップ名：${shopName}</div>
          <div class="row">価格${itemPrice}円</div>
          <div class="row">アフェリエイト率：${affiliateRate}%</div>
          <div class="row">
            <div class="col-md-2">
              <button type="button" class="btn btn-primary btn-block" onclick="item_select();">選択</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</script>

</body>
</html>