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
            io.sockets.emit('emit_from_server_tweetID', doc.id_str);
        });
    });
}

function db_reConnect(){
    db.close();
    db = mongojs(db_url);
}

io.sockets.on('connection', function(socket){
    socket.on('emit_from_client', function(data){
        console.log(data);
        exec(get_tweet + ' ' + data[0] + ' 3', (err, stdout, stderr) => {
          if (err) { console.log(err); }
          console.log(stdout);
        });
        // socket.emit('emit_from_server', 'client says :'+data);
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
    console.log(global.prev_count);
},500);



// setInterval(function(){
//     // console.log("prev_count typeof :" + typeof prev_count);
//     // console.log("(out)prev_count :" + global.prev_count);
//     console.log("count typeof :" + typeof count);
//     console.log("(out)count :" + count);
// },1000);

/* 2. listen()メソッドを実行して3000番ポートで待ち受け。*/
// http.createServer(function (req, res) {
//   console.log("listen on 8000...")
//   res.writeHead(200, {'Content-Type': 'text/plain'});
//   res.end('Hello Hosomichi!\n');
// }).listen(8080, '127.0.0.1');
// console.log("listen on 8000...")

// var command = 'python3.6 get_tweet.py 広島 10';

// const exec = require('child_process').exec;
// exec(command, (err, stdout, stderr) => {
//   if (err) { console.log(err); }
//   console.log(stdout);
// });

// app.get('/', function(request, response){ 
//       fs.readFile('./index.html', 'UTF-8', 
//             function (err, data) { 
//                 response.writeHead(200, {'Content-Type': 'text/html'}); 
//                 response.write(data); 
//                 response.end(); 
//             } 
//         );

// })

// app.get('/app.js', function(req, res) {
//     res.send('<h1>Hello World!</h1>');
// });

// app.listen(8000, function() {
//     console.log('Express Listen port 8000');
// })


/* 3. 以後、アプリケーション固有の処理 */

// var mongojs = require('mongojs');
// var db = mongojs('mongodb://127.0.0.1:27017/eventtweet');
// var tweetdata = db.collection('tweetdata')
// すべての Document を削除
// db.mycollection.remove();

// Document 追加
// db.testcol.insert({name: 'maku'});
// db.testcol.insert({name: 'moja'});
// db.testcol.insert({name: 'mayy'});

// Document を検索
// db.tweetdata.find(function(err, docs) {
//     if (err) {
//         console.log('Error!');
//         return;
//     }

//     docs.forEach(function(doc) {
//         console.log(doc.text);
//         // console.log(doc);
//         console.log("forEach!!!!!");
//     });

//     db.close();
// });