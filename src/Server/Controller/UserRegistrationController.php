<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/27/2016
 * Time: 8:57 PM
 */

namespace Server\Controller;


use Core\AES;
use Core\Controller;

/**
 * Class UserRegistrationController
 * @package Server\Controller
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

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id received!!"));
        }

        if (!isset($request["txs"])) {
            return json_encode(array("flag" => 2, "msg" => "No TXs received!!"));
        }

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status received!!"));
        }

        if ("Register" != $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a user registration request!!"));
        }

        $stmt = $this->db->prepare("SELECT
                                      `id`
                                    FROM `server_storage`
                                    WHERE server_id = :server_id");

        $stmt->execute(array("server_id" => $request["sid"]));

        if ($stmt->rowCount() == 0) {
            return json_encode(array("flag" => 2, "msg" => "No such server exists!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "txs" => $request["txs"]));
    }

    /**
     * @return string
     */
    public function secretGeneration()
    {
        $request = $this->request;
        $EKS = "";

        $stmt = $this->db->prepare("SELECT
                                      `EKs`
                                    FROM `server_storage`
                                    WHERE server_id = :server_id");

        $stmt->execute(array("server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $EKS = $row["EKs"];
            }
        } else {
            return json_encode(array("flag" => 2, "msg" => "No such server exists!!"));
        }

        $SKaes = $this->getServerPrivateKey($request["sid"]);
        $aes = new AES($SKaes);
        $Ks = $aes->decrypt($EKS);
        $Xs = hash("sha256", $Ks . $request["txs"]);

        return json_encode(array("flag" => 1, "xs" => $Xs));
    }

    /**
     * @return string
     */
    public function secretStorage()
    {
        $request = $this->request;

        if (!isset($request["id"]) || !isset($request["sid"]) || !isset($request["xs"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id or server id or Xs found!!"));
        }

        $SKaes = $this->getServerPrivateKey($request["sid"]);
        $aes = new AES($SKaes);
        $SXi = $aes->encrypt($request["xs"]);

        $stmt = $this->db->prepare("INSERT INTO `server_user_tbl`
                                    (`user_id`,
                                     `server_id`,
                                     `SXi`)
                                        VALUES (:user_id,
                                                :server_id,
                                                :SXi)");

        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"], "SXi" => $SXi));

        $aes = new AES($SKaes);
        $xs = $aes->decrypt($SXi);

        if ($this->db->lastInsertId() > 0) {
            return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"]));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to save SXi in the database!!"));
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

        $stmt = $this->db->prepare("SELECT id FROM server_user_tbl WHERE user_id = :user_id AND server_id = :server_id");
        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {

            $stmt = $this->db->prepare("DELETE FROM server_user_tbl WHERE user_id = :user_id AND server_id = :server_id");
            $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

            if ($stmt->rowCount() > 0) {
                return json_encode(array("flag" => 1, "msg" => "Server change reverted"));
            } else {
                return json_encode(array("flag" => 2, "msg" => "Can not be reverted!!"));
            }

        } else {
            return json_encode(array("flag" => 1, "msg" => "No server change found!!"));
        }
    }
}