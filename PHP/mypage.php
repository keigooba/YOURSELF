<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「マイページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

// =================================
// 画面表示処理
// ==================================
$u_id = $_SESSION['user_id'];
// DBから連絡掲示板データを取得
$bordData = getMyMsgsAndBord($u_id);

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle='マイページ';
require('head.php');
?>
<!-- メニュー -->

<body class="page-2colum">
  <?php
  require('header.php');
  ?>
  <p id="js-show-msg" style="display:none;" class="msg-slide">
      <?php echo getSessionFlash('msg_success'); ?>
  </p>
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <h1 class="page-title">マイページ</h1>
    
    <section id="main">
      <section class="list list-table">
        <h2 class="list-title">
          メッセージリスト
        </h2>
        <table class="table">
          <thead>
            <tr>
              <th>最新送信日時</th>
              <th>メッセージ</th>
            </tr>
          <thead>
          <tbody>
            <?php
            if(!empty($bordData)){
              foreach($bordData as $key => $val){
                if(!empty($val['msg'])){
                  $msg = array_shift($val['msg']);
            ?>
                <tr>
                    <td><?php echo sanitize(date('Y.m.d H:i',  strtotime($msg['create_date']))); ?></td>
                    <td><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>"><?php echo mb_substr(sanitize($msg['msg']),0,40); ?>...</a></td>
                </tr>
            <?php
                }else{
            ?> 
                <tr>
                    <td>--</td>
                    <td><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>">まだメッセージはありません</a></td>
                </tr>
              <?php
                  }
                }
              }
            ?>
          </tbody>
        </table>
      </section>   
      <?php
      require('sidebar.php');
      ?>
    </section>

  </div>
<?php
require('footer.php');
?>
