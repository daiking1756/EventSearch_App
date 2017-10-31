<form action="result.php" method="get">
    地名：<input type="text" name="message" placeholder="地名" required><br>
    日付：<input type="date" name="since_date" value="<?php echo(date('Y-m-d'));?>" required> ~
        <input type="date" name="until_date" value="<?php echo(date('Y-m-d', strtotime("+ 1 months")));?>" required><br>
    並び替え：<select name="sort_by">
            <option value="date" selected>日付順</option>
            <option value="favoriteeeee">お気に入り順</option>
            </select><br>
    順序：<select name="sort_order">
            <option value="-1">昇順</option>
            <option value="1" selected>降順</option>
            </select><br>
 <!--  <input type="radio" name="sort_radio" value="date">日付順<br>
  <input type="radio" name="sort_radio" value="favorite">反響順<br>
  --> 
    キーワード：<input type="text" name="keyword" placeholder="例：ライブ"><br>
    <input type="submit" value="検索">

</form>