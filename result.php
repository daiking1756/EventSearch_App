<?php
    $request_num = 5;

    // $checkDB_path = 'python3.6 ./checkDB.py ' . htmlspecialchars($_GET['message']) . ' ' . htmlspecialchars($_GET['since_date']) . ' ' . htmlspecialchars($_GET['until_date']) . ' ' . htmlspecialchars($_GET['sort_by']) . ' ' . htmlspecialchars($_GET['sort_order'] . ' ' . $request_num);
    $get_tweet_path = 'python3.6 ./get_tweet.py ' . htmlspecialchars($_GET['message'] . ' ' . $request_num);
    $regex_path = 'python3.6 ./regex.py';
    $get_tweetIDs_path = 'python3.6 ./get_tweetIDs.py ' . htmlspecialchars($_GET['message']) . ' ' . htmlspecialchars($_GET['since_date']) . ' ' . htmlspecialchars($_GET['until_date']) . ' ' . htmlspecialchars($_GET['sort_by']) . ' ' . htmlspecialchars($_GET['sort_order']);
  
    exec($get_tweet_path);
    exec($regex_path);
    exec($get_tweetIDs_path, $tweetIDArray);

    // echo($checkDB_path);

    // exec($checkDB_path . " > /dev/null &");
    // exec($get_tweet_path . " > /dev/null &");

    // exec($checkDB_path . " > /dev/null");
    // exec($get_tweet_path . " > /dev/null");

    // echo("tweetIDArray :");echo($tweetIDArray[0]);

    $tweetIDArray = json_encode($tweetIDArray);
    // echo($tweetIDArray);
    // echo("result : " .$tweetIDArray[1]);
      
