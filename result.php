<?php
    $get_tweet_path = 'python3.6 ./get_tweet.py ' . htmlspecialchars($_GET['message']);
    $regex_path = 'python3.6 ./regex.py';
    $collection_path = 'python3.6 ./collection.py ' . htmlspecialchars($_GET['message']) . ' ' . htmlspecialchars($_GET['since_date']) . ' ' . htmlspecialchars($_GET['until_date']) . ' ' . htmlspecialchars($_GET['sort_by']) . ' ' . htmlspecialchars($_GET['sort_order']);
    
    exec($get_tweet_path);
    exec($regex_path);
    exec($collection_path, $outpara);
    
    $collection_id = split('-', $outpara[0]);
    // echo($outpara[0]);
    // echo($collection_path);
    // echo(date('Y/m/d'));
    $collection_url = "https://twitter.com/cs_hiroshima_u/timelines/" . $collection_id[1];

    // echo($collection_id[1]);
    // echo($collection_url);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>test page</title>
    </head>
<body>

<p>コレクションは<a href="<?php echo $collection_url;?>" target="_blank">こちら</a>です。</p>

<!-- <h2>タイムライン形式</h2> -->
<!-- コンテナ -->
<div id="tweet-collection-timeline"></div>

<!-- ライブラリの読み込み -->
<script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>

<script>
// コレクションを埋め込み表示するメソッドを実行 (タイムライン形式)
var $collection_id = "<?php echo $collection_id[1];?>";

twttr.widgets.createTimeline (
    {   // 第1引数: ウィジェットの種類
        sourceType: "collection",
        id: $collection_id,   // コレクションID
    },
    document.getElementById( "tweet-collection-timeline" ) ,    // 第2引数: コンテナの要素
    {   // 第3引数: パラメータ
      width: 500 ,    // 横幅
    }
) ;
</script>
</body>
</html>
