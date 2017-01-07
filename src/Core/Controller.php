<?php
namespace Core;

/**
 * Class Controller
 * @package Core
 */
/**
 * Class Controller
 * @package Core
 */
class Controller
{
    /**
     * @return string
     */
    public function getBasePath()
    {
        return "http://localhost/sim/";
    }

    /**
     * @param string $bundle
     * @return string
     */
    public function getViewPath($bundle = "")
    {
        return $bundle . "/Resources/views/";
    }

    /**
     * @return string
     */
    public function getCssPath()
    {
        return "public/css/";
    }

    /**
     * @return string
     */
    public function getJsPath()
    {
        return "public/js/";
    }

    /**
     * @return string
     */
    public function getVendorPath()
    {
        return "public/vendor/";
    }

    /**
     * @return string
     */
    public function getUniqueString()
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $random = substr(str_shuffle(str_repeat($pool, 16)), 0, 16);

        return sha1($random . time());
    }

    /**
     * @return string
     */
    public function getUniqueString256()
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $random = substr(str_shuffle(str_repeat($pool, 16)), 0, 16);

        return hash("md5", $random . time());
    }

    /**
     * @return string
     */
    public function getServerPrivateKey($sid = "none")
    {
        return $this->readFile('src/Server/Key/' . $sid . '.key');
    }

    /**
     * @return string
     */
    public function getRcPrivateKey()
    {
        return $this->readFile('src/RegistrationCenter/Key/Rc.key');
    }

    /**
     * @return string
     */
    public function getUserPrivateKey($id = "none")
    {
        return $this->readFile('src/User/Key/' . $id . '.key');
    }

    /**
     * @param string $sid
     * @param string $key
     * @return int|string
     */
    public function writeServerKey($sid = "none", $key = "")
    {
        return $this->writeFile('src/Server/Key/' . $sid.'.key', $key);
    }

    /**
     * @param string $id
     * @param string $content
     * @return int|string
     */
    public function writeCard($id = "none", $content = "")
    {
        return $this->writeFile('src/User/Card/' . $id.'.card', $content);
    }

    /**
     * @param string $id
     * @return string
     */
    public function readCard($id = "none")
    {
        return $this->readFile('src/User/Card/' . $id . '.card');
    }

    /**
     * @param string $id
     */
    public function deleteCard($id = "none")
    {
        $this->deleteFile('src/User/Card/' . $id . '.card');
    }

    /**
     * @param string $file
     * @param string $content
     * @return int|string
     */
    private function writeFile($file = "none", $content = "")
    {
        if ("none" === $file) {
            return "";
        }

        try {
            $fp = fopen($file, 'w');
            fwrite($fp, $content);
            fclose($fp);
            return 1;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

    }

    /**
     * @param string $file
     * @return string
     */
    private function readFile($file = "")
    {
        if ("" == $file) {
            return "";
        }

        return file_get_contents($file);
    }


    /**
     * @param string $file
     * @return string
     */
    private function deleteFile($file = "")
    {
        if ("" == $file) {
            return "";
        }

        if (file_exists($file)) {
            unlink($file);
        }
    }
}