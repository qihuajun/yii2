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

class MemcachedSession extends Session
{
    /**
     * @var array Memcache Server Configurations
     *
     * See [PHP manual](http://php.net/manual/en/memcache.addserver.php) for detailed explanation
     * of each configuration property.
     */
    public $servers;

    /**
     * @var int
     *
     * See [PHP manual](http://php.net/manual/en/memcached.configuration.php#ini.memcached.sess-consistent-hash) for detailed explanation
     */
    public $sessConsistentHash = 1;

    /**
     * @var int
     *
     * See [PHP manual](http://php.net/manual/en/memcached.configuration.php#ini.memcached.sess-binary) for detailed explanation
     *
     */
    public $sessBinary = 0;

    /**
     * @var int
     *
     * See [PHP manual](http://php.net/manual/en/memcached.configuration.php#ini.memcached.sess-lock-wait) for detailed explanation
     */
    public $sessLockWait = 150000;


    /**
     * @var string
     *
     * See [PHP manual](http://php.net/manual/en/memcached.configuration.php#ini.memcached.sess-prefix) for detailed explanation
     */
    public $sessPrefix = 'memc.sess.key.';

    /**
     * @var int
     *
     * See [PHP manual](http://php.net/manual/en/memcached.configuration.php#ini.memcached.sess-number-of-replicas) for detailed explanation
     */
    public $sessNumberOfReplicas = 0;


    /**
     * @var int
     *
     * See [PHP manual](http://php.net/manual/en/memcached.configuration.php#ini.memcached.sess-randomize-replica-read) for detailed explanation
     */
    public $sessRandomizeReplicaRead = 0;

    /**
     * @var int
     *
     * See [PHP manual](http://php.net/manual/en/memcached.configuration.php#ini.memcached.sess-remove-failed) for detailed explanation
     */
    public $sessRemoveFailed = 0;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!extension_loaded('memcached')) {
            throw new InvalidConfigException("MemcacheSession requires PHP memcache extension to be loaded.");
        }


        ini_set('session.save_handler','memcached');
        ini_set('memcached.sess_locking',1);

        ini_set('memcached.sess_consistent_hash',intval($this->sessConsistentHash) );
        ini_set('memcached.sess_binary',intval($this->sessBinary) );
        ini_set('memcached.sess_lock_wait',intval($this->sessLockWait) );
        ini_set('memcached.sess_prefix',$this->sessPrefix );
        ini_set('memcached.sess_number_of_replicas',intval($this->sessNumberOfReplicas) );
        ini_set('memcached.sess_randomize_replica_read',intval($this->sessRandomizeReplicaRead) );
        ini_set('memcached.sess_remove_failed',intval($this->sessRemoveFailed) );

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


        $uris = [];

        foreach ($this->servers as $server) {
            if(!isset($server['host']) || !$server['host']){
                throw new InvalidConfigException("Memcache server's host must be configured!");
            }

            if(!isset($server['port']) || !$server['port']){
                $server['port'] = 11211;
            }

            $uri = "{$server['host']}:{$server['port']}";

            if(isset($server['weight'])){
                $uri .= ":{$server['weight']}";
            }

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