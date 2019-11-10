<?php
if (!isset($_SESSION['login'])) {
    header('location: ?login');
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <?php include ROOT_PATH . '/components/html_head.html'; ?>
    <link rel="stylesheet" type="text/css" href="css/cropper.min.css">
    <script src="js/cropper.min.js"></script>
    <script src="js/account.js"></script>
</head>
<body>
<?php
include ROOT_PATH . '/components/navigation_bar_login.html';
include ROOT_PATH . '/components/other_components.html';
?>
<div class="content">
    <div class="section-container">
        <div class="section-title">个人中心</div>
        <div class="section-content">
            <section class="account-sub-section account-user-information">
                <div class="account-user-information-row" onclick="openChangeAvatarDialog()">
                    <div class="account-user-information-row-title">头像</div>
                    <div class="account-user-information-row-content">
                        <div class="account-user-avatar" id="account-user-avatar"></div>
                    </div>
                </div>
                <div class="account-user-information-row" onclick="accountOpenSettingDialog('change_username')">
                    <div class="account-user-information-row-title">用户名</div>
                    <div class="account-user-information-row-content" id="account-username"></div>
                </div>
                <div class="account-user-information-row" onclick="accountOpenSettingDialog('change_password')">
                    <div class="account-user-information-row-title">密码</div>
                    <div class="account-user-information-row-content" id="account-password">********</div>
                </div>
                <div class="account-user-information-row" onclick="openAccountEmailBindingDialog()">
                    <div class="account-user-information-row-title">电子邮件</div>
                    <div class="account-user-information-row-content" id="account-email"></div>
                </div>
                <div class="account-user-information-row" onclick="accountOpenSettingDialog('bind_qq')">
                    <div class="account-user-information-row-title">QQ</div>
                    <div class="account-user-information-row-content" id="account-qq"></div>
                </div>
            </section>
        </div>
    </div>
</div>
</body>
</html>