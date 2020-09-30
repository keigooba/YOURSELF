<footer id="footer">
    Copyright <a href="../index.html">YOURSELF</a>.All Rights Reserved.
    <p>大庭慶吾のポートフォリオ</p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
<script>
$(function(){

  //フッターを最下部に固定
  var $ftr = $('#footer'); //画面表示が小さいと表示される
  if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight()){
    $ftr.attr({'style':'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
  }
  // メッセージ表示
  var $jsShowMsg = $('#js-show-msg'); //jqueryでDOMを取得する（頭に＄をつける）
  var msg = $jsShowMsg.text(); //中身のメッセージを取り出す
  if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
    $jsShowMsg.slideToggle('slow');
    setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
  }

  // 画像ライブプレビュー
  var $dropArea = $('.area-drop');
  var $fileInput = $('.input-file');
  $dropArea.on('dragover', function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '3px #ccc dashed');
  });
  $dropArea.on('dragleave', function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', 'none');
  });
  $fileInput.on('change', function(e){
    $dropArea.css('border', 'none');
    var file = this.files[0],  //2.files配列にファイルが入っています
        $img = $(this).siblings('.prev-img'), //3.jqeryのsiblingメソッドで兄弟のimgを取得
        fileReader = new FileReader(); //4.ファイルを読み込むFileReaderオブジェクト

    // 5.読み込みが完了した歳のイベントハンドラ.imgのsrcにデータをセット
    fileReader.onload = function(event) {
        $img.attr('src', event.target.result).show();
    };

    // 6.画像読み込み
    fileReader.readAsDataURL(file);

  });
  /// お気に入り登録・削除
  var $like,
          likeEntryuserId;
  $like = $('.js-click-like') || null; //nullというのはnull値という値で、「変数の中身は空ですよ」と明示するためにつかう値
  likeEntryuserId = $like.data('entryuser_id') || null;
  // 数値の0はfalseと判定されてしまう。entryuser_idが0の場合もありえるので、0もtrueとする場合にはundefinedとnullを判定する
  if(likeEntryuserId !== undefined && likeEntryuserId !== null){
    $like.on('click',function(){
    var $this = $(this);
    $.ajax({
        type: "POST",
        url: "AjaxLike.php",
        data: { EntryuserId : likeEntryuserId}
    }).done(function( data ){   //.done成功
        console.log('Ajax Success');
        // クラス属性をtoggleでつけ外しする
        $this.toggleClass('active');
    }).fail(function( msg ) {   //.fail失敗
        console.log('Ajax Error');
    });
    });
  }
});

</script>
