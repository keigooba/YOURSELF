<header>
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