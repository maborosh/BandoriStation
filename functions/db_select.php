<?php

function db_select($db_name)
{
    $dbh = new PDO('mysql:host=server_ip;dbname=' . $db_name, 'account', 'password');
    $dbh->query('set names utf-8');
    return $dbh;
}