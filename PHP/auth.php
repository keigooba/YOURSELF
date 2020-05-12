<?php

//===============================
// ログイン認証・自動ログアウト
// ログインしている場合
//ログインしていたら任意のページへ
//ログインしていなかったらログインページへ

if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです。'); 

    //現在日時が最終ログイン日時＋有効期限を超えていた場合 SESSION['login_date']に入っているのはtimeに記録した日時 それにlogin_limit(60*60)を足す
    if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
        debug('ログイン有効期限オーバーです。');    
        //セッションを削除（ログアウトする）
        session_destroy();
        //ログインページへ
        header("Location:login.php");
    }else{
        debug('ログイン有効期限以内です。');
        //最終ログイン日時を現在日時に更新
        $_SESSION['login_date']=time();
        
        //これはログイン画面に入ったときログイン有効期限内であればログイン有効期限を更新してマイページへ自動で飛ばす処理
        //$_SERVER['PHP_SELF']はドメインからのパスを返す わかりやすく言うと現在のパス 例えばYOURSELF/singup.phpに接続したとするとその相対パスを取得できる
        //更にbasename関数を使うことでファイル名だけを取り出せるということ
        if(basename($_SERVER['PHP_SELF']) ==='login.php'){
            debug('マイページへ遷移します。');
            header("Location:mypage.php"); //マイページへ
        }
    }

}else{
    debug('未ログインユーザーです。');
    if(basename($_SERVER['PHP_SELF']) !=='login.php'){
        header("Location:login.php"); //ログインページへ
    }
}