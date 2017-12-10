<?php
    $get_tweet_path = 'python3.6 ./get_tweet.py ' . htmlspecialchars($_GET['message']);
    $regex_path = 'python3.6 ./regex.py';
    $collection_path = 'python3.6 ./collection.py ' . htmlspecialchars($_GET['message']) . ' ' . htmlspecialchars($_GET['since_date']) . ' ' . htmlspecialchars($_GET['until_date']) . ' ' . htmlspecialchars($_GET['sort_by']) . ' ' . htmlspecialchars($_GET['sort_order']);
    $col_name2id_path = 'python3.6 ./col_name2id.py ' . htmlspecialchars($_GET['message']);
    

    exec($col_name2id_path, $outpara);
    // echo('col_name2id.py finish!<br>');

    exec($get_tweet_path . " > /dev/null &");
    exec($regex_path . " > /dev/null &");
    exec($collection_path . " > /dev/null &");
    
    $collection_id = split('-', $outpara[0]);
    // echo($outpara[0] . '<br>');
    // echo($collection_path);
    // echo(date('Y/m/d'));
    $collection_url = "https://twitter.com/cs_hiroshima_u/timelines/" . $collection_id[1];

    // echo($collection_id[1]);
    // echo($collection_url . '<br>');
?>

<!DOCTYPE html>
<html>
    <head>
        <title>test page</title>
        <script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>

        <script type="text/javascript">
        function show_collection(){
            // コレクションを埋め込み表示するメソッドを実行 (タイムライン形式)
            
            // DOMを削除する処理 
            // var element = document.getElementById("tweet-collection-timeline");
            // element.parentNode.removeChild(element);
            
            // var new_timeline = document.createElement('div');
            // new_timeline.setAttribute('id','tweet-collection-timeline');

            // var parent = document.getElementById('container');
            // parent.appendChild(new_timeline);

            var collection_id = "<?php echo $collection_id[1];?>";

            var tl = twttr.widgets.createTimeline (
                {   // 第1引数: ウィジェットの種類
                    sourceType: "collection",
                    id: collection_id,   // コレクションID
                },
                document.getElementById("container") ,    // 第2引数: コンテナの要素
                //new_timeline,
                {   // 第3引数: パラメータ
                  width: 500 ,    // 横幅
                }
            ) ;
        }
        var count = 0;
        function get_iframe_contents(){
            var tl = document.getElementById("twitter-widget-" + count + "").contentWindow.document;
            count++;
            // 文字列処理によって、不要なツイート部分の<li>を取り除く(splitを用いるとできそう)

            // splitstr = "I have".split(' ');
            

            // コレクションの最後のツイート(<li>タグ)の抜き出し
            splitstr = tl.body.innerHTML.split('<ol')[1];
            splitstr = splitstr.split('</ol>')[0];
            // splitstr = splitstr.split('<li class="timeline-TweetList-tweet customisable-border">');
            // splitstr = '<li class="timeline-TweetList-tweet customisable-border">' + splitstr[splitstr.length-1];
            
            
            // console.log(tl.innerHTML);
            // console.log(tl.innerText);
            // console.log(tl.textContent);
            console.log(typeof splitstr);
            console.log(splitstr);
            // console.log(tl.body.innerHTML);
            // alert(typeof tl.body.innerHTML)
            // tl.body.innerHTML = "<p>hogehoge</p><br>"
        }

        // setInterval(show_collection, 2000);
        </script>
    </head>
    
    <body>
        <input type="button" value="更新" onClick="show_collection();">
        <input type="button" value="iframe内容取得" onClick="get_iframe_contents();">
        <p>コレクションは<a href="<?php echo $collection_url;?>" target="_blank">こちら</a>です。</p>

        <!-- <h2>タイムライン形式</h2> -->
        <!-- コンテナ -->
        <div id="container">
            <div id="tweet-collection-timeline">    
            </div>
        </div>
        <!-- <script type="text/javascript">show_collection();</script> -->
    </body>
</html>
