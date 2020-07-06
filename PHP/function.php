<?php
//================================
// ログ
//================================
// composerのライブラリの認識
require __DIR__ . '/vendor/autoload.php'; // path to vendor/
// require_once _DIR_ . '/vendor/autoload.php';
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');
//デバッグモードへ切り替え
ini_set( 'display_errors', 1 );
// 時間設定
date_default_timezone_set('Asia/Tokyo');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = false;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}

//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug( 'ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
  }
}

//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01','※入力必須です');
define('MSG02', '※Emailの形式で入力してください');
define('MSG03','※パスワード（再入力）が合っていません');
define('MSG04','※半角英数字のみご利用いただけます');
define('MSG05','※8文字以上で入力してください');
define('MSG06','※256文字以内で入力してください');
define('MSG07','※エラーが発生しました。入力内容を見直してください。');
define('MSG08', '※そのEmailは既に登録されています');
define('MSG09', '※名前またはメールアドレスまたはパスワードが違います');
define('MSG10','※漢字入力');
define('MSG11','※10文字以内');
define('MSG12','※カタカナ入力');
define('MSG13','※電話番号の形式が違います');
define('MSG14','※郵便番号の形式が違います');
define('MSG15','※現在のパスワードが違います');
define('MSG16','現在のパスワードと同じです');
define('MSG17','文字で入力してください');
define('MSG18','※プロフィール編集で会社登録してください');
define('MSG19','登録ありがとうございます!登録完了しました');
define("MSG20",'正しくありません');
define('SUC01','パスワードを変更しました');
define('SUC02','プロフィールを変更しました');
define('SUC03','メールを送信しました');
define('SUC04','が参加しました。');
define('SUC05','ゲストユーザーは使用出来ません。');

//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================

