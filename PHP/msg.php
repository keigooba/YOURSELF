<?php
//  共通関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('「連絡掲示板ページ」');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ====================================
// 画面処理
// ＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝

// 画面表示用データ取得
// ＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
// GETパラメータを取得
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
// DBから掲示板とメッセージデータを取得
$viewData = getMsgsAndBord($m_id);
// パラメータに不正な値が入っているかチェック
if(empty($viewData)){
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:mypage.php"); //マイページへ
}
//viewDataから相手のユーザーIDを取り出す
$dealUserIds[] = $viewData[0]['entry_user'];
$dealUserIds[] = $viewData[0]['company_user'];
if(($key = array_search($_SESSION['user_id'], $dealUserIds)) !== false) {
  unset($dealUserIds[$key]);
}
$partnerUserId = array_shift($dealUserIds);
debug('取得した相手のユーザーID：'.$partnerUserId);
// DBから取引相手のユーザー情報を取得
if(!empty($partnerUserId)){
  $partnerUserInfo = getUser($partnerUserId);
}
// 相手のユーザ情報が取れたかチェック
if(empty($partnerUserInfo)){
  error_log('エラー発生：相手のユーザー情報が取得できませんでした');
  header("Location:mypage.php"); //マイページへ
}
debug('取得した相手のユーザーデータ：'.print_r($partnerUserInfo,true));
// DBから自分のユーザー情報を取得
$myUserInfo = getUser($_SESSION['user_id']);
// 自分のユーザー情報が取れたかチェック
if(empty($myUserInfo)){
  error_log('エラー発生：自分のユーザー情報が取得できませんでした');
  header("Location:mypage.php"); //マイページへ
}
debug('取得した自分のユーザーデータ：'.print_r($myUserInfo,true));

//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');

  // ログイン認証
  require('auth.php');

  // バリデーションチェック
  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
  // 最大文字数チェック
  validMaxLen($msg,'msg');
  // 未入力チェック
  validRequired($msg,'msg');

  if(empty($err_msg)){
    debug('バリデーションOKです。');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO message (bord_id, send_date, send_time, to_user,from_user, msg, create_date) VALUES(:b_id, :send_date, :send_time, :to_user, :from_user, :msg, :date)';
      $data = array(':b_id' =>$m_id, ':send_date' =>date('Y-m-d'), ':send_time' =>date('H:i:s'),  ':to_user' => $partnerUserId, ':from_user' => $_SESSION['user_id'], ':msg' => $msg, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh,$sql,$data);

      //クエリ成功の場合
      if($stmt){
        $_POST = array(); //postをクリア
        debug('連絡掲示板へ遷移します。');
        header("Location:".$_SERVER['PHP_SELF'] .'?m_id='.$m_id);
        // 自分自身に遷移する
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

debug('画面表示処理終了
<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '連絡掲示板';
require('head.php');
?>
<body>
  <header style="margin-bottom:inherit;">
      <div id="header-container" class="site-width">
          <div class="header-logo">
              <a href="index.php"><img src="../img/yourself-logo.png"></a>
          </div>
          <nav id="top-nav">
              <ul>
                  <?php
                      if(empty($_SESSION['user_id'])){
                  ?>
                      <li><a href="login.php"><i class="fas fa-sign-in-alt"></i>ログイン</a></li>
                      <li><a href="signup.php"><i class="fas fa-user-plus"></i>ユーザー登録</a></li>
                  <?php
                      }else{
                  ?>
                      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>ログアウト</a></li>
                      <li><a href="mypage.php"><i class="fas fa-portrait"></i>マイページ</a></li>
                  <?php
                      }
                  ?>
              </ul>
          </nav>
      </div>
  </header>
  <div id="main" style="margin-bottom:inherit;">
    <div class="top-msg">
      <a href="userDetail.php?u_id=<?php echo appendGetParam(array('m_id')); ?><?php echo $partnerUserId ?>" class="back-arrow"><i class="fas fa-chevron-left fa-4x"></i></a>
      <h1 class="title">連絡掲示板</h1>
    </div>
    <div class="msg-container">
      <div id="js-slideDown-msg" class="msg-show">
        <p><?php echo getSessionFlash('msg_time'); ?> <br><?php echo getSessionFlash('msg_username'); ?>
        <?php echo getSessionFlash('msg_success'); ?></p>
      </div>
      <div class="area-bord" id="js-scroll-bottom">
        <?php
          if(!empty($viewData)){
            foreach($viewData as $key => $val){
              if(!empty($val['from_user']) && $val['from_user'] == $partnerUserId){
        ?>
          <div class="msg-cnt msg-left">
            <img src="<?php echo sanitize(showImg($partnerUserInfo['pic'])); ?>" class="avatar">
            <div class="msg-info">
              <span class="avatar-name">
                <?php echo sanitize($partnerUserInfo['name']); ?>
              </span>
              <p class="msg-inrText" id="js-replace-msg">
                <?php echo sanitize($val['msg']); ?>
              </p>
            </div>
            <div class="msg-datetime">
              <p><?php echo sanitize(date('m.d', strtotime($val['send_date']))); ?><br>
              <?php echo sanitize(date('H:i', strtotime($val['send_time']))); ?></p>
            </div>
          </div>
        <?php
              }else{
        ?>
          <div class="msg-cnt msg-right">
            <img src="<?php echo showImg(sanitize($myUserInfo['pic'])); ?>" class="avatar">
            <div class="msg-info">
              <span class="avatar-name">
                <?php echo sanitize($myUserInfo['name']); ?>
              </span>
              <p class="msg-inrText">
                <?php echo sanitize($val['msg']); ?>
              </p>
            </div>
            <div class="msg-datetime">
              <p><?php echo sanitize(date('m.d', strtotime($val['send_date']))); ?><br>
                <?php echo sanitize(date('H:i', strtotime($val['send_time']))); ?></p>
            </div>
          </div>
        <?php
            }
          }
        }else{
        ?>
          <p style="text-align:center; line-height:20;">メッセージ投稿はまだありません</p>
        <?php
          }
        ?>
      </div>
    </div>
    <div class="area-send-msg">
      <form action="" method="post">
        <input name="msg" class="textlines" placeholder="メッセージを入力">
        <input type="submit" value="&#xf0da;" class="fas">
      </form>
    </div>
  </div>
  <?php
  require('footer.php');
  ?>
  <script>
    $(function(){
      //scrollHeightは要素のスクロールビューの高さを取得するもの
      $('#js-scroll-bottom').animate({scrollTop: $('#js-scroll-bottom')[0].scrollHeight}, 'fast');
    });
    //メッセージ表示
    var $jsShowMsg = $('#js-slideDown-msg'); //jqueryでDOMを取得する（頭に＄をつける）
    var msg = $jsShowMsg.text(); //中身のメッセージを取り出す
    if(msg.replace(/^[\s　]+|[\s　]+$/g,"").length){
      $jsShowMsg.slideDown('slow');
    }
  </script>
</body>
</html>
