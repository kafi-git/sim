<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/2/2017
 * Time: 12:23 PM
 */

namespace Server\Controller;


use Core\AES;
use Core\Controller;
use Core\Converter;
use Core\ExclusiveOR;

/**
 * Class AuthenticationController
 * @package Server\Controller
 */
class AuthenticationController extends Controller
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
     * AuthenticationController constructor.
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

        $stmt = $this->db->prepare("SELECT
                                      `id`,
                                      `user_id`,
                                      `server_id`,
                                      `SXi`
                                    FROM `server_user_tbl`
                                    WHERE user_id = :user_id
                                    AND server_id = :server_id");

        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() == 1){

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $SXi = "";

            foreach ($result as $row){
                $SXi = $row["SXi"];
            }

            $SKaes = $this->getServerPrivateKey($request["sid"]);
            $aes = new AES($SKaes);

            $Xs = $aes->decrypt($SXi);
            $temp = hash("sha256", $request["id"].$Xs);

            $converter = new Converter();
            $binary1 = $converter->setHexadecimal($request["M2"])->hexadecimalToBinary()->getBinary();
            $binary2 = $converter->setString($temp)->stringToBinary()->getBinary();

            $xor = new ExclusiveOR();
            $xored = $xor->set($binary1, $binary2)->bitwiseXor()->getXored();

            $Rn2 = $converter->setBinary($xored)->binaryToString()->getString();

            $M3 = hash("sha256", $Xs.$Rn2);

            if ($request["M1"] === $M3){
                $Rn3 = $this->getUniqueString();
                $M4 = hash("sha256", $Xs.$Rn3);

                $binary1 = $binary2;
                $binary2 = $converter->setString($Rn3)->stringToBinary()->getBinary();
                $xored = $xor->set($binary1, $binary2)->bitwiseXor()->getXored();

                $M5 = $converter->setBinary($xored)->binaryToHexadecimal()->getHexadecimal();

                return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "M4" => $M4, "M5" => $M5, "Rn3" => $Rn3));
            } else {
                return json_encode(array("flag" => 2, "msg" => "Verification failed. M1 != M3!!"));
            }

        } else {
            return json_encode(array("flag" => 2, "msg" => "Verification failed. User may not be registered to this server!!"));
        }
    }


    /**
     * @return string
     */
    public function receiveAcknowledgement()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        if (!isset($request["M7"])) {
            return json_encode(array("flag" => 2, "msg" => "No M7 found!!"));
        }

        if (!isset($request["Rn2"])) {
            return json_encode(array("flag" => 2, "msg" => "No Rn2 found!!"));
        }

        if (!isset($request["Rn3"])) {
            return json_encode(array("flag" => 2, "msg" => "No Rn3 found!!"));
        }

        if (!isset($request["stat"]) || "Auth" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a authentication acknowledgement!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "M7" => $request["M7"], "Rn2" => $request["Rn2"], "Rn3" => $request["Rn3"]));
    }

    /**
     * @return string
     */
    public function verificationAndFinalization()
    {
        $request = $this->request;

        $M8 = hash("sha256", $request["Xs"].$request["Rn2"].$request["Rn3"]);

        if ($request["M7"] === $M8){
            $this->loginLog($request["id"], $request["sid"]);
            return json_encode(array("flag" => 1, "msg" => "Authentication Completed!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Authentication Failed. M7 != M8!!"));
        }
    }

    /**
     * @return string
     */
    public function sessionKeyGeneration()
    {
        $request = $this->request;

        if (!isset($request["Rn2"])) {
            return json_encode(array("flag" => 2, "msg" => "No Rn2 found!!"));
        }

        if (!isset($request["Rn3"])) {
            return json_encode(array("flag" => 2, "msg" => "No Rn3 found!!"));
        }

        $Kses = hash("sha256", $request["Rn2"].$request["Rn3"]);

        if ("" != $Kses){
            return json_encode(array("flag" => 1, "msg" => "Session Key Generated!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to Generate Session Key!!"));
        }
    }

    /**
     * @param $user_id
     * @param $server_id
     */
    private function loginLog($user_id, $server_id)
    {
        if (isset($user_id) && isset($server_id)){

            $stmt = $this->db->prepare("INSERT INTO `login_log_tbl`
                                            (`user_id`,
                                             `server_id`,
                                             `date_time`)
                                VALUES (:user_id,
                                        :server_id,
                                        :date_time)");
            $stmt->execute(array("user_id" => $user_id, "server_id" => $server_id, "date_time" => $this->dateTime));
        }
    }
}