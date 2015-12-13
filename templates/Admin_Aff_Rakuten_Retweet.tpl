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
    var params = $("#form_search_api_parms").serializeArray();
    var post_data = [];
    for(index in params){
        post_data[params[index]["name"]] = params[index]["value"];
    }

    $.ajax({
        type: "POST",
        url: "/admin/AffRakutenRetweet/ajax_search_items/",
        data: post_data
    }).done(function(data){

        for(index in data['search_item_result']){
            img_tag = [];
            for(img_index in data['search_item_result'][index]['smallImageUrls']){
                img_url_splits = data['search_item_result'][index]['smallImageUrls'][img_index].split('?');
                img_tag.push("<img src=" + img_url_splits[0] + ">");
            }

            serach_item_result = {
                "itemName" : data['search_item_result'][index]['itemName'],
                "itemCaption" : data['search_item_result'][index]['itemCaption'],
                "images" : img_tag.join();
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
                <button type="button" class="btn btn-primary btn-block">商品検索</button>
            </div>
        </div>
    </div>

    <div id="search_item_result"></div>


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
    <div class="col-md-9">
        ${itemName}
    </div>
</div>
<div class="row">
    <div class="col-md-9">
        {{html images}}<!--imgタグつきで生成-->
    </div>
</div>
<div class="row">
    <div class="col-md-9">
        ${itemCaption}
    </div>
</div>

<div class="row">
    <div class="col-md-2">
        <button type="button" class="btn btn-primary btn-block">選択</button>
    </div>
</div>
</script>

</body>
</html>