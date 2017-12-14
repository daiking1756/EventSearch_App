<?php
    // $checkDB_path = 'python3.6 ./checkDB.py ' . htmlspecialchars($_GET['message']) . ' ' . htmlspecialchars($_GET['since_date']) . ' ' . htmlspecialchars($_GET['until_date']) . ' ' . htmlspecialchars($_GET['sort_by']) . ' ' . htmlspecialchars($_GET['sort_order']);
    $get_tweet_path = 'python3.6 ./get_tweet.py ' . htmlspecialchars($_GET['message']);
    $regex_path = 'python3.6 ./regex.py';
    $get_tweetIDs_path = 'python3.6 ./get_tweetIDs.py ' . htmlspecialchars($_GET['message']) . ' ' . htmlspecialchars($_GET['since_date']) . ' ' . htmlspecialchars($_GET['until_date']) . ' ' . htmlspecialchars($_GET['sort_by']) . ' ' . htmlspecialchars($_GET['sort_order']);
  
    exec($get_tweet_path);
    exec($regex_path);
    exec($get_tweetIDs_path, $tweetIDArray);

    // echo($checkDB_path);

    // exec($checkDB_path . " &",$tweetIDArray);
    // exec($get_tweet_path . " > /dev/null &");
    // exec($get_tweetIDs_path, $tweetIDArray);

    $tweetIDArray = json_encode($tweetIDArray);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>JavaScriptで埋め込みツイートを表示するデモ</title>
    </head>
    <body>

        <h1>JavaScriptで埋め込みツイートを表示するデモ</h1>
        <!-- <input type="button" value="timer1 start" onClick="timer_head();">
        <input type="button" value="timer1 stop" onClick="count = 0;">
         -->
        <input type="button" value="ツイート埋め込み！" onClick="dosomething();">
        <input type="button" value="昇順" onClick="sort_order='-1';reflesh();">
        <input type="button" value="降順" onClick="sort_order='1';reflesh();">
        <input type="button" value="日付順" onClick="sort_by='date';reflesh();">
        <input type="button" value="イイね順" onClick="sort_by='favo';reflesh();">
        <!-- コンテナ -->
        <div id="embeddedTweet-container">
            <div id="embeddedTweet-container-tmp"></div>
        </div>


        <script type="text/javascript" src="https://platform.twitter.com/widgets.js" async></script>
        <script>
            // var tweetID = '';
            // var tweetList = []
            // var count = 0
            // console.log(<?php echo($tweetIDArray); ?>);
            // while(1){
            //     tweetList = <?php echo($tweetIDArray); ?>;
            //     tmp = tweetList.length;
            //     if(count != tmp){
            //         console.log(tweetList);
            //         // prev_tweetID = tweetID;
            //         // tweetID = tweetList[0];
            //         count = tmp;
            //         console.log("test");
            //         console.log(tweetList);
            //         break;
            //     }
            // }

            var tweetList = <?php echo($tweetIDArray); ?>;
            console.log(tweetList);
            
            var tweetList_datesort = tweetList[0].replace('[','').replace(']','').split(',');
            var tweetList_favosort = tweetList[1].replace('[','').replace(']','').split(',');


            console.log(tweetList_datesort);
            console.log(tweetList_favosort);

            var list_size = tweetList_datesort.length;  // tweetList_favosortの方のlengthを取得しても同じ

            var sort_by = "<?php echo(htmlspecialchars($_GET['sort_by'])); ?>";
            var sort_order = "<?php echo(htmlspecialchars($_GET['sort_order'])); ?>";
            
            dosomething();

            function dosomething(){
                console.log("dosomething!");
                console.log(sort_order + ", " + sort_by);

                for(var i=0; i<list_size; i++){ 
                    new_DOM = create_EmbeddedTweetDOM(i);
                    if(sort_by=="date"){
                        show_EmbeddedTweet(i, tweetList_datesort, new_DOM, sort_order);
                    }else{
                        show_EmbeddedTweet(i, tweetList_favosort, new_DOM, sort_order);
                    }
                }
            }

            function reset_DOM(element){
                
                element.parentNode.removeChild(element);
                var new_DOM = document.createElement('div');
                new_DOM.setAttribute('id', 'embeddedTweet-container-tmp');
                var parent = document.getElementById('embeddedTweet-container');
                parent.appendChild(new_DOM);
            }

            function reflesh(){
                var old_DOM = document.getElementById('embeddedTweet-container-tmp');
                reset_DOM(old_DOM);
                dosomething();
            }

            function create_EmbeddedTweetDOM(count){
                var new_DOM = document.createElement('div');
                new_DOM.setAttribute('id','tweet-container'+count+"");
                var parent = document.getElementById('embeddedTweet-container-tmp');
                parent.appendChild(new_DOM);
                return new_DOM;
            }

            function show_EmbeddedTweet(count, tweetList, container, sort_order){
                if(sort_order=='1'){    // 降順の場合
                    // console.log("i :"+count);
                    var index = count;
                    var tweetID = tweetList[index].replace(' ','').replace('\'','').replace('\'','');
                    // console.log("index :"+index+ ", ID :"+tweetID);
                    // console.log("降順");
                }else{
                    // console.log("i :"+count);
                    var index = list_size-1-count
                    var tweetID = tweetList[index].replace(' ','').replace('\'','').replace('\'','');
                    // console.log("index :"+index+ ", ID :"+tweetID);
                    // console.log("昇順");
                }
                    twttr.widgets.createTweet (
                        tweetID ,  // ツイートID
                        // '940043877800484865',
                        container , // コンテナの要素
                        {   // パラメータ
                            // cards: "hidden" ,
                            conversation: "none" ,
                            theme: "dark",
                            // linkColor: "#D36015" ,
                            // width: 250 ,
                            align: "center" ,
                            lang: "ja" ,
                            dnt: true ,
                        }
                    ) ;
            }

            // // setInterval(dosomething,1000);
        </script>
    </body>
</html>