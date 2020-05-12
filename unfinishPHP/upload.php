<?php

//画像アップロードプログラム
//====================================-

//1.ファイルが送られてきている場合
if(!empty($file)){

  //本来は下記のようなバリデーションを行うが、今回は省略
  // バリデーション内容
  // 1.送られてきたファイルは画像ファイルか？（拡張子がjpeg,pngかどうかなどで判定）
  // 2.ファイルサイズは上限内か？（やみくもにバカでかいファイルを送ってこられると困るので、ファイルサイズ上限内か判定）

  //A.サーバーに画像を保存する
  $upload_path = 'images/'.$file['name']; // images//HNCK52
  $rst = move_uploaded_file($file['tmp_name'],$upload_path); //移動元のファイルパスと移動先のファイルパスを指定

  //B.アップロード結果によって表示するメッセージを変数へ入れる
  if($rst){
    $msg = '画像をアップしました。アップした画像ファイル名：'.$file['name'];
    $img_path = $upload_path; //表示用画像パスの変数へ画像パスを入れる
  }else{
    $msg = '画像をアップできませんでした。エラー内容：'.$file['error'];

  }

}else{

  $msg = '画像を選択してください';
}

 ?>

if(!empty($_FILES)){

$file = $_FILES['image'];

$msg = '';
$img_path = '';   //画像の定義

include('../img/upload.php');
}
<div id="main">
  <section class="site-width" style="text-align:center;">

    <p>
      ログイン完了です。
    </p>

    <p><?php if(!empty($msg)) echo $msg; ?></p>

    <h1>お気に入りの画像をアップしましょう。</h1>

    <form method="post" enctype="multipart/form-data">
      <input type="file" name="image" value="">

      <input type="submit" name="" value="アップロード">

    </form>

    <?php if(!empty($img_path)) { ?>
      <div class="img_area">
        <p>
          アップロードした画像
        </p>
        <img src="<?php echo $img_path; ?>">
      </div>
    <?php } ?>

  </section>
</div>

<?php } else { ?>

  <p>ログインしていないと見れません。</p>

<?php } ?>