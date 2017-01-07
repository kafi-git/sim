<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/5/2017
 * Time: 10:09 PM
 */

namespace RegistrationCenter\Controller;


use Core\AES;
use Core\Controller;

/**
 * Class PasswordChangeController
 * @package RegistrationCenter\Controller
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

        if (!isset($request["Xs"])) {
            return json_encode(array("flag" => 2, "msg" => "No Xs found!!"));
        }

        if (!isset($request["PBi"])) {
            return json_encode(array("flag" => 2, "msg" => "No PBi found!!"));
        }

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status found!!"));
        }

        if ("Passchange" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a password change request!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "Xs" => $request["Xs"], "PBi" => $request["PBi"]));
    }

    /**
     * @return string
     */
    public function verifyAndGenerateRequest()
    {
        $request = $this->request;

        $stmt = $this->db->prepare("SELECT id, UXi, EXi FROM rc_user_tbl WHERE user_id = :user_id AND server_id = :server_id");
        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {

            $EXi = "";
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $EXi = $row["EXi"];
            }

            $RKaes = $this->getRcPrivateKey();

            $aes = new AES($RKaes);
            $TXs = $aes->decrypt($EXi);

            $stmt = $this->db->prepare("SELECT HKs FROM rc_server_tbl WHERE server_id = :server_id");
            $stmt->execute(array("server_id" => $request["sid"]));

            if ($stmt->rowCount() > 0) {

                $HKs = "";
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($result as $row) {
                    $HKs = $row["HKs"];
                }

                $Ks = $aes->decrypt($HKs);
                $XCs = hash("sha256", $Ks . $TXs);

                if ($XCs !== $request["Xs"]) {
                    return json_encode(array("flag" => 2, "msg" => "Verification Failed. XCs != Xs!!", "XCs" => $XCs, "TXs" => $TXs));
                }

                $Rn4 = $this->getUniqueString();
                $XTs = $Rn4 . $request["PBi"];

                return json_encode(array("flag" => 1, "XTs" => $XTs, "TXs" => $TXs, "Rn4" => $Rn4, "Ks" => $Ks));

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
    public function receiveReply()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status found!!"));
        }

        if ("Complete" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a password change reply!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"]));
    }

    /**
     * @return string
     */
    public function generateSmartCardUpdateRequest()
    {
        $request = $this->request;

        if (!isset($request["Rn4"]) || !isset($request["Ks"])) {
            return json_encode(array("flag" => 2, "msg" => "No Rn4 or Ks found!!"));
        }

        $stmt = $this->db->prepare("SELECT id, UXi, EXi FROM rc_user_tbl WHERE user_id = :user_id AND server_id = :server_id");
        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {

            $UXi = "";
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $UXi = $row["UXi"];
            }

            $CTs = $request["Ks"] . $request["Rn4"];
            $RKaes = $this->getRcPrivateKey();
            $aes = new AES($RKaes);
            $TCs = $aes->decrypt($UXi);

            return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "CTs" => $CTs, "TCs" => $TCs));

        } else {
            return json_encode(array("flag" => 2, "msg" => "Database Verification Failed!!"));
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

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status found!!"));
        }

        if ("Done" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not an Acknowledgement!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"]));
    }


    /**
     * @return string
     */
    public function updateDatabase()
    {
        $request = $this->request;

        if (!isset($request["CTs"])) {
            return json_encode(array("flag" => 2, "msg" => "No CTs found!!"));
        }

        if (!isset($request["XTs"])) {
            return json_encode(array("flag" => 2, "msg" => "No XTs found!!"));
        }

        $aes = new AES($this->getRcPrivateKey());

        $XUi = $aes->encrypt($request["CTs"]);
        $XEi = $aes->encrypt($request["XTs"]);

        $stmt = $this->db->prepare("UPDATE rc_user_tbl SET UXi = :XUi, EXi = :XEi WHERE user_id = :user_id AND server_id = :server_id");
        $stmt->execute(array("XUi" => $XUi, "XEi" => $XEi, "user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {
            return json_encode(array("flag" => 1, "msg" => "Password Change Successful!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to Replace UXi and EXi with XUi and XEi!!"));
        }
    }

}