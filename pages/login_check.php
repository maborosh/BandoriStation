<?php
if (isset($_SESSION['login'])) {
    header('location: /');
    exit();
}

if(isset($_POST['submit']) and $_POST['submit'] == '登录' and isset($_POST['username']) and isset($_POST['password'])) {
    if ($_POST['username'] == '' or $_POST['password'] == '') {
        $msg = '请输入账号或密码';
    } else {
        $dbh_bandori_station = db_select('bandori_station');
        if (filter_var($_POST['username'], FILTER_VALIDATE_EMAIL)) {
            $sql_text = "SELECT user_id, password, avatar FROM website_account WHERE email = '" . $_POST['username'] . "'";
        } else {
            $sql_text = "SELECT user_id, password, avatar FROM website_account WHERE username = '" . $_POST['username'] . "'";
        }
        $sth = $dbh_bandori_station->prepare($sql_text);
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        $transfer_user_flag = false;
        if (!$sql_result) {
            $msg = '用户名或电子邮件不存在';
        } else {
            $timestamp = time();
            $lock_flag = false;
            if (isset($_SESSION['login_times'])) {
                if ($_SESSION['login_times'] > 5) {
                    $msg = '登录次数过多，请稍后再试';
                    $lock_flag = true;
                }
            } else {
                $_SESSION['login_times'] = 1;
            }
            if (!$lock_flag) {
                $password_hash = generate_password_cipher($sql_result['user_id'], $_POST['password']);
                if ($sql_result['password'] == $password_hash) {
                    if (binding_check($sql_result['user_id'])) {
                        $_SESSION['login'] = true;
                        $_SESSION['user_id'] = $sql_result['user_id'];
                        $_SESSION['login_times'] = 1;
                        update_user_login_data($sql_result['user_id'], $sql_result['password'], $sql_result['avatar']);
                        header('location: /');
                    } else {
                        $_SESSION['user_id'] = $sql_result['user_id'];
                        header('location: ?verify_email');
                    }
                    exit();
                } else {
                    $msg = '密码错误，请重新登录';
                    $_SESSION["login_times"] += 1;
                }
            }
        }
    }
} else {
    $msg = '提交失败';
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta http-equiv="refresh" content="3;URL=?login">
    <?php include ROOT_PATH . '/components/html_head.html'; ?>
    <script src="js/login.js"></script>
</head>
<body>
<?php
include ROOT_PATH . '/components/navigation_bar_only_brand.html';
include ROOT_PATH . '/components/other_components.html';
?>
<div class="content">
    <div class="section-container">
        <div class="section-title">提示</div>
        <div class="section-content"><?php echo $msg; ?></div>
    </div>
</div>
</body>
</html>