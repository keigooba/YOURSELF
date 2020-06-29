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
//年代別
$age_id = (!empty($_GET['age_id'])) ? $_GET['age_id'] : '';
//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
//カテゴリー
$category =(!empty($_GET['c_id'])) ? $_GET['c_id'] : '';

if(!is_int((int)$currentPageNum)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php");  //トップページへ
}
// 表示件数
$listSpan = 20;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1-1)*15 = 0, 2ページ目なら(2-1)*15 = 15
// DBからユーザーデータを取得(total,total_page,data)
$dbUserRecord = getUserList($currentMinNum, $category, $age_id, $sort);
// debug('レコード情報：'.print_r($dbUserRecord,true));

//DBからカテゴリーデータを取得
$dbCategoryData = getCategory();
// debug('カテゴリーデータ：'.print_r($dbCategoryData,true));

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
  <form name="" method = "get">
    <div class="search-title">
      <span class="total-num"><?php echo sanitize($dbUserRecord['total']); ?></span>件のユーザーが登録しています。
      <span class="select-title">前職</span>
      <div class="selectbox">
        <select name="c_id" id="">
          <option value="0" <?php if(getFormData('c_id') == 0){
          echo 'selected'; } ?> >前職</option>
          <?php
          foreach($dbCategoryData as $key => $val){
          ?>
            <option value="<?php echo $val['id'] ?>" <?php if(getFormData('c_id') == $val['id'] ){ echo 'selected'; } ?> >
            <?php echo $val['name']; ?>
            </option>
          <?php
            }
          ?>
        </select>
      </div>
      <span class="select-title">年代別</span>
      <div class="selectbox">
        <select name="age_id">
          <option value="0" <?php if(getFormData('age_id',true) == 0){
          echo 'selected'; } ?> >年代</option>
          <option value="10" <?php if(getFormData('age_id',true) == 1 ){
            echo 'selected';} ?> >〜19歳</option>
          <option value="20" <?php if(getFormData('age_id',true) == 2){
            echo 'selected'; } ?> >20~29歳</option>
          <option value="30" <?php if(getFormData('age_id',true) == 3){
            echo 'selected'; } ?> >30~39歳</option>
          <option value="40" <?php if(getFormData('age_id',true) == 4){
            echo 'selected'; } ?> >40~49歳</option>
          <option value="50" <?php if(getFormData('age_id',true) == 5){
            echo 'selected'; } ?> >50~59歳</option>
          <option value="60" <?php if(getFormData('age_id',true) ==6){
            echo 'selected'; } ?> >60歳~</option>
        </select>
      </div>
      <span>表示順</span>
      <div class="selectbox">
        <select name="sort">
          <option value="0" <?php if(getFormData('sort',true) == 0 ){
            echo 'selected'; } ?> >表示</option>
          <option value="1" <?php if(getFormData('sort',true) == 1 ){
            echo 'selected'; } ?> >年齢が低い順</option>
          <option value="2" <?php if(getFormData('sort', true) == 2 ){
            echo 'selected'; } ?> >年齢が高い順</option>
        </select>
      </div>
      <input type="submit" value="検索">
      <span class="search-number">
        <span class="num"><?php echo (!empty($dbUserRecord['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo sanitize($dbUserRecord['total']); ?></span>件中
          </span>
    </div>
  </form>
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
