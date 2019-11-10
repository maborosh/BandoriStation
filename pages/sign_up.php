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
    <script src="js/login.js"></script>
</head>
<body>
<?php
include ROOT_PATH . '/components/navigation_bar_only_brand.html';
include ROOT_PATH . '/components/other_components.html';
?>
<div class="content">
    <div class="section-container login-section-container">
        <div class="section-title login-section-title">注册</div>
        <div class="section-content login-section-content">
            <form action="?sign_up_check" method="post">
                <div class="line-container login-input-container" id="login-input-username-container" onclick="inputBorderTriggerInput('username')">
                    <label for="username" class="fas fa-user login-input-text-label" aria-hidden="true"></label>
                    <input type="text" class="login-input-text" id="username" name="username" placeholder="用户名"
                           onfocus="inputFocusTriggerBorder('login-input-username-container', 'login-input-container-focus', 1)"
                           onblur="inputFocusTriggerBorder('login-input-username-container', 'login-input-container-focus', 0)">
                </div>
                <div class="line-container login-input-container" id="login-input-password-container" onclick="inputBorderTriggerInput('password')">
                    <label for="password" class="fas fa-lock login-input-text-label" aria-hidden="true"></label>
                    <input type="password" class="login-input-text" id="password" name="password" placeholder="密码"
                           onfocus="inputFocusTriggerBorder('login-input-password-container', 'login-input-container-focus', 1)"
                           onblur="inputFocusTriggerBorder('login-input-password-container', 'login-input-container-focus', 0)">
                </div>
                <div class="line-container login-input-container" id="sign-up-input-password-repeat-container" onclick="inputBorderTriggerInput('password-repeat')">
                    <label for="password-repeat" class="fas fa-check-circle login-input-text-label" aria-hidden="true"></label>
                    <input type="password" class="login-input-text" id="password-repeat" name="email" placeholder="再次输入密码"
                           onfocus="inputFocusTriggerBorder('sign-up-input-password-repeat-container', 'login-input-container-focus', 1)"
                           onblur="inputFocusTriggerBorder('sign-up-input-password-repeat-container', 'login-input-container-focus', 0)">
                </div>
                <div class="line-container login-input-container" id="login-input-email-container" onclick="inputBorderTriggerInput('email')">
                    <label for="email" class="fas fa-user login-input-text-label" aria-hidden="true"></label>
                    <input type="text" class="login-input-text" id="email" name="email" placeholder="电子邮件"
                           onfocus="inputFocusTriggerBorder('login-input-email-container', 'login-input-container-focus', 1)"
                           onblur="inputFocusTriggerBorder('login-input-email-container', 'login-input-container-focus', 0)">
                </div>
                <div class="line-container">
                    <input type="submit" class="button button-theme-color-reverse login-button" value="注册" name="submit" onclick="return signUpSubmitCheck()">
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
