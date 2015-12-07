<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>記事宣伝</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<form method="post" action="">

    <input type="hidden" name="mode" value="article_tweet">
    {html_options name=site_id options=$site_info class=controls}

    <div class="control-group">
        <label class="control-label" for="article_id">article_id</label>
        <div class="controls">
            <input type="text" name="article_id" class="input-xlarge" id="article_id">
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn">ツイート</button>
    </div>
</form>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
</body>
</html>