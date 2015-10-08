<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>メッセージ確認</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<button type="button" class="btn btn-info">更新</button>
<br>
<br>
<a class="btn btn-default btn-xs" href="#" role="button">確認済みも表示</a>
<br>
<ul class="pagination">
    <li{if $pagenation.page <= 1} class="disabled"{/if}><a href="/admin/Checkmessage/index/{$pagenation.page - 1}/">&laquo;</a></li>
    {foreach from=$pagenation.page_buttons key=key item=item}
    <li{if $item == $pagenation.page} class="active"{/if}><a href="/admin/Checkmessage/index/{$item}/">{$item}</a></li>
    {/foreach}
    <li{if $pagenation.page >= $pagenation.max_page} class="disabled"{/if}><a href="/admin/Checkmessage/index/{$pagenation.page + 1}/">&raquo;</a></li>
</ul>

{if $mes_list}
<table class="table table-bordered">
    <thead>
        <tr>
            <th>チェック</th>
            <th>発生日時</th>
            <th>アカウント</th>
            <th>種別</th>
            <th>メッセージ</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$mes_list key=key item=item}
        <tr>
            <td><input type="checkbox" value="1"{if $item->check_flg} checked{/if}></td>
            <td>{$item->create_date}</td>
            <td>{$item->account_name}</td>
            <td>{$item->type}</td>
            <td>{$item->message1}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{else}
メッセージがありません<br>
{/if}

<button type="button" class="btn btn-success">確認</button>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
</body>
</html>