?>
<!DOCTYPE html>
<html>
    <head>
        <title>JavaScriptで埋め込みツイートを表示するデモ</title>
        <meta name="twitter:widgets:autoload" content="on">
        <!-- <meta name="twitter:widgets:csp" content="on"> -->
    </head>
    <body>

        <h1>JavaScriptで埋め込みツイートを表示するデモ</h1>
        
        <!-- <input type="button" value="setInterval(check_tweetID, 1000);" onClick="setInterval(check_tweetID, 1000);">
        <input type="button" value="show();" onClick="show();">
        <input type="button" value="showList();" onClick="showList();">
         -->

        <input type="button" value="ツイート埋め込み！" onClick="dosomething(list_size, tweetList);">
        <input type="button" value="昇順" onClick="sort_order='-1';reflesh();">
        <input type="button" value="降順" onClick="sort_order='1';reflesh();">
        <input type="button" value="日付順" onClick="sort_by='date';reflesh();">
        <input type="button" value="イイね順" onClick="sort_by='favo';reflesh();">
        <!-- コンテナ -->
        <div id="embeddedTweet-container">
            <div id="embeddedTweet-container-tmp">
            </div>
        </div>


        <script async charset="utf-8" type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>
        <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
        <!-- <script>
            window.twttr = (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0],
                t = window.twttr || {};
                if (d.getElementById(id)) return t;
                js = d.createElement(s);
                js.id = id;
                js.src = "https://platform.twitter.com/widgets.js";

                js.type = "text/javascript";
                js.charset = "utf-8";

                fjs.parentNode.insertBefore(js, fjs);

                t._e = [];
                t.ready = function(f) {
                    t._e.push(f);
                };

                return t;
            }(document, "script", "twitter-wjs"));
        </script> -->
        <script>
            var tweetList = <?php echo($tweetIDArray); ?>;

            console.log(tweetList);

            var tweetList_datesort = tweetList[0].replace('[','').replace(']','').split(',');
            var tweetList_favosort = tweetList[1].replace('[','').replace(']','').split(',');

            console.log(tweetList_datesort);
            console.log(tweetList_favosort);

            var list_size = tweetList_datesort.length;  // tweetList_favosortの方のlengthを取得しても同じ

            console.log("list_size :"+list_size);

            var sort_by = "<?php echo(htmlspecialchars($_GET['sort_by'])); ?>";
            var sort_order = "<?php echo(htmlspecialchars($_GET['sort_order'])); ?>";
            
            var tweetID_count = 0;
            // var i = 0;
            // var index_prev = 0;
            // dosomething();

            // setInterval(check_tweetID, 1000)    // 処理開始
            // dosomething(5, []);

            console.log(sort_by);
            console.log(sort_order);
            // console.log(typeof sort_order);

            function show(){
                twttr.widgets.load(
                    document.getElementById('embeddedTweet-container')
                ).then(function (el) {
                    console.log("twttr.widgets.load DONE");
                });
                twttr.widgets.createTweet (
                    // tweetID ,  // ツイートID
                    '941330297857048576',
                    // container , // コンテナの要素
                    document.getElementById('embeddedTweet-container'),
                    {   // パラメータ
                        // cards: "hidden" ,
                        // conversation: "none" ,
                        // theme: "dark",
                        // linkColor: "#D36015" ,
                        // width: 250 ,
                        // align: "center" ,
                        // lang: "ja" ,
                        // dnt: true ,
                        align: 'left'
                    }
                ).then(function (el) {
                    console.log("Tweet displayed.")
                });
            }

            function showList(){
                httpObj = new XMLHttpRequest();
                httpObj.open('GET','./tweetID.txt'+"?"+(new Date()).getTime(),true);
                // ?以降はキャッシュされたファイルではなく、毎回読み込むためのもの
                httpObj.send(null);
                httpObj.onreadystatechange = function(){
                    if ( (httpObj.readyState == 4) && (httpObj.status == 200) ){
                        // document.getElementById("text1").value=httpObj.responseText;
                        var tweetIDArray = httpObj.responseText.split(',');
                        // console.log("XMLHttpRequest OK 200.");
                        dosomething(tweetIDArray.length, tweetIDArray);
                    }
                }
            }


            function dosomething(list_size, tweetList){
                console.log("dosomething!");
                // console.log(sort_order + ", " + sort_by);
                console.log(list_size);
                console.log(tweetList);

                // console.log(tweetList[i]);
                // console.log(typeof tweetList[i]);


                for(var i=0; i<list_size; i++){ 
                    // console.log("i :"+i)
                    new_DOM = create_EmbeddedTweetDOM(i);
                    // show_EmbeddedTweet(i, tweetList[i], new_DOM, sort_order);
                    // show_EmbeddedTweet(i, tweetList, new_DOM, sort_order);

                    if(sort_by=="date"){
                        show_EmbeddedTweet(i, tweetList_datesort, new_DOM, sort_order);
                    }else{
                        show_EmbeddedTweet(i, tweetList_favosort, new_DOM, sort_order);
                    }
                }
                // index_prev = i;
                // alert("do something!!");
            }

            // function check_tweetID(){
            //     var tweetIDArray = [];
            //     var diff = 0;

            //     httpObj = new XMLHttpRequest();
            //     httpObj.open('GET','./tweetID.txt'+"?"+(new Date()).getTime(),true);
            //     // ?以降はキャッシュされたファイルではなく、毎回読み込むためのもの
            //     httpObj.send(null);
            //     httpObj.onreadystatechange = function(){
            //         if ( (httpObj.readyState == 4) && (httpObj.status == 200) ){
            //             // document.getElementById("text1").value=httpObj.responseText;
            //             tweetIDArray = httpObj.responseText.split(',');
            //             // console.log("XMLHttpRequest OK 200.");
            //             if(tweetID_count <= tweetIDArray.length-1){
            //                 // console.log("tweetID added!");
            //                 diff = (tweetIDArray.length-1)-tweetID_count;
            //                 tweetID_count = tweetIDArray.length-1;
            //                 // console.log("tweetIDArray.length-1 :"+(tweetIDArray.length-1));
            //                 // console.log("tweetID_count :"+tweetID_count);
            //                 // console.log("diff :"+diff);
            //                 // console.log("tweetID_count :"+tweetID_count);
                            
            //                 dosomething(diff, tweetIDArray);
            //             }
            //         }
            //     }
            // }

            function reflesh(){
                var old_DOM = document.getElementById('embeddedTweet-container-tmp');
                reset_DOM(old_DOM);
                // dosomething();
                if(sort_by=="date"){
                        dosomething(list_size, tweetList_datesort);
                        // show_EmbeddedTweet(i, tweetList_datesort, new_DOM, sort_order);
                    }else{
                        dosomething(list_size, tweetList_favosort);
                        // show_EmbeddedTweet(i, tweetList_favosort, new_DOM, sort_order);
                    }
            }

            function reset_DOM(element){
                element.parentNode.removeChild(element);
                var new_DOM = document.createElement('div');
                new_DOM.setAttribute('id', 'embeddedTweet-container-tmp');
                var parent = document.getElementById('embeddedTweet-container');
                parent.appendChild(new_DOM);
            }

            function create_EmbeddedTweetDOM(count){
                var new_DOM = document.createElement('div');
                new_DOM.setAttribute('id','tweet-container'+count+"-tmp");
                var parent = document.getElementById('embeddedTweet-container-tmp');
                parent.appendChild(new_DOM);
                return new_DOM;
            }

            // function show_EmbeddedTweet(count, tweetID, container, sort_order){
            //     data = '<blockquote class="twitter-tweet" data-conversation="none"><a href="https://twitter.com/twitterapi/status/' + tweetID + '"></a></blockquote>';
            //     // data = tweetID;
            //     // data = '<blockquote class="twitter-tweet" data-conversation="none"><input type="button" value="BUTTON"></blockquote>';
            //     // data = tweetID;

            //     console.log(typeof container);
            //     // container.parentNode.appendChild(data);
            //     // container.innerHTML = container.innerHTML + data;
            //     // container.insertAdjacentElement('afterbegin', data);
            //     // container.insertAdjacentHTML = "aaa";

            //     // var d1 = document.getElementById('embeddedTweet-container');
            //     container.insertAdjacentHTML('beforeend', data);

            //     // var huga = 0;
            //     // var hoge = setInterval(function() {
            //     //     console.log("huga :"+huga);
            //     //     huga++;
            //     //     //終了条件
            //     //     if (huga%2 == 0) {
            //     //         clearInterval(hoge);
            //     //         console.log("終わり");
            //     //     }
            //     // }, 3000);

            //     // console.log("data :"+data);
            // }

            function show_EmbeddedTweet(count, tweetList, container, sort_order){
                if(sort_order=='1'){    // 降順の場合
                    // console.log("i :"+count);
                    var index = count;
                    var tweetID = tweetList[index].replace(' ','').replace('\'','').replace('\'','');
                    console.log("index :"+index+ ", ID :"+tweetID);
                    // console.log("降順");
                }else{
                    // console.log("i :"+count);
                    var index = list_size-1-count;
                    var tweetID = tweetList[index].replace(' ','').replace('\'','').replace('\'','');
                    console.log("index :"+index+ ", ID :"+tweetID);
                    // console.log("昇順");
                }
                // twttr.widgets.load(container);

                // console.log("tweetID :"+tweetID);
                // console.log("typeof tweetID :"+ typeof tweetID);
                // console.log("container :"+container);
                // console.log("typeof container :"+ typeof container);


                twttr.widgets.createTweet (
                    tweetID ,  // ツイートID
                    // '941330297857048576',
                    // '941330297857048000',
                    container , // コンテナの要素
                    // document.getElementById('embeddedTweet-container-tmp'),
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
                ).then(function (el) {
                    console.log("Tweet displayed.")
                });
            }
        </script>
    </body>
</html>