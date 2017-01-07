<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/27/2016
 * Time: 5:00 PM
 */

namespace RegistrationCenter\Controller;


use Core\AES;
use Core\Controller;

/**
 * Class UserRegistrationController
 * @package RegistrationCenter\Controller
 */
class UserRegistrationController extends Controller
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
     * UserRegistrationController constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->db = $db;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function receiveRequest()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id received!!"));
        }

        if (!isset($request["bpi"])) {
            return json_encode(array("flag" => 2, "msg" => "No bpi received!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id received!!"));
        }

        if (!isset($request["rcont"])) {
            return json_encode(array("flag" => 2, "msg" => "No recovery contact received!!"));
        }

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status received!!"));
        }

        if ("Register" != $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a user registration request!!"));
        }

        $stmt = $this->db->prepare("SELECT
                                      `id`
                                    FROM `rc_server_tbl`
                                    WHERE server_id = :server_id");

        $stmt->execute(array("server_id" => $request["sid"]));

        if ($stmt->rowCount() == 0) {
            return json_encode(array("flag" => 2, "msg" => "The server is not registered!!"));
        }

        $W = $this->getUniqueString();
        $TXs = $W . $request["bpi"];

        return json_encode(array("flag" => 1, "id" => $request["id"], "bpi" => $request["bpi"], "sid" => $request["sid"], "rcont" => $request["rcont"], "txs" => $TXs, "W" => $W));
    }

    /**
     * @return string
     */
    public function receiveReply()
    {
        $request = $this->request;

        if (!isset($request["id"]) || !isset($request["rid"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id received!!"));
        }

        if ($request["id"] !== $request["rid"]) {
            return json_encode(array("flag" => 2, "msg" => "User id mismatch!!"));
        }

        if (!isset($request["sid"]) || !isset($request["rsid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id received!!"));
        }

        if ($request["sid"] !== $request["rsid"]) {
            return json_encode(array("flag" => 2, "msg" => "Server id mismatch!!"));
        }

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status received!!"));
        }

        if ("Complete" != $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a server reply!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["rid"], "sid" => $request["rsid"]));

    }


    /**
     * @return string
     */
    public function prepareCard()
    {
        $request = $this->request;
        $HKs = "";

        if (!isset($request["id"]) || !isset($request["sid"]) || !isset($request["bpi"]) || !isset($request["W"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id or server id or bpi or W found!!"));
        }

        $stmt = $this->db->prepare("SELECT HKs FROM rc_server_tbl WHERE server_id = :server_id");

        $stmt->execute(array("server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $HKs = $row["HKs"];
            }
        } else {
            return json_encode(array("flag" => 2, "msg" => "Server is not registered!!"));
        }

        $RKaes = $this->getRcPrivateKey();
        $aes = new AES($RKaes);
        $Ks = $aes->decrypt($HKs);

        $W = $request["W"];

        $TCs = $Ks . $W;

        /*$cardContent = "IDi:" . $request["id"] . "\n";
        $cardContent .= "SIDi:" . $request["sid"] . "\n";
        $cardContent .= "BPi:" . $request["bpi"] . "\n";
        $cardContent .= "TCs:" . $TCs;*/

        $stmt = $this->db->prepare("INSERT INTO `card_tbl` (`user_id`, `server_id`, `TCs`, `BPi`) VALUES (:user_id, :server_id, :TCs, :BPi)");
        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"], "TCs" => $TCs, "BPi" => $request["bpi"]));

        /*if (1 === $this->writeCard($request["id"], $cardContent)) {*/
        if ($stmt->rowCount() > 0){
            return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "tcs" => $TCs));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Unable to write smart card!!"));
        }
    }

    /**
     * @return string
     */
    public function receiveAck()
    {
        $request = $this->request;

        if (!isset($request["id"]) || !isset($request["aid"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id received!!"));
        }

        if ($request["id"] !== $request["aid"]) {
            return json_encode(array("flag" => 2, "msg" => "User id mismatch!!"));
        }

        if (!isset($request["sid"]) || !isset($request["asid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id received!!"));
        }

        if ($request["sid"] !== $request["asid"]) {
            return json_encode(array("flag" => 2, "msg" => "Server id mismatch!!"));
        }

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status received!!"));
        }

        if ("Accept" != $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a server reply!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["aid"], "sid" => $request["asid"]));
    }

    /**
     * @return string
     */
    public function storeDataAndFinalization()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        if (!isset($request["rcont"])) {
            return json_encode(array("flag" => 2, "msg" => "No recovery contact found!!"));
        }

        if (!isset($request["txs"])) {
            return json_encode(array("flag" => 2, "msg" => "No TXs found!!"));
        }

        if (!isset($request["tcs"])) {
            return json_encode(array("flag" => 2, "msg" => "No TCs found!!"));
        }

        $RKaes = $this->getRcPrivateKey();

        $aes = new AES($RKaes);

        $Rcov = $aes->encrypt($request["rcont"]);
        $EXi = $aes->encrypt($request["txs"]);
        $UXi = $aes->encrypt($request["tcs"]);

        $stmt = $this->db->prepare("INSERT INTO `rc_user_tbl`
                                            (`user_id`,
                                             `server_id`,
                                             `UXi`,
                                             `EXi`,
                                             `Rcov`)
                                VALUES (:user_id,
                                        :server_id,
                                        :UXi,
                                        :EXi,
                                        :Rcov)");

        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"], "UXi" => $UXi, "EXi" => $EXi, "Rcov" => $Rcov));

        if ($this->db->lastInsertId() > 0) {
            return json_encode(array("flag" => 1, "msg" => "User registration completed!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to save data!!"));
        }
    }

    /**
     * @return string
     */
    public function revert()
    {
        $request = $this->request;

        if (!isset($request["id"]) || !isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id or server id found!!"));
        }

        $stmt = $this->db->prepare("SELECT id FROM rc_user_tbl WHERE user_id = :user_id AND server_id = :server_id");
        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {
            $stmt = $this->db->prepare("DELETE FROM rc_user_tbl WHERE user_id = :user_id AND server_id = :server_id");
            $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

            if ($stmt->rowCount() > 0) {
                return json_encode(array("flag" => 1, "msg" => "RC change reverted"));
            } else {
                return json_encode(array("flag" => 2, "msg" => "Can not be reverted!!"));
            }
        } else {
            return json_encode(array("flag" => 1, "msg" => "No RC change found"));
        }
    }
}