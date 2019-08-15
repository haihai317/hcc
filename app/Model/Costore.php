<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

require (app_path() . '/Libs/cosquery/include.php');
use QCloud\Cos\Api;

class Costore extends Model
{
    protected $bucket = 'skphotos';
    protected $src = '';
    protected $dst = '/';
    protected $dst2;
    protected $folder;
    protected $config = array(
        'app_id' => '1252356175',
        'secret_id' => 'AKIDdgfix9cVb1X9fknOzWH4ufYmgibY7E1B',
        'secret_key' => 'fBzspr2cNjBSm6WKyhYG2fAWHRRTRFzY',
        'region' => 'sh',   // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
        'timeout' => 60
    );
    protected $cosApi = null;

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('PRC');
        $this->cosApi = new Api($this->config);
    }

    /**
     * 上传文件
     *
     * @param void
     * @return object
     */
    public function fileUpload($src, $fileName) {
        $this->src = $src;
        return $this->cosApi->upload($this->bucket, $this->src, $this->dst.$fileName);
    }
}
