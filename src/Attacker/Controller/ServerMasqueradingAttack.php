<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/7/2017
 * Time: 2:37 AM
 */

namespace Attacker\Controller;


use Core\Controller;
use Core\Converter;
use Core\ExclusiveOR;

class ServerMasqueradingAttack extends Controller
{
    /**
     * @var \PDO
     */
    private $db;
    /**
     * @var
     */
    private $request;
    /**
     * @var false|string
     */
    private $dateTime;

    /**
     * ServerMasqueradingAttack constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->db = $db;
        $this->request = $request;
        $this->dateTime = date("Y-m-d H:i:s");
    }

    /**
     * @return string
     */
    public function receiveRequest()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        if (!isset($request["M1"])) {
            return json_encode(array("flag" => 2, "msg" => "No M1 found!!"));
        }

        if (!isset($request["M2"])) {
            return json_encode(array("flag" => 2, "msg" => "No M2 found!!"));
        }

        if (!isset($request["stat"]) || "Login" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a login request!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "M1" => $request["M1"], "M2" => $request["M2"]));
    }

    /**
     * @return string
     */
    public function verifyAndMuaGeneration()
    {
        $request = $this->request;

        if (1){

            $Xs = "";
            $temp = hash("sha256", $request["id"].$Xs);

            //$M3 = hash("sha256", $Xs.$Rn2);

            $Rn3 = $this->getUniqueString();
            $M4 = hash("sha256", $Xs.$Rn3);

            $converter = new Converter();
            $binary1 = $converter->setString($temp)->stringToBinary()->getBinary();
            $binary2 = $converter->setString($Rn3)->stringToBinary()->getBinary();
            $xor = new ExclusiveOR();
            $xored = $xor->set($binary1, $binary2)->bitwiseXor()->getXored();

            $M5 = $converter->setBinary($xored)->binaryToHexadecimal()->getHexadecimal();

            return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "M4" => $M4, "M5" => $M5, "Rn3" => $Rn3));


        } else {
            return json_encode(array("flag" => 2, "msg" => "Verification failed. User may not be registered to this server!!"));
        }
    }
}