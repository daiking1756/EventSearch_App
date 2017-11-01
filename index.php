<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>入力ページ</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <style>
        @media (min-width: 768px)
        .container {
            max-width: 730px;
        }
    </style>
</head>
<body>
    <div class="container">
    <h1>Event Tweet Search</h1>
        <form action="result.php" method="get">
            <div class="form-group">
                <label>地名：</label>
                <input type="text" name="message" placeholder="例：渋谷" required>
            </div>
            <div class="form-group">
                <label>日付：</label>
                <input type="date" name="since_date" value="<?php echo(date('Y-m-d'));?>" required> ~
                <input type="date" name="until_date" value="<?php echo(date('Y-m-d', strtotime("+ 1 months")));?>" required>
            </div>
            <div class="form-group">
                <label>並び替え：</label>
                <select name="sort_by">
                    <option value="date" selected>日付順</option>
                    <option value="favoriteeeee">お気に入り順</option>
                </select>
            </div>
            <div class="form-group">
                <label>順序：</label>
                <select name="sort_order">
                    <option value="1" selected>降順</option>
                    <option value="-1">昇順</option>
                </select>
            </div>
            <div class="form-group">
                <label>キーワード：</label>
                <input type="text" name="keyword" placeholder="例：ライブ"><br>    
            </div>
            <input type="submit" value="検索" class="btn btn-primary">
        </form>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</body>
</html>