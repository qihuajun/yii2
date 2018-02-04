<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2018/1/10
 * Time: 下午8:37
 */

namespace yii\web;


use yii\base\InvalidConfigException;
use yii\caching\MemCacheServer;

class MemcacheSession extends Session
{
    /**
     * @var array Memcache Server Configurations
     *
     * See [PHP manual](http://php.net/manual/en/memcache.addserver.php) for detailed explanation
     * of each configuration property.
     */
    public $servers;

    /**
     * @var array
     */
    public $sessionRedundancy = 2;



    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!extension_loaded('memcache')) {
            throw new InvalidConfigException("MemcacheSession requires PHP memcache extension to be loaded.");
        }

        ini_set('session.save_handler','memcache');
        ini_set('memcache.redundancy',2);

        ini_set('memcache.session_redundancy',$this->sessionRedundancy ? $this->sessionRedundancy : 2 );

        $this->setServers();
    }

    /**
     * validate servers and add servers to session.save_path
     *
     */
    public function setServers()
    {

        if(empty($this->servers)){
            throw new InvalidConfigException("Memcache servers must be configured!");
        }

        $params = [
            'weight' => 1,
            'persistent' => true,
            'timeout' => 1000,
            'retryInterval' => 15,
            'status' => true
        ];

        $uris = [];

        foreach ($this->servers as $server) {
            if(!isset($server['host']) || !$server['host']){
                throw new InvalidConfigException("Memcache server's host must be configured!");
            }

            if(!isset($server['port']) || !$server['port']){
                $server['port'] = 11211;
            }

            $uri = "tcp://{$server['host']}:{$server['port']}";

            $configs = [];

            foreach ($params as $param => $value) {
                if(isset($server[$param])){
                    $configs[$param] =  $server[$param];
                }else{
                    $configs[$param] = $value;
                }
            }

            $uri = $uri.'?'.http_build_query($configs);

            $uris[] = $uri;
        }

        $path = implode(',',$uris);

        ini_set('session.save_path',$path);

    }

    /**
     * @inheritdoc
     */
    public function setSavePath($value)
    {
        return ;
    }
}