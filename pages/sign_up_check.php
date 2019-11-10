<?php
if (isset($_SESSION['login'])) {
    header('location: /');
    exit();
}

if(isset($_POST['submit']) and $_POST['submit'] == '注册' and isset($_POST['username']) and isset($_POST['password']) and isset($_POST['email'])) {
    if ($_POST['username'] == '' or $_POST['password'] == '') {
        $msg = '请输入账号或密码';
    } elseif (filter_var($_POST['username'], FILTER_VALIDATE_EMAIL)) {
        $msg = '用户名不能是邮箱地址';
    } elseif (mb_strlen($_POST['password']) < 6) {
        $msg = '密码不得小于6位';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $msg = '错误的邮箱地址';
    } else {
        $lock_flag = false;
        if (isset($_SESSION['sign_up_times'])) {
            if ($_SESSION['sign_up_times'] > 5) {
                $msg = '注册次数过多，请稍后再试';
                $lock_flag = true;
            }
        } else {
            $_SESSION['sign_up_times'] = 1;
        }
        if (!$lock_flag) {
            $dbh_bandori_station = db_select('bandori_station');
            $sth = $dbh_bandori_station->prepare("SELECT username FROM website_account WHERE username = '" . $_POST['username'] . "' OR email = '" . $_POST['email'] . "'");
            $sth->execute();
            $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
            if ($sql_result) {
                $msg = '该用户名或邮箱已被注册';
            } else {
                $timestamp = time();
                $sth = $dbh_bandori_station->prepare("SELECT MAX(user_id) FROM website_account");
                $sth->execute();
                $max_user_id_result = $sth->fetch(PDO::FETCH_ASSOC);
                if ($max_user_id_result) {
                    $user_id = $max_user_id_result['MAX(user_id)'] + 1;
                } else {
                    $user_id = 1;
                }
                $password_hash = generate_password_cipher($user_id, $_POST['password']);
                $sth = $dbh_bandori_station->prepare("INSERT INTO website_account(user_id, username, password, email, sign_up_time, avatar) VALUES ($user_id, '" . $_POST['username'] . "', '" . $password_hash . "', '" . $_POST['email'] . "', $timestamp, '')");
                $sth->execute();
                $_SESSION['user_id'] = $user_id;
                header('location: ?verify_email');
                exit();
            }
        }
        $_SESSION['sign_up_times'] += 1;
    }
} else {
    $msg = '提交失败';
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta http-equiv="refresh" content="3;URL=?sign_up">
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