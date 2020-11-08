<?php

use BS_API\RequestProcessing;

define('ROOT_PATH', dirname(__FILE__));

require ROOT_PATH . '/Utility/class_loader.php';
spl_autoload_register('class_loader');
require ROOT_PATH . '/Utility/error_handler.php';

echo json_encode((new RequestProcessing())->execute());