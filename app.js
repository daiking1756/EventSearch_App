/* 1. expressモジュールをロードし、インスタンス化してappに代入。*/
// var router = express.Router();
// var app = express();
const   express = require('express'),
        app = require('http').createServer(handler),
        fs = require('fs'),
        io = require('socket.io').listen(app),
        exec = require('child_process').exec,
        mongojs = require('mongojs'),
        db_url = 'mongodb://127.0.0.1:27017/eventtweet',
        db = mongojs(db_url),
        tweetdata = db.collection('tweetdata'),
        request_num = 2;

const   get_tweet = 'python3.6 ./get_tweet.py';
        

app.listen(8000);
console.log("Server running ... at 192.168.33.10:8000")

prev_count = 0;
page_name = '/index.html';
keyword = "none";

function handler(req, res){
    fs.readFile(__dirname + page_name, 'UTF-8', function (err, data) { 
        if(err){
            res.writeHead(500);
            return res.end("Error");
        }
        res.writeHead(200, {'Content-Type': 'text/html'}); 
        res.write(data);
        res.end(); 
    });
}

function check_mongoDB(data, since_date, until_date, keyword){
    // console.log("check_mongoDB!");
    if(keyword=='none'){
        db.tweetdata.count({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true, keyword:{$exists:false}},function(err, docs) {
            if (err) {
                console.log('Error!1');
                return;
            }
            count = docs;
            if(count > prev_count){ // イベントツイートが追加された場合
                console.log("insert new tweet!");
                var diff = count - prev_count;
                prev_count = count;
                get_tweetID(diff, data, since_date, until_date, keyword);
                // db_reConnect();
            }
        });
    }
    else{
        db.tweetdata.count({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, keyword:keyword},function(err, docs) {
            if (err) {
                console.log('Error!1');
                return;
            }
            count = docs;
            if(count > prev_count){ // イベントツイートが追加された場合
                console.log("insert new tweet!");
                var diff = count - prev_count;
                prev_count = count;
                get_tweetID(diff, data, since_date, until_date, keyword);
                // db_reConnect();
            }
        });
    }
}

function get_tweetID(diff, data, since_date, until_date, keyword){
    if(keyword=='none'){
        db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true, keyword:{$exists:false}}).sort({$natural:-1}).limit(diff).toArray(function(err, docs){
        // db.tweetdata.find({event_date:{$exists:true, $ne:false}, event_gcpnl:true}).limit(diff).toArray(function(err, docs) {
            if (err) {
                console.log('Error!2');
                return;
            }
            docs.forEach(function(doc) {
                console.log("count :" + count);
                console.log("tweetID :" + doc.id_str);
                // console.log("search_word is :" + data[0]);
                
                io.sockets.emit('emit_from_server_tweetID', doc.id_str);    // クライアントにツイートIDを送信

            });
        });
    }else{
        db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, keyword:keyword}).sort({$natural:-1}).limit(diff).toArray(function(err, docs){
    // db.tweetdata.find({event_date:{$exists:true, $ne:false}, event_gcpnl:true}).limit(diff).toArray(function(err, docs) {
        if (err) {
            console.log('Error!2');
            return;
        }
        docs.forEach(function(doc) {
            console.log("count :" + count);
            console.log("tweetID :" + doc.id_str);
            // console.log("search_word is :" + data[0]);
            
            io.sockets.emit('emit_from_server_tweetID', doc.id_str);    // クライアントにツイートIDを送信

        });
    });
    }
}

// function db_reConnect(){
//     db.close();
//     db = mongojs(db_url);
// }

