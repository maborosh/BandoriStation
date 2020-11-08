<?php

function class_loader($class_name)
{
    $class_path = str_replace(array('\\', 'BS_API'), array('/', ROOT_PATH), $class_name) . '.php';
    if (file_exists($class_path)) {
        require $class_path;
    }
}