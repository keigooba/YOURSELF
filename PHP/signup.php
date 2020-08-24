<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ユーザー登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// 1.post送信されていたとき
if(!empty($_POST)){

  // 変数にユーザ-情報を代入
  $surname=$_POST['surname'];
  $name=$_POST['name'];
  $surkanaName=$_POST['surkanaName'];
  $kanaName=$_POST['kanaName'];
  $email=$_POST['email'];
  $pass=$_POST['pass'];
  $pass_re=$_POST['pass_re'];
  $company_flg = (!empty($_POST['company_flg'])) ?  1 :  0;

  //未入力チェック
  validRequired($surname,'surname');
  validRequired($name,'name');
  validRequired($surkanaName,'surkanaName');
  validRequired($kanaName,'kanaName');
  validRequired($email,'email');
  validRequired($pass,'pass');
  validRequired($pass_re,'pass_re');

  if(empty($err_msg)){
    
    // 姓名チェック
    validName($surname,'surname');
    // 名前チェック
    validName($name,'name');

    // 姓名チェック（カナ文字）
    validkanaName($surkanaName,'surkanaName');
    // 名前チェック（カナ文字）
    validkanaName($kanaName,'kanaName');

    // メールアドレスチェック
    validEmail($email, 'email');
    //メールアドレス重複チェック
    validEmailDup($email,'email');

    // パスワード形式チェック
    validPass($pass,'pass');

    if(empty($err_msg)){

      //パスワードとパスワード再入力同値チェック
      validMatch($pass,$pass_re,'pass','pass_re');

      if(empty($err_msg)){

        //例外処理
        try{
          //DBへ接続
          $dbh=dbConnect();
          //SQL文作成 
          $sql='INSERT INTO users (surname,name,surkanaName,kanaName,email,password,company_flg,login_time,create_date) VALUES (:surname,:name,:surkanaName,:kanaName,:email,:pass,:company_flg,:login_time,:create_date)';
          //値を代入
          $data=array(':surname'=>$surname,':name'=>$name,':surkanaName'=>$surkanaName,':kanaName'=>$kanaName,':email'=>$email,':pass'=> password_hash($pass,PASSWORD_DEFAULT),':company_flg' => $company_flg,':login_time'=>date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
            //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);
          
          //ログイン有効期限（デフォルトを1時間とする）60秒*X=Y時間
          $sesLimit=60*60;
          //最終ログイン日時を現在日時にする
          $_SESSION['login_date']=time();
          $_SESSION['login_limit']=$sesLimit;
          //ユーザーIDを格納 lastInsertId();最後の番号
          $_SESSION['user_id']=$dbh->lastInsertId();

          debug('セッション変数の中身：'.print_r($_SESSION,true));
          
          $_SESSION['msg_success'] = MSG19;
          
          header("Location:mypage.php");

        } catch(Exception $e){
          error_log('エラー発生:'.$e->getMessage());
          $err_msg['common']=MSG07;
        }
      }
    }   
  }
}
?>
<?php
$siteTitle = '新規登録';
require('head.php'); 
?>
<body>
  <?php
  require('header.php');
  ?>
  <div id="main">
    <div class="signup-wrapper">
      <div class="area-msg">
        <?php 
        if(!empty($err_msg['common'])) echo $err_msg['common'];
        ?>
      </div>
      <div id="signup-block"><i class="fas fa-user-plus"></i>ユーザー登録</div>
      <form action="" method="post">
        <div id="fullname">
          <label>名字  ※10文字以内
            <input type="text" name="surname" placeholder="姓" value="<?php if(!empty($_POST['surname'])) echo $_POST['surname']; ?>">
            <div class="err_msg"><?php if(!empty($err_msg['surname'])) echo $err_msg['surname']; ?></div>
          </label>
          <label>名前  ※10文字以内
            <input type="text" name="name" placeholder="名" value="<?php if(!empty($_POST['name'])) echo $_POST['name']; ?>">
            <div class="err_msg"><?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?></div>
          </label>
        </div>
        <div id="fullname">
          <label>名字  ※10文字以内
            <input type="text" name="surkanaName" placeholder="姓（カタカナ）" value="<?php if(!empty($_POST['surkanaName'])) echo $_POST['surkanaName']; ?>">
            <div class="err_msg"><?php if(!empty($err_msg['surkanaName'])) echo $err_msg['surkanaName']; ?></div>
          </label>
          <label>名前 ※10文字以内
            <input type="text" name="kanaName" placeholder="名（カタカナ）" value="<?php if(!empty($_POST['kanaName'])) echo $_POST['kanaName']; ?>">
            <div class="err_msg"><?php if(!empty($err_msg['kanaName'])) echo $err_msg['kanaName']; ?></div>
          </label>
        </div>
        <label>メールアドレス
          <input type="email" name="email" placeholder="メールアドレス" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
          <div class="err_msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></div>
        </label>
        <label>パスワード ※半角英数8文字以上
          <input type="password" name="pass" placeholder="パスワード" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
          <div class="err_msg"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
          </div>
        </label>
        <label>パスワード（再入力）
          <input type="password" name="pass_re" placeholder="パスワード(再入力)" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re'];?> ">
          <div class="err_msg"><?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?></div>
        </label>
        <label>
          <input type="checkbox" name="company_flg">会社登録する
          <p class="flg_text">※登録後連絡掲示板が利用できます</p>
        </label>
        <input type="submit" value="登録する">
      </form>
    </div>
  </div>
  <?php
  require('footer.php');
  ?>
</body>
</html>