
<!DOCTYPE html>
<html lang="zh" xmlns:v-bind="http://www.w3.org/1999/xhtml" xmlns:v-on="http://www.w3.org/1999/xhtml">
<head>
    <?php include ROOT_PATH . '/components/html_head.html'; ?>
    <script src="js/vue.js"></script>
    <script src="js/clipboard.min.js"></script>
    <script src="js/main.js?19110710.29"></script>
</head>
<body>
<?php
if (isset($_SESSION['login'])) {
    include ROOT_PATH . '/components/navigation_bar_login.html';
} else {
    include ROOT_PATH . '/components/navigation_bar.html';
}
include ROOT_PATH . '/components/other_components.html';
?>
<div id="dummy" data-clipboard-text=""></div>
<div class="content">
    <div class="room-number-data-container" id="room-number-data-container" v-cloak>
        <transition-group name="flip-list" tag="div">
            <div class="room-number-data" v-for="data in room_number_list" v-bind:key="data.index">
                <div class="room-number-data-column">
                    <div class="room-number-data-user-avatar" v-bind:style="data.user_info.avatar_style"></div>
                </div>
                <div class="room-number-data-column room-number-data-column-follow">
                    <div>
                        <span class="room-number-data-user-info-username">{{data.user_info.username}}</span>
                        <span class="room-number-data-other">来自{{data.source_info.name}}</span>
                        <span class="room-number-data-other">{{data.time_interval}}</span>
                    </div>
                    <div class="line-container">
                        <span class="room-number-data-number-copy" title="复制房间号" v-on:click="copyRoomNumber(data.number)">
                            <span class="room-number-data-number">{{data.number}}</span>
                            <span class="fas fa-copy"></span>
                        </span>
                        <span class="room-number-data-number-type">{{data.type}}</span>
                        <span class="room-number-data-raw-msg">{{data.raw_message}}</span>
                    </div>
                    <div class="line-container room-number-data-operation-button-container">
                        <span class="room-number-data-operation-button"
                              v-on:click="blockUser(data.user_info.type, data.user_info.user_id, data.user_info.username)">屏蔽</span>
                        <span class="room-number-data-operation-button" style="color: var(--theme-color)"
                              v-on:click="informUser(data)">举报</span>
                    </div>
                </div>
            </div>
        </transition-group>
    </div>
</div>
<div class="home-side-button-container">
    <div class="button button-theme-color home-side-button" title="筛选房间号" onclick="homeOpenFilterDialog()">
        <i class="fas fa-filter"></i>
    </div>
    <div class="button button-theme-color-reverse home-side-button" title="发送房间号" onclick="homeOpenDialog('send_room_number')">
        <i class="fas fa-plus"></i>
    </div>
</div>
</body>
</html>