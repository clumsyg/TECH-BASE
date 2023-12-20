<!-- 削除完了後、"削除済み"と表示させる -->

<!-- 38, 110以降(DELETEではなくUPDATEにしたり -->



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
<body>
    
    <div style="width: 500px; overflow: hidden;">
    <marquee behavior="alternate" direction="right" scrollamount="5">
        <h1 style="color: blue; font-size: 30px;">プログラミング掲示板</h1>
    </marquee>
    </div>

    <?php
    
    # データ接続設定
    $dsn = 'mysql:dbname=********db;host=localhost';
    $user = '*********';
    $password = '**********';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    # bbstbというテーブルを作成
    # bbs = bulletin board system（掲示板）
    $sql = "CREATE TABLE IF NOT EXISTS bbstb"
    ." ("
    . "number INT AUTO_INCREMENT PRIMARY KEY,"
    . "name CHAR(32),"
    . "comment TEXT,"
    . "date TEXT,"
    . "pw TEXT,"
    . "deleted TEXT,"
    . "edited TEXT"
    .");";
    $stmt = $pdo->query($sql);
    
    $pw = isset($_POST["pw"]) ? $_POST["pw"] : "";
    
    
    
    
    # 投稿
    if (!empty($_POST["post_button"])) {    # 投稿ボタンが押された場合
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $date = date("Y年m月d日 H:i:s");
        $edited = "(編集済み)";
        if (!empty($name) && !empty($comment)) {    # 名前とコメントが空でないことを確認
            if (!empty($_POST["pw"])) {    # pwも入力された場合
                $pw = $_POST["pw"];
                if (!empty($_POST["option"])) {     # 編集手続きがなされた後の編集投稿の場合
                    $option = $_POST["option"];
                    $sql = 'SELECT * FROM bbstb WHERE number=:number';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':number', $option, PDO::PARAM_INT);
                    $stmt->execute();
                    $results = $stmt->fetchAll();
                    if ($results[0]["pw"] == $pw) {    # ここ足した。sqlデータ内のpwが送信されたpwと一致した場合
                        echo "編集完了<br>";
                        $sql = 'UPDATE bbstb SET name=:name,comment=:comment,date=:date,edited=:edited WHERE number=:number';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':number', $option, PDO::PARAM_INT);
                        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                        $stmt->bindParam(':edited', $edited, PDO::PARAM_STR);
                        $stmt->execute();
                    } else {    # sqlデータ内のpwが送信されたpwと一致しなかった場合
                        echo "パスワードが違います<br>";
                    }
                } else {    # 新規投稿の場合
                    echo "投稿完了<br>";
                    $sql = "INSERT INTO bbstb (name, comment, date, pw) VALUES (:name, :comment, :date, :pw)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt->bindParam(':pw', $pw, PDO::PARAM_STR);
                    $stmt->execute();
                }
            } else {    # pwが入力されていない場合
                echo "パスワードを入力してください<br>";
            }
        } else {
            echo "名前とコメントを入力してください<br>";
        }
    }


    
    
    
    # 削除
    elseif (!empty($_POST["del_button"]) && !empty($_POST["pw"])) {     # 削除ボタンが押された かつ pwも入力された場合
        $del_num = $_POST["del_num"];

        $sql = 'SELECT * FROM bbstb WHERE number=:number';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':number', $del_num, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        if ($results[0]["pw"] == $pw) {    # sqlデータ内のpwが送信されたpwと一致した場合
            echo "削除完了<br>";
            $name = "";
            $comment = "";
            $date = "";
            $pw = "";
            $deleted = "削除済み";
            $edited = "";
            $sql = 'UPDATE bbstb SET number=:number,name=:name,comment=:comment,date=:date,pw=:pw,deleted=:deleted,edited=:edited WHERE number=:number';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':number', $del_num, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':pw', $pw, PDO::PARAM_STR);
            $stmt->bindParam(':deleted', $deleted, PDO::PARAM_STR);
            $stmt->bindParam(':edited', $edited, PDO::PARAM_STR);
            $stmt->execute();
        } else {    # sqlデータ内のpwが送信されたpwと一致しなかった場合
            echo "パスワードが違います<br>";
        }
    } elseif (!empty($_POST["del_button"])) {      # 削除ボタンは押されたがpwが入力されていない場合（その逆はありえないのでelseは省略）
        echo "パスワードも入力してください<br>";
    }
    
    
    
    # 編集
    elseif (!empty($_POST["edit_button"]) && !empty($_POST["pw"])) {    # 編集ボタンが押された　かつ pwも入力された場合
        $edit_num = $_POST["edit_num"];
    
        $sql = 'SELECT * FROM bbstb WHERE number=:number';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':number', $edit_num, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        if ($results[0]["pw"] == $pw) {    # sqlデータ内のpwが送信されたpwと一致した場合
            echo "編集中<br>";
            $edit_name = $results[0]['name'];
            $edit_comment = $results[0]['comment'];
        } else {    # sqlデータ内のpwが送信されたpwと一致しなかった場
            echo "パスワードが違います<br>";
        }
    } elseif (!empty($_POST["edit_button"])) {      # 編集ボタンは押されたがpwが入力されていない場合（その逆はありえないのでelseは省略）
        echo "パスワードも入力してください<br>";
    }


    
    # テーブルの中身を全て表示（=id指定なし）
    $sql = 'SELECT * FROM bbstb';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row) {
        echo $row['number'] . ' ';    # $rowの中にはテーブルのカラム名が入る
        echo $row['name'] . ' ';
        echo $row['comment'] . ' ';
        echo $row['date'] . ' ';
        echo $row['deleted'] . ' ';
        echo $row['edited'] . '<br>';
        echo "<hr>";
    }
    
    ?>


    <form action="" method="post">
        <input type="number" name="option" size="65" value="<?php if(isset($edit_name)) echo $edit_num; ?>" placeholder="編集番号(書き込み不可。編集中に自動で表示されます)" readonly><br>
        <input type="text" name="name" value="<?php if(isset($edit_name)) echo $edit_name; ?>" placeholder="名前">
        <input type="text" name="comment" value="<?php if(isset($edit_comment)) echo $edit_comment; ?>" placeholder="コメント">
        <input type="submit" name="post_button" value="投稿"><br>
        
        <input type="number" name="del_num" placeholder="削除番号">
        <input type="submit" name="del_button" value="削除"><br>
        
        <input type="number" name="edit_num" placeholder="編集番号">
        <input type="submit" name="edit_button" value="編集作業へ"><br>
        
        <input type="text" name="pw" placeholder="パスワード"><br>
    </form>
    
</body>
</html>
