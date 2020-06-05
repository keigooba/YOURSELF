<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「 退会ページ」');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ================================
// 画面処理
// ================================
// post送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります ');

    //ゲストユーザーかどうか確認
    if($_SESSION['user_id'] === 1){
        GuestUser();
        return  header("Location:mypage.php");
    }
    debug('ゲストユーザーではありません。');
    //例外処理
    try {
        //DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
        //データ流し込み
        $data = array(':us_id' => $_SESSION['user_id']);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ実行成功の場合
        if($stmt){
            // セッション削除
            session_destroy();
            debug('セッション変数の中身：'.print_r($_SESSION,true));
            debug('トップページへ遷移します。');

            header("Location:index.php");

        }else{
            debug('クエリが失敗しました。');
            $err_msg['common'] = MSG07;
        }

    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '退会';
require('head.php');
?>
<body>
    <?php
    require('header.php');
    ?>
    <div id="main">
        <div id="withdraw-container" class="site-width">
            <section class="withdraw-box">
                <h1 class="title">退会</h1>
                <form action="" method="post" class="form">
                    <input type="submit" name="submit" value="退会する" style="float:inherit;">
                </form>
            </section>
            <a href="mypage.php">&lt;マイページに戻る</a>
        </div>
    </div>
    <?php
    require('footer.php');
    ?>
</body>
</html>
