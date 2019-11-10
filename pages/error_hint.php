<?php
$msg = '未找到该页面';
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta http-equiv="refresh" content="3;URL=?">
    <?php include ROOT_PATH . '/components/html_head.html'; ?>
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