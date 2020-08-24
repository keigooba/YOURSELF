
<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー詳細ページ」　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

//ログイン認証
require('auth.php');

// 画面表示用データ取得
//================================
// ユーザーデータのGETパラメータを取得
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';
// DBからユーザーデータを取得
$viewData = getUser($u_id);
// パラメータに不正な値が入っているかチェック
if(empty($viewData)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
debug('取得したDBデータ：'.print_r($viewData,true));
// DBから自分のユーザーデータを取得
$myUserInfo = getUser($_SESSION['user_id']);

debug('取得した自分のDBデータ：'.print_r($myUserInfo,true));

// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');

  //会社登録済みか確認
  // validUserCompany($_SESSION['user_id']);

  // debug('取得したDBデータ：'.print_r(validUserCompany($_SESSION['user_id'])));

  if(empty($err_msg)){

    //例外処理
    try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'INSERT INTO bord (entry_user,company_user,create_date) VALUES (:e_uid, :c_uid, :date)';
    $data = array(':e_uid' => $viewData['id'], ':c_uid' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
    // クエリ実行
    $stmt = queryPost($dbh, $sql , $data);

    // クエリ成功の場合
    if($stmt){
      $_SESSION['msg_time'] = date('m-d H:i');
      $_SESSION['msg_username'] =$myUserInfo['name'];
      $_SESSION['msg_success'] = SUC04;

      debug('連絡掲示板へ遷移します。');

      header("Location:msg.php?m_id=".$dbh->lastInsertID()); //連絡掲示板へ
    }

    } catch (Exception $e) {
      error_log('エラー発生：' .$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

debug('画面表示処理終了
<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'ユーザー詳細情報';
require('head.php');
?>

<body>
  <?php
    require('header.php');
    ?>
  <div id="main" class="site-width">
    <section id="user-container">
      <div class="title-block">
        <a href="index.php<?php echo appendGetParam(array('u_id')); ?>" class="cancel">キャンセル</a>
        <h1 class="title">ユーザー詳細ページ</h1>
        <form action="" method="post">
          <button type="submit" name="submit" class="contact">連絡掲示板へ</button>
        </form>
      </div>
      <div class="area-msg">
        <?php
        if(!empty($err_msg['common'])) echo $err_msg['common'];
        ?>
      </div>
      <div class="user__imgframe">
        <img src="<?php echo showImg(sanitize($viewData['pic'])); ?>" class="img">
        <i class="fa fa-heart icn-like js-click-like <?php if(isLike($_SESSION['user_id'], $viewData['id'])){ echo 'active'; } ?>" aria-hidden="true" data-entryuser_id="<?php echo sanitize($viewData['id']); ?>" ></i>
      </div>
      <table class="user__textbox">
       <tbody>
          <tr>
            <th>
              お名前
            </th>
            <td>
              <?php
              echo sanitize($viewData['surname']);
              ?>
            </td>
            <td>
              <?php
              echo sanitize($viewData['name']);
              ?>
            </td>
          </tr>
          <tr>
            <th>
            お名前（フリガナ）
            </th>
            <td>
            <?php
            echo sanitize($viewData['surkanaName']);
            ?>
            </td>
            <td>
            <?php
            echo sanitize($viewData['kanaName']);
            ?>
            </td>
          </tr>
          <tr>
            <th>
            メールアドレス
            </th>
            <td colspan="2">
            <?php
            echo sanitize($viewData['email']);
            ?>
            </td>
          </tr>
          <tr>
            <th>
            電話番号
            </th>
            <td colspan="2">
            <?php
            echo sanitize($viewData['tel']);
            ?>
            </td>
          </tr>
          <tr>
            <th>
            郵便番号
            </th>
            <td colspan="2">
            <?php
            echo sanitize($viewData['zip']);
            ?>
            </td>
          </tr>
          <tr>
            <th>
            住所
            </th>
            <td colspan="2">
            <?php
            echo sanitize($viewData['addr']);
            ?>
            </td>
          </tr>
          <tr>
            <th>
            年齢
            </th>
            <td colspan="2">
            <?php
            if(!empty($viewData['age']))
            echo sanitize($viewData['age']);
            ?>
            </td>
          </tr>
        </body>
      </table>
    </section>
  </div>
  <?php
  require('footer.php');
  ?>
</body>
</html>
