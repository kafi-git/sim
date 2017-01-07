<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/6/2017
 * Time: 12:44 AM
 */

namespace Server\Controller;


use Core\AES;
use Core\Controller;

/**
 * Class PasswordChangeController
 * @package Server\Controller
 */
class PasswordChangeController extends Controller
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
     * PasswordChangeController constructor.
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
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        if (!isset($request["TXs"])) {
            return json_encode(array("flag" => 2, "msg" => "No TXs found!!"));
        }

        if (!isset($request["XTs"])) {
            return json_encode(array("flag" => 2, "msg" => "No XTs found!!"));
        }

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status found!!"));
        }

        if ("Passchange" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a password change request!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "TXs" => $request["TXs"], "XTs" => $request["XTs"]));
    }


    /**
     * @return string
     */
    public function verifyAndSecretChange()
    {
        $request = $this->request;

        $stmt = $this->db->prepare("SELECT SXi FROM server_user_tbl WHERE user_id = :user_id AND server_id = :server_id");
        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $SXi = "";

            foreach ($result as $row) {
                $SXi = $row["SXi"];
            }

            $SKaes = $this->getServerPrivateKey($request["sid"]);
            $aes = new AES($SKaes);
            $Xs = $aes->decrypt($SXi);

            $stmt = $this->db->prepare("SELECT EKs FROM server_storage WHERE server_id = :server_id");
            $stmt->execute(array("server_id" => $request["sid"]));

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $EKs = "";

                foreach ($result as $row) {
                    $EKs = $row["EKs"];
                }

                $Ks = $aes->decrypt($EKs);

                if ($Xs !== hash("sha256", $Ks . $request["TXs"])) {
                    return json_encode(array("flag" => 2, "msg" => "Verification Failed!! Xs != h(TXs||Ks)"));
                }

                $Xcs = hash("sha256", $Ks . $request["XTs"]);
                $XSi = $aes->encrypt($Xcs);

                $stmt = $this->db->prepare("UPDATE server_user_tbl SET RSXi = SXi, SXi = :XSi WHERE user_id = :user_id AND server_id = :server_id");
                $stmt->execute(array("XSi" => $XSi, "user_id" => $request["id"], "server_id" => $request["sid"]));

                if ($stmt->rowCount() > 0) {
                    return json_encode(array("flag" => 1, "msg" => "Secret Change Successful!!"));
                } else {
                    return json_encode(array("flag" => 2, "msg" => "Failed to Replace SXi with XSi!!"));
                }

            } else {
                return json_encode(array("flag" => 2, "msg" => "Server is Not Registered!!"));
            }
        } else {
            return json_encode(array("flag" => 2, "msg" => "Database Verification Failed!!"));
        }
    }

    /**
     * @return string
     */
    public function clearCache()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        $stmt = $this->db->prepare("UPDATE server_user_tbl SET RSXi = :RSXi WHERE user_id = :user_id AND server_id = :server_id");
        $stmt->execute(array("RSXi" => "", "user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {
            return json_encode(array("flag" => 1, "msg" => "Cache Clear Successful!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Nothing to Clear!!"));
        }
    }

    /**
     * @return string
     */
    public function revert()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        $stmt = $this->db->prepare("UPDATE server_user_tbl SET SXi = RSXi, RSXi = :RSXi WHERE user_id = :user_id AND server_id = :server_id");
        $stmt->execute(array("RSXi" => "", "user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {
            return json_encode(array("flag" => 1, "msg" => "Revert Successful!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Nothing to Revert!!"));
        }
    }
}