//バリデーション関数（未入力チェック）
function validRequired($str, $key){
  if(empty($str)){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
//バリデーション関数（Email形式チェック）
function validEmailFormat($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
//バリデーション関数（Email重複チェック）新しくデータを登録する場合のみ
function validEmailDup($email){
  global $err_msg;
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
// バリデーション関数（会社登録チェック）
function validUserCompany($u_id){
  global $err_msg;
  //  例外処理
  try {
    // DBへの接続
    $dbh = dbConnect();
    // SQL文の作成
    $sql = 'SELECT count(*) FROM users WHERE company_flg = 1 AND id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);
    //クエリ結果の値を取得
    $rst = $stmt->fetch(PDO::FETCH_ASSOC);
    // 結果が空のとき
    if(empty(array_shift($rst))){
      $err_msg['common'] = MSG18;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' .$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
//バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 8){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
//バリデーション関数（最大文字数チェック）DBでエラーする前に止める
function validMaxLen($str, $key, $max = 256){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}
//バリデーション関数（半角チェック）
function validHalf($str, $key){
  if(!preg_match("/[a-zA-Z0-9]/", $key)){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
//バリデーション関数(漢字チェック）
function validCharacters($str,$key){
    if(!preg_match("/([\x{3005}\x{3007}\x{303b}\x{3400}-\x{9FFF}\x{F900}-\x{FAFF}\x{20000}-\x{2FFFF}])(.*|)/u",$str)){
      $err_msg[$key] = MSG10;
    }
}
//バリデーション関数（名前最大文字数チェック）
function validNameMixLen($str,$key,$max=10){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key]=MSG11;
    }
}
//バリデーション関数（カナ文字チェック）
function validkanaCharacters($str,$key){
  if(!preg_match("/^[ァ-ヶー]+$/u",$str)){
    global $err_msg;
    $err_msg[$key] = MSG12;
  }
}
//電話番号チェック
function validTel($str, $key){
  if(!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG13;
  }
}
//郵便番号形式チェック
function validZip($str,$key){
  if(!preg_match("/^\d{7}$/",$str)){
    global $err_msg;
    $err_msg[$key]=MSG14;
  }
}
//半角数字チェック
function validNumber($str,$key){
  if(!preg_match("/^[0-9]+$/",$str)){
    global $err_msg;
    $err_msg[$key]=MSG11;
  }
}
//文字数チェック
function validLength($str, $key, $len = 8){
  if(mb_strlen($str) !== $len ){
    global $err_msg;
    $err_msg[$key] = $len . MSG17;
  }
}
// 姓名・名前チェック
function validName($str, $key){
  //追加機能 漢字チェック
  validCharacters($str, $key);
  //最大文字数チェック（名前）
  validNameMixLen($str, $key);
}
// 姓名・名前チェック(カナ文字)
function validkanaName($str, $key){
  //カナ文字チェック
  validkanaCharacters($str,$key);
  //最大文字数チェック（名前）
  validNameMixLen($str, $key);
}
// メールアドレスチェック
function validEmail($str,$key){
  // emailの形式チェック
  validEmailFormat($str, $key);
  //emailの最大文字数チェック
  validMaxLen($str, $key);
}
// パスワードチェック
function validPass($str, $key){
  //パスワード半角英数チェック
  validHalf($str, $key);
  //パスワード最大文字数チェック
  validMaxLen($str, $key);
  //パスワード最小文字数チェック
  validMinLen($str, $key);
}
//selectboxチェック
function validSelect($str, $key) {
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}
// エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}
// ＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
// ログイン認証
// ＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
function isLogin(){
  //ログインしている場合
  if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです。');

    //現在日時が最終ログイン日時＋有効期限を超えていた場合
    if($_SESSION['login_date'] + $_SESSION['login_limit'] < time()){
      debug('ログイン有効期限オーバーです。');

      //セッションを削除(ログアウトする)
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です。');
      return true;
    }

  }else{
    debug('未ログインユーザーです。');
    return false;
  }
}
//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
  $db = parse_url($_SERVER['CLEARDB_DATABASE_URL']);
  $db['dbname'] = ltrim($db['path'], '/');
  $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8";
  $user = $db['user'];
  $password = $db['pass'];
  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY =>true,
  );
  $dbh = new PDO($dsn,$user,$password,$options);
  return $dbh;
}
//SQL実行関数 例外処理をしていることを強調するため否定形
function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  // SOL実行
  if($stmt->execute($data)){
    debug('クエリ成功。');
    return $stmt;
  }
  debug('クエリ失敗しました。');
  $err_msg['common'] = MSG07;
  return 0;
}
function getUser($u_id){
  debug('ユーザー情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users  WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果のデータを1レコード返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
function getUserList($currentMinNum = 1, $category, $age_id, $sort, $span = 20){
  debug('ユーザー情報を取得します。');
  //例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM users WHERE delete_flg = 0';
    if(!empty($category)) $sql .= ' AND category_id = '.$category;
    if(!empty($age_id)){
      switch($age_id){
        case 10:
          $sql .= ' AND age < 20 ';
          break;
        case 20:
          $sql .= ' AND age >= 20  AND age < 30';
          break;
        case 30:
          $sql .= ' AND age >= 30 AND age < 40';
          break;
        case 40:
          $sql .= ' AND age >= 40 AND age < 50';
          break;
        case 50:
          $sql .= ' AND age >= 50 AND age < 60';
          break;
        case 60:
          $sql .= ' AND age >= 60';
          break;
      }
    }
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY age ASC';
          break;
        case 2:
          $sql .= ' ORDER BY age DESC';
          break;
      }
    }
    $data = array();
    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数 ceil繰り上げ
    debug(print_r($rst['total'],true));
    debug(print_r($rst['total_page'],true));
    if(!$stmt){
      return false;
    }
    //ページング用のSQL文作成
    $sql = 'SELECT * FROM users WHERE delete_flg = 0';
    if(!empty($category)) $sql .= ' AND category_id = '.$category;
    if(!empty($age_id)){
      switch($age_id){
        case 10:
          $sql .= ' AND age < 20 ';
          break;
        case 20:
          $sql .= ' AND age >= 20 AND age < 30';
          break;
        case 30:
          $sql .= ' AND age >= 30 AND age < 40';
          break;
        case 40:
          $sql .= ' AND age >= 40 AND age < 50';
          break;
        case 50:
          $sql .= ' AND age >= 50 AND age < 60';
          break;
        case 60:
          $sql .= ' AND age >= 60';
          break;
      }
    }
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY age ASC';
          break;
        case 2:
          $sql .= ' ORDER BY age DESC';
          break;
      }
    }
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL:'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      // クエリ結果のデータの全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }
  } catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getMsgsAndBord($id){
  debug('msg情報を取得します。');
  debug('掲示板ID：'.$id);
  // 例外処理
  try {
    // DBへの接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT m.id AS m_id, bord_id, send_date, send_time, to_user, from_user, entry_user, company_user, msg, b.create_date FROM message AS m RIGHT JOIN bord AS b ON b.id = m.bord_id WHERE b.id = :id AND b.delete_flg = 0 ORDER BY create_date ASC';
    $data = array(':id' => $id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：' .$e->getMessage());
  }
}
function getMyMsgsAndBord($u_id){
  debug('自分のmsg情報を取得します。');
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();

    // SQL文作成
    $sql = 'SELECT * FROM bord AS b WHERE b.entry_user = :id OR b.company_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    if(!empty($rst)){
      foreach($rst as $key => $val){
        //SQL文作成
        $sql = 'SELECT m.id, m.bord_id, m.send_date, m.send_time, m.to_user, m.from_user, m.msg, m.create_date, m.update_date FROM message AS m WHERE m.bord_id = :id AND m.delete_flg = 0 ORDER BY m.create_date DESC';

        $data = array(':id' => $val['id']);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }
    if($stmt){
      // クエリ結果の全データを返却
      return $rst;
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getCategory(){
  debug('カテゴリー情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM category';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e){
    error_log('エラー発生：' . $e->getMessage());
  }
}
function isLike($u_id, $e_id){
  debug('お気に入り情報があるか確認します。');
  debug('ユーザーID：'.$u_id);
  debug('登録ユーザーのID：'.$e_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM `like` WHERE entryuser_id = :e_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':e_id' => $e_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt->rowCount()){
      debug('お気に入りです');
      return true;
    }else{
      debug('特に気に入ってません');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getMyLike($u_id){
  debug('自分のお気に入り情報を取得します。');
  debug('ユーザーID：'.$u_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM `like` AS l LEFT JOIN users AS u ON l.entryuser_id = u.id WHERE l.user_id = :u_id';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//===============================
// メール送信
//===============================
function sendMail($from, $to, $subject, $comment){
  if(!empty($to) && !empty($subject) && !empty($comment)){
    // 文字化けしないように設定（お決まりパターン）
    mb_language("Japanese"); //現在使っている言語を設定する
    mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定
    // $sendgrid = new \SendGrid(getenv('app170880272@heroku.com'), getenv('o9sdvb974837'));
    // $email = new \SendGrid\Email();
    // $email->addTo($to)->
    //     setFrom($from)->
    //     setSubject($subject)->
    //     setText($comment);

    // $sendgrid->send($email);

    // メールを送信（送信結果はtrueかfalseで返ってくる）
    $result = mb_send_mail($to,$subject,$comment,"From:".$from);
    // 送信結果を判定
    if($result) {
      debug('メールを送信しました。');
    }else {
      debug('【エラー発生】メールの送信に失敗しました。');
    }
  }
}

//==================================
//その他
//==================================
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}
// フォーム入力保持
function getFormData($str){
  global $dbFormData;
  // ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($_POST[$str])){//金額や郵便番号などのフォームで数字や数値の0が入っている場合もあるので、issetを使うこと
        return $_POST[$str];
      }else{
        //ない場合（フォームにエラーがある＝POSTされてるハズなので、まずありえないが）はDBの情報を表示
        return $dbFormData[$str];
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合（このフォームも変更していてエラーはないが、他のフォームでひっかかっている状態）
      if(isset($_POST[$str]) && $_POST[$str] !== $dbFormData[$str]){
        return $_POST[$str];
      }else{//そもそも変更していない
        return $dbFormData[$str];
      }
    }
  }else{
    if(isset($_POST[$str])){
      return $_POST[$str];
    }
  }
}
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = ''; //これで中身を空にする
    return $data;
  }
}
// 認証キー生成
function makeRandkey($length = 8){
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; ++$i) {
    $str .= $chars[mt_rand(0,61)];
  }
  return $str;
}
// 画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));
  var_dump('kokoha1');
  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
          case UPLOAD_ERR_OK: // OK
              break;
          case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
              throw new RuntimeException('ファイルが選択されていません');
          case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
          case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
              throw new RuntimeException('ファイルサイズが大きすぎます');
          default: // その他の場合
              throw new RuntimeException('その他のエラーが発生しました');
      }
      var_dump('kokoha2');
      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = exif_imagetype($file['tmp_name']);
      var_dump('kokomade1');
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
        var_dump('kokomade2');
          throw new RuntimeException('画像形式が未対応です');
        var_dump('kokomade3');
      }
      var_dump('kokoha3');
      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = '../uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
          throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      var_dump('kokoha4');
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);
      var_dump('kokoha5');
      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {
      var_dump($e->getMessage());
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じかつ総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}
//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}
/* 画面表示陽関数 */
function showImg($path){
  if(empty($path)){
    return '../img/sample-img.png';
  }else{
    return $path;
  }
}
// ゲストユーザーチェック関数
function GuestUser(){
  $_SESSION['msg_success'] = SUC05;
  debug('ゲストユーザーは使用出来ません。');
}
