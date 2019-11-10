<?php
if (isset($_SESSION['login'])) {
    header('location: /');
    exit();
} elseif (!isset($_SESSION['user_id'])) {
    header('location: /');
    exit();
}

if (binding_check($_SESSION['user_id'])) {
    $_SESSION['login'] = true;
    update_user_login_data($_SESSION['user_id'], null, null);
    header('location: /');
    exit();
} else {
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT email FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    $email = $sql_result['email'];
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <?php include ROOT_PATH . '/components/html_head.html'; ?>
    <script src="js/login.js"></script>
</head>
<body>
<?php
include ROOT_PATH . '/components/navigation_bar_only_brand.html';
include ROOT_PATH . '/components/other_components.html';
?>
<div class="content">
    <div class="section-container login-section-container">
        <div class="section-title login-section-title">邮箱验证</div>
        <div class="section-content login-section-content">
            <div>验证邮箱之后才能继续登录</div>
            <div class="line-container">
                <label for="current-email">当前邮箱</label>
                <input type="text" class="input-text login-email-input-text" id="current-email" value="<?php echo $email; ?>">
            </div>
            <div class="line-container">
                <div class="button button-theme-color login-button" onclick="loginVerifyEmailChangeEmail()">修改邮箱</div>
            </div>
            <div class="line-container">
                <div class="button button-theme-color login-button" id="send-verification-code-button" onclick="verifyEmailSendEmailVerificationCode()">发送验证码</div>
            </div>
            <div class="line-container login-input-container" id="login-input-verification-code-container" onclick="inputBorderTriggerInput('verification-code')">
                <label for="verification-code" class="fas fa-key login-input-text-label" aria-hidden="true"></label>
                <input type="text" class="login-input-text" id="verification-code" name="verification-code" placeholder="验证码"
                       onkeyup="this.value = this.value.replace(/\D/g,'')"
                       onfocus="inputFocusTriggerBorder('login-input-verification-code-container', 'login-input-container-focus', 1)"
                       onblur="inputFocusTriggerBorder('login-input-verification-code-container', 'login-input-container-focus', 0); this.value = this.value.replace(/\D/g,'')">
            </div>
            <div class="line-container">
                <div class="button button-theme-color-reverse login-button" onclick="verifyEmailVerifyVerificationCode()">验证邮箱</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
