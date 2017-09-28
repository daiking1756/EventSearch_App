<?php
    $get_tweet_path = 'python3.6 ./get_tweet.py ' . htmlspecialchars($_GET['message']);
    $regex_path = 'python3.6 ./regex.py';
    $collection_path = 'python3.6 ./collection.py ' . htmlspecialchars($_GET['message']);
    $col_name2id_path =
      'python3.6 ./col_name2id.py ' . htmlspecialchars($_GET['message']);
    

    exec($get_tweet_path);
    exec($regex_path);
    exec($collection_path);
    exec($col_name2id_path, $outpara);
    
    $collection_id = split('-', $outpara[0]);

    $collection_url = "https://twitter.com/arayutw/timelines/" . $collection_id[1];

    echo($collection_url);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>test page</title>
    </head>
<body>

<p>コレクションは<a href="<?php echo $collection_url;?>" target="_blank">こちら</a>です。</p>

<h2>タイムライン形式</h2>
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
      width: 300 ,    // 横幅
    }
) ;
</script>
</body>
</html>