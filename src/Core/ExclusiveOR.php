<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/31/2016
 * Time: 6:26 PM
 */

namespace Core;


/**
 * Class ExclusiveOR
 * @package Core
 */
class ExclusiveOR
{
    /**
     * @var
     */
    private $binary1;
    /**
     * @var
     */
    private $binary2;
    /**
     * @var
     */
    private $xored;

    /**
     * @param string $binary1
     * @param string $binary2
     * @return $this
     */
    public function set($binary1 = "", $binary2 = "")
    {
        $this->binary1 = $binary1;
        $this->binary2 = $binary2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getXored()
    {
        return $this->xored;
    }


    /**
     * @return $this|string
     */
    public function bitwiseXor()
    {
        if ("" === $this->binary1 && "" === $this->binary2) {
            $this->xored = "";
            return $this;
        }

        $length1 = strlen($this->binary1);
        $length2 = strlen($this->binary2);

        $maxLength = $length2 < $length1 ? $length1 : $length2;

        $binary1 = str_pad($this->binary1, $maxLength, "0", STR_PAD_LEFT);
        $binary2 = str_pad($this->binary2, $maxLength, "0", STR_PAD_LEFT);

        $this->xored = "";

        for ($i = 0; $i < $maxLength; $i++) {

            if ($binary1[$i] === $binary2[$i]) {
                $this->xored .= "0";
            } else {
                $this->xored .= "1";
            }
        }

        return $this;
    }

}