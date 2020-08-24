<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「 ログインページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

// =====================
// ログイン画面処理
// =====================
// 1.post送信されていたとき
if(!empty($_POST)){
  debug('POST送信があります。');
  
  //変数にユーザー情報を代入
  $name=$_POST['name'];
  $email=$_POST['email'];
  $pass=$_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false; 

  //未入力チェック
  validRequired($name,'name');
  validRequired($email,'email');
  validRequired($pass,'pass');

  if(empty($err_msg)){

    //名前漢字チェック
    validCharacters($name,'name');

    //E-mailの形式チェック
    validEmailFormat($email,'email');
  
    //パスワード半角英数チェック
    validHalf($pass,'pass');
    //パスワード最小文字数チェック
    validMinLen($pass,'pass');
    
    if(empty($err_msg)){
      
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT password,id FROM users WHERE name= :name AND email = :email  AND delete_flg = 0';
        $data = array(':name'=>$name, ':email'=>$email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得 
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        debug('クエリ結果の中身：'.print_r($result,true));
        
        //パスワード照合
        if(!empty($result) && password_verify($pass,array_shift($result))){
          debug('パスワードがマッチしました。');

          //ログイン有効期限（デフォルトを1時間とする）
          $sesLimit = 60*60;
          //最終ログイン日時を現在日時に
          $_SESSION['login_date'] = time();

          //ログイン保持にチェックがある場合
          if($pass_save){
            debug('ログイン保持にチェックがあります。');
            //ログイン有効期限を30日にしてセット
            $_SESSION['login_limit'] = $sesLimit * 24 * 30;
          }else{
            debug('ログイン保持にチェックはありません。');
            //次回からログイン保持しないので、ログイン有効期限を1時間後にセット
            $_SESSION['login_limit'] = $sesLimit;
          }
          //ユーザーIDを格納
          $_SESSION['user_id'] = $result['id'];

          debug('セッション変数の中身：'.print_r($_SESSION,true));
          debug('マイページへ遷移します。');
          header("Location:mypage.php"); //マイページへ
        }else{
          debug('名前またはメールアドレスまたはパスワードが一致しません。');
          $err_msg['common'] = MSG09;
        }

      } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
      }
    }
  }
}
 ?>
<?php
$siteTitle = 'ログイン';
require('head.php'); 
?>
<?php
require('header.php');
?>
<p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
</p>
<body>
  <div id="main" class="site-width">
  <div class="guest-container">
    ゲストユーザーの方は以下の名前・メールアドレス・パスワードを使用して下さい。
    <p><i class="fas fa-user-alt"></i>名前：guest</p>
    <p><i class="far fa-envelope"></i>メールアドレス：guest@mail.com</p>
    <p><i class="fas fa-unlock-alt"></i>パスワード：guestmail</p>
  </div>
  <div id="login" style="margin-top:50px;">
    <div id="signup-block"><i class="fas fa-sign-in-alt"></i>ログイン</a></div>
    <div class="area-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?></div>
    <form action="" method="post" style="width:500px; margin:0 auto;">

      <label>名前 ※名前のみ入力してください
        <input type="text" name="name" placeholder="名前" value="<?php if(!empty($_POST['name'])) echo $_POST['name'];?>">
        <div class="err_msg"><?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
        </div>
      </label>
      <label>メールアドレス
        <input type="email" name="email" placeholder="メールアドレス" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
        <div class="err_msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></div>
      </label>
      <label>パスワード ※半角英数8文字以上
        <input type="password" name="pass" placeholder="パスワード" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
        <div class="err_msg"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
        </div>
      </label>
      <label>
        <input type="checkbox" name="pass_save">次回ログインを省略する
      </label>
      <input type="submit" value="ログイン">
      <div  class="passRemind">パスワードを忘れた方は<a href="passRemindSend.php">こちら</a>
      </div>
    </form>
  </div> 
  </div>
  <?php
  require('footer.php');
  ?>
</body>
</html>