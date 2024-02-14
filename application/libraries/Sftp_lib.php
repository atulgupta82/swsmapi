<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'phpseclib\phpseclib\Net\SFTP.php';

// use phpseclib\phpseclib\Net\SFTP;

class Sftp_lib {

    private $sftp;

    public function __construct() {
        require_once APPPATH . '../vendor/autoload.php';

        $this->sftp = new phpseclib\Net\SFTP('103.116.26.53');
        $this->sftp->login('hdfc', 'HDFC@3247');
    }

    public function listFiles($path = '/') {
        return $this->sftp->nlist($path);
    }

    // You can add more SFTP-related methods here as needed

}
