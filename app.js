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
        tweetdata = db.collection('tweetdata');

const   get_tweet = 'python3.6 ./get_tweet.py';
        

app.listen(8000);

global.prev_count = 0;

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

function check_mongoDB(){
    // console.log("check_mongoDB!");
    db.tweetdata.count({event_date:{$exists:true, $ne:false}, event_gcpnl:true},function(err, docs) {
        if (err) {
            console.log('Error!');
            return;
        }
        // console.log(docs);
        global.count = docs;
        if(global.count > global.prev_count){ // イベントツイートが追加された場合
            // console.log("insert new tweet!");
            var diff = global.count - global.prev_count;
            global.prev_count = global.count;
            get_tweetID(diff);
            // db_reConnect();
        }
    });
}

function get_tweetID(diff){
    db.tweetdata.find({event_date:{$exists:true, $ne:false}, event_gcpnl:true}).sort({$natural:-1}).limit(diff).toArray(function(err, docs){
        if (err) {
            console.log('Error!');
            return;
        }
        docs.forEach(function(doc) {
            console.log(doc.id_str);
            io.sockets.emit('emit_from_server_tweetID', doc.id_str);    // クライアントにツイートIDを送信
        });
    });
}

// function db_reConnect(){
//     db.close();
//     db = mongojs(db_url);
// }

io.sockets.on('connection', function(socket){
    socket.on('emit_from_client', function(data){
        console.log(data);
        exec(get_tweet + ' ' + data[0] + ' 3', (err, stdout, stderr) => {
          if (err) { console.log(err); }
          console.log(stdout);
        });
    });
});

// 最初に一回、prev_countを初期化するための処理
setImmediate(function(){
    db.tweetdata.count({event_date:{$exists:true, $ne:false}, event_gcpnl:true},function(err, docs) {
        if (err) {
            console.log('Error!');
            return;
        }
        global.prev_count = docs;
    });
});


setInterval(check_mongoDB, 500);    // mongoDBへのツイート追加をチェック
setInterval(function(){
    console.log(global.prev_count); // デバッグ用
},500);
