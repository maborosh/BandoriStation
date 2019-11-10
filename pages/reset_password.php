<?php
if (isset($_SESSION['login'])) {
    header('location: /');
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <?php include ROOT_PATH . '/components/html_head.html'; ?>
    <script src="js/login.js?19110921.48"></script>
</head>
<body>
<?php
include ROOT_PATH . '/components/navigation_bar_only_brand.html';
include ROOT_PATH . '/components/other_components.html';
?>
<div class="content">
    <div class="section-container login-section-container">
        <div class="section-title login-section-title">重置密码</div>
        <div class="section-content login-section-content" id="login-reset-password-container">
            <div class="line-container login-input-container" id="login-input-email-container"
                 onclick="inputBorderTriggerInput('email')">
                <label for="email" class="fas fa-user login-input-text-label" aria-hidden="true"></label>
                <input type="text" class="login-input-text" id="email" name="email" placeholder="电子邮件"
                       onfocus="inputFocusTriggerBorder('login-input-email-container', 'login-input-container-focus', 1)"
                       onblur="inputFocusTriggerBorder('login-input-email-container', 'login-input-container-focus', 0)"
                       onkeydown="textInputEnter('login-reset-password-next-step-button')">
            </div>
            <div class="line-container">
                <div class="link-block button button-theme-color-reverse login-button" id="login-reset-password-next-step-button" onclick="resetPasswordInputEmail()">下一步</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>