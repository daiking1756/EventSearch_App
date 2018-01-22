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

function handler(req, res){
    fs.readFile(__dirname + '/index.html', 'UTF-8', function (err, data) { 
        if(err){
            res.writeHead(500);
            return res.end("Error");
        }
        res.writeHead(200, {'Content-Type': 'text/html'}); 
        res.write(data);
        res.end(); 
    });
}

function check_mongoDB(data, since_date, until_date){
    // console.log("check_mongoDB!");
    
    db.tweetdata.count({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true},function(err, docs) {
        if (err) {
            console.log('Error!1');
            return;
        }
        count = docs;
        if(count > prev_count){ // イベントツイートが追加された場合
            console.log("insert new tweet!");
            var diff = count - prev_count;
            prev_count = count;
            get_tweetID(diff, data, since_date, until_date);
            // db_reConnect();
        }
    });
}

function get_tweetID(diff, data, since_date, until_date){
    db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true}).sort({$natural:-1}).limit(diff).toArray(function(err, docs){
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

// function db_reConnect(){
//     db.close();
//     db = mongojs(db_url);
// }

io.sockets.on('connection', function(socket){
    socket.on('emit_from_client_searchStart', function(data){   // 検索ボタンが押された場合
        console.log(data);

        //########################## ↓ 初期化処理 ↓ ##########################
        sd = data[1].split('-');
        ud = data[2].split('-');
        
        since_date = new Date(Number(sd[0]), Number(sd[1])-1, Number(sd[2])+1, -15, 0, 0);
        until_date = new Date(Number(ud[0]), Number(ud[1])-1, Number(ud[2])+1, -15, 0, 0);
        
        search_word = data[0];
        sort_by = data[3];
        sort_order = Number(data[4]) * (-1);
        
        db.tweetdata.count({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true},function(err, docs) {
            if (err) {
            console.log('Error!6');
            return;
        }
            prev_count = docs;
        });
        //########################## ↑ 初期化処理 ↑ ##########################


        setInterval(function(){check_mongoDB(data, since_date, until_date)}, 500);    // mongoDBへのツイート追加をチェック


        // console.log("since_date :" + since_date);
        // console.log("until_date :" + until_date);

        exec(get_tweet + ' ' + search_word + ' ' + request_num, (err, stdout, stderr) => {
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

        if(sort_by=="event_date"){
            db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true}).sort({"event_date":sort_order}).toArray(function(err, docs){
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
            db.tweetdata.find({search_word:search_word, event_date:{$gt:since_date, $lt:until_date}, event_gcpnl:true}).sort({"favorite_count":sort_order}).toArray(function(err, docs){
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
    });
});

// 最初に一回、prev_countを初期化するための処理
setImmediate(function(){
    db.tweetdata.count({event_date:{$exists:true, $ne:false}, event_gcpnl:true},function(err, docs) {
        if (err) {
            console.log('Error!5');
            return;
        }
        prev_count = docs;
    });
});


// setInterval(check_mongoDB, 500);    // mongoDBへのツイート追加をチェック
// setInterval(function(){
//     console.log(prev_count); // デバッグ用
// },500);
