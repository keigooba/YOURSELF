<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('「登録者検索ページ」');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//==========================
//画面処理
//==========================

//画面表示用データ取得
//==========================
//GETパラメータを取得
// ----------------------------
// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは1ページ目

if(!is_int((int)$currentPageNum)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php");  //トップページへ
}
// 表示件数
$listSpan = 15;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1-1)*15 = 0, 2ページ目なら(2-1)*15 = 15
// DBからユーザーデータを取得(total,total_page,data)
$dbUserRecord = getUserList($currentMinNum);

// debug('レコード情報：'.print_r($dbUserRecord,true));

debug('画面表示処理終了
<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '登録者検索ページ';
require('head.php');
?>
<body>
  <?php
  require('header.php');
  ?>
  <!-- メインコンテンツ -->
  <div id="main" class="site-width">
    <div class="search-title">
      <div class="search-left">
        <span class="total-num"><?php echo sanitize($dbUserRecord['total']); ?></span>件のユーザーが登録しています。
      </div>
      <div class="search-right">
        <span class="num"><?php echo (!empty($dbUserRecord['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo sanitize($dbUserRecord['total']); ?></span>件中
      </div>
    </div>
    <div id="card-list">
      <?php
        foreach($dbUserRecord['data'] as $key => $val) :
      ?>
      <div class="card card-skin">
        <a href="userDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&u_id='.$val['id'] : '?u_id='.$val['id']; ?>" class="card-panel">
          <div class="card__imgframe">
            <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="<?php echo sanitize($val['name']); ?>" class="img">
          </div>
          <div class="card__textbox">
            <div class="card__text">
              <span class="age"><?php echo sanitize($val['surname']); ?></span>
              <span class="name"><?php echo sanitize($val['name']); ?></span>
            </div>
          </div>
        </a>
      </div> 
      <?php
        endforeach;
        ?>   
    </div>
    <?php pagination($currentPageNum,$dbUserRecord['total_page']); ?>
  </div>

  <?php
  require('footer.php');
  ?>
  <script>
    $(window).on('load',function(){
      $('#js-fadeIn-card').fadeIn('slow');
    });
  </script>
</body>
</html>
