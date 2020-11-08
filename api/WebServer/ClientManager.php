<?php


namespace BS_API\WebServer;


use Swoole\Table;

class ClientManager
{
    private static Table $clientList;

    public static function createTable()
    {
        self::$clientList = new Table(4096);
        self::$clientList->column('fd', Table::TYPE_INT, 4);
        self::$clientList->column('client', Table::TYPE_STRING, 30);
        self::$clientList->column('user_id', Table::TYPE_INT, 4);
        self::$clientList->column('send_room_number', Table::TYPE_INT, 1);
        self::$clientList->column('send_chat', Table::TYPE_INT, 1);
        self::$clientList->create();
    }

    public static function setClient($fd, $setting, $is_set_access_permission = false)
    {
        if (self::$clientList->exist(strval($fd))) {
            $config = self::$clientList->get(strval($fd));
        } else {
            $config = array(
                'client' => 'Unknown',
                'user_id' => 0,
                'send_room_number' => 0,
                'send_chat' => 0
            );
        }
        $config_setting = array(
            'client' => 'string',
            'user_id' => 'int',
            'send_room_number' => 'boolean',
            'send_chat' => 'boolean'
        );
        foreach ($config as $key => $value) {
            if (!$is_set_access_permission and $key == 'user_id') {
                continue;
            } elseif (isset($setting[$key])) {
                switch ($config_setting[$key]) {
                    case 'string':
                        $config[$key] = strval($setting[$key]);
                        break;
                    case 'int':
                        $config[$key] = intval($setting[$key]);
                        break;
                    case 'boolean':
                        $config[$key] = $setting[$key] ? 1 : 0;
                        break;
                    default:
                        $config[$key] = $setting[$key];
                }
            }
        }
        $config['fd'] = $fd;
        self::$clientList->set(
            strval($fd),
            $config
        );
    }

    public static function getClientList()
    {
        return self::$clientList;
    }

    public static function getClientSetting($fd, $field = null)
    {
        return self::$clientList->get(strval($fd), $field);
    }

    public static function removeClient($fd)
    {
        self::$clientList->del(strval($fd));
    }
}