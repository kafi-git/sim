<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/31/2016
 * Time: 7:15 PM
 */

namespace Core;


/**
 * Class Converter
 * @package Core
 */
class Converter
{
    /**
     * @var
     */
    private $binary;
    /**
     * @var
     */
    private $hexadecimal;
    /**
     * @var
     */
    private $string;

    /**
     * @return mixed
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * @param mixed $binary
     * @return $this
     */
    public function setBinary($binary)
    {
        $this->binary = $binary;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHexadecimal()
    {
        return $this->hexadecimal;
    }

    /**
     * @param mixed $hexadecimal
     * @return $this
     */
    public function setHexadecimal($hexadecimal)
    {
        $this->hexadecimal = $hexadecimal;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @param mixed $string
     * @return $this
     */
    public function setString($string)
    {
        $this->string = $string;

        return $this;
    }


    /**
     * @return $this|string
     */
    public function stringToBinary()
    {
        if ("" === $this->string || !is_string($this->string)) {
            $this->binary = "";

            return $this;
        }

        $this->binary = '';

        for ($i = 0; $i < strlen($this->string); $i++) {
            $temp = decbin(ord($this->string{$i}));
            $this->binary .= str_repeat("0", 8 - strlen($temp)) . $temp;
        }

        return $this;
    }


    /**
     * @return $this|string
     */
    public function binaryToString()
    {
        if ("" === $this->binary) {

            $this->string = '';
            return $this;
        }

        $this->string = '';
        $chars = explode("\n", trim(chunk_split(str_replace("\n", '', $this->binary), 8)));
        $length = count($chars);

        for ($i = 0; $i < $length; $i++) {
            $dec = bindec($chars[$i]);
            if (0 != $dec){
                $this->string .= chr(bindec($chars[$i]));
            }
        }

        return $this;
    }


    /**
     * @return $this|string
     */
    public function binaryToHexadecimal()
    {
        if ("" === $this->binary) {
            $this->hexadecimal = "";
            return $this;
        }

        $this->hexadecimal = "";

        $chars = explode("\n", trim(chunk_split(str_replace("\n", '', $this->binary), 4)));
        $length = count($chars);

        for ($i = 0; $i < $length; $i++) {
            $this->hexadecimal .= base_convert($chars[$i], 2, 16);
        }

        return $this;
    }


    /**
     * @return $this|string
     */
    public function hexadecimalToBinary()
    {
        if ("" === $this->hexadecimal) {

            $this->binary = "";
            return $this;
        }

        $this->binary = "";

        $chars = explode("\n", trim(chunk_split(str_replace("\n", '', $this->hexadecimal), 1)));
        $length = count($chars);

        for ($i = 0; $i < $length; $i++) {

            $temp = base_convert($chars[$i], 16, 2);

            $this->binary .= str_pad($temp, 4, "0", STR_PAD_LEFT);
        }

        return $this;
    }
}