io.sockets.on('connection', function(socket){
    socket.on('emit_from_client_searchStart', function(data){   // 検索ボタンが押された場合
        console.log(data);

        //########################## ↓ 初期化処理 ↓ ##############################################################################
        search_word = data[0];

        sd = data[1].split('-');
        ud = data[2].split('-');
        
        since_date = new Date(Number(sd[0]), Number(sd[1])-1, Number(sd[2])+1, -15, 0, 0);
        until_date = new Date(Number(ud[0]), Number(ud[1])-1, Number(ud[2])+1, -15, 0, 0);
        
        sort_by = "event_date";
        sort_order = 1;
        // sort_by = data[3];
        // sort_order = Number(data[4]);
        
        keyword = data[3];
        // keyword = data[5];
        
        if(keyword == ''){
            keyword = "none";
        }

        if(keyword=='none'){
            db.tweetdata.count({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true, keyword:{$exists:false}},function(err, docs) {
                if (err) {
                    console.log('Error!6');
                    return;
                }
                prev_count = docs;
            });
        }else{
            db.tweetdata.count({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, keyword:keyword},function(err, docs) {
                if (err) {
                    console.log('Error!6');
                    return;
                }
                prev_count = docs;
            });
        }
        //########################## ↑ 初期化処理 ↑ ##############################################################################


        setInterval(function(){check_mongoDB(data, since_date, until_date, keyword)}, 500);    // mongoDBへのツイート追加をチェック


        // console.log("since_date :" + since_date);
        // console.log("until_date :" + until_date);

        exec(get_tweet + ' ' + search_word + ' ' + request_num + ' ' + keyword, (err, stdout, stderr) => {
            if (err) { 
                console.log(err); 
            }
            console.log(stdout);
            if (stdout.search("elapsed_time:")!=-1){    // get_tweet.pyの実行が終わった場合
                console.log("get_tweet.py is finished!!!!!!!!!!!!!!!!!");
                socket.emit('emit_from_server_searchEnd', 'searchEnd');
            }   
        });
    });

    socket.on('emit_from_client_sortButton', function(data){    // 並び替えボタンが押された場合
        if(data=="event_date" || data=="favorite_count"){
            sort_by = data;
        }else if(data=='1' || data=="-1"){
            sort_order = Number(data);
        }

        var sorttweetIDs = [];

        if(keyword=="none"){
            if(sort_by=="event_date"){
                db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true, keyword:{$exists:false}}).sort({"event_date":sort_order}).toArray(function(err, docs){
                    if (err) {
                        console.log('Error!3');
                        return;
                    }
                    docs.forEach(function(doc) {
                        //io.sockets.emit('emit_from_server_tweetID', doc.id_str);    // クライアントにツイートIDを送信
                        // console.log(doc.id_str);
                        sorttweetIDs.push(doc.id_str);
                    });
                    console.log(sorttweetIDs);
                    socket.emit('emit_from_server_sorttweetIDs', sorttweetIDs);
                });
            }else if(sort_by=="favorite_count"){
                db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true, keyword:{$exists:false}}).sort({"favorite_count":sort_order}).toArray(function(err, docs){
                    if (err) {
                        console.log('Error!4');
                        return;
                    }
                    docs.forEach(function(doc) {
                        //io.sockets.emit('emit_from_server_tweetID', doc.id_str);    // クライアントにツイートIDを送信
                        // console.log(doc.id_str);
                        sorttweetIDs.push(doc.id_str);
                    });
                    console.log(sorttweetIDs);
                    socket.emit('emit_from_server_sorttweetIDs', sorttweetIDs);
                });
            }
        }else{
            if(sort_by=="event_date"){
            db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, keyword:keyword}).sort({"event_date":sort_order}).toArray(function(err, docs){
                if (err) {
                    console.log('Error!3');
                    return;
                }
                docs.forEach(function(doc) {
                    //io.sockets.emit('emit_from_server_tweetID', doc.id_str);    // クライアントにツイートIDを送信
                    // console.log(doc.id_str);
                    sorttweetIDs.push(doc.id_str);
                });
                console.log(sorttweetIDs);
                socket.emit('emit_from_server_sorttweetIDs', sorttweetIDs);
            });
        }else if(sort_by=="favorite_count"){
            db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, keyword:keyword}).sort({"favorite_count":sort_order}).toArray(function(err, docs){
                if (err) {
                    console.log('Error!4');
                    return;
                }
                docs.forEach(function(doc) {
                    //io.sockets.emit('emit_from_server_tweetID', doc.id_str);    // クライアントにツイートIDを送信
                    // console.log(doc.id_str);
                    sorttweetIDs.push(doc.id_str);
                });
                console.log(sorttweetIDs);
                socket.emit('emit_from_server_sorttweetIDs', sorttweetIDs);
            });
        }
        }
    });
});
