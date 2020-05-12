<?php
error_reporting(E_ALL); //全てのエラーを報告する
ini_set('display_errors','On');  //画面にエラーを表示させるか

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「仕事探しページ」');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//==============================
//画面処理
//==============================
//DBからユーザーデータを取得

$title1=(!empty($_POST['title1'])) ? $_POST['title1']:'登録されていません';
$title2=(!empty($_POST['title2'])) ? $_POST['title2']:'登録されていません';
$title3=(!empty($_POST['title3'])) ? $_POST['title3']:'登録されていません';

$text1=(!empty($_POST['text1'])) ? $_POST['text1']:'未入力です。';
$text2=(!empty($_POST['text2'])) ? $_POST['text2']:'未入力です。';
$text3=(!empty($_POST['text3'])) ? $_POST['text3']:'未入力です。';


?>
<?php
$siteTitle='仕事探し';
require('head.php');
?>
<body>
    <?php
    require('header.php');
    ?>
    <div id="main" class="site-width">
        <div class="container">
            <div class="work">
                <h1><?php echo $title1; ?></h1>
                <div class="company-wrapper">
                    <div class="img_area">
                        <img src="<?php if(!empty($img_path3)) echo $img_path3; ?>">
                        <p>会社の写真</p>   
                    </div>
                    <div class="text_area">
                        <p><?php echo $text3; ?></p>
                    </div>
                </div>
                <div class="btn_area">
                    <ul>
                        <li>
                            <a href="contact.php" class="btn btn-comment">話を聞いてみたい</a>
                        </li>
                        <li>
                            <a href="application.php" class="btn btn-application">応募する</a>
                        </li>
                    </ul>
                </div>
            </div>  
            <div class="work">
                <h1><?php echo $title2; ?></h1>
                <div class="company-wrapper">
                    <div class="img_area">
                        <img src="<?php if(!empty($img_path3)) echo $img_path3; ?>">
                        <p>会社の写真</p>   
                    </div>
                    <div class="text_area">
                        <p><?php echo $text3; ?></p>
                    </div>
                </div>
                <div class="btn_area">
                    <ul>
                        <li>
                            <a href="contact.php" class="btn btn-comment">話を聞いてみたい</a>
                        </li>
                        <li>
                            <a href="application.php" class="btn btn-application">応募する</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="work">
                <h1><?php echo $title3; ?></h1>
                <div class="company-wrapper">
                    <div class="img_area">
                        <img src="<?php if(!empty($img_path3)) echo $img_path3; ?>">
                        <p>会社の写真</p>   
                    </div>
                    <div class="text_area">
                        <p><?php echo $text3; ?></p>
                    </div>
                </div>

                <div class="btn_area">
                    <ul>
                        <li>
                            <a href="contact.php" class="btn btn-comment">話を聞いてみたい</a>
                        </li>
                        <li>
                            <a href="application.php" class="btn btn-application">応募する</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        require('sidebar.php');
        ?>
    </div>
    </div>
<?php
require('footer.php');
?>

