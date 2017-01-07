<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/5/2017
 * Time: 5:10 PM
 */

namespace User\Controller;


use Core\AES;
use Core\Controller;
use Home\Controller\HomeController;
use User\Resources\Views\PasswordChangeView;

/**
 * Class PasswordChangeController
 * @package User\Controller
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
     * @var HomeController
     */
    private $home;
    /**
     * @var PasswordChangeView
     */
    private $view;

    /**
     * PasswordChangeController constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->db = $db;
        $this->request = $request;
        $this->home = new HomeController();
        $this->view = new PasswordChangeView();
    }

    /**
     *
     */
    public function index()
    {
        $this->home->head();
        $this->body();
        $this->body_bottom();
        $this->home->foot();
    }

    /**
     *
     */
    public function body()
    {
        $this->view->body();
    }

    /**
     *
     */
    public function body_bottom()
    {
        $server_list = array();

        $stmt = $this->db->prepare("SELECT
                                      `id`,
                                      `server_domain`,
                                      `server_ip`,
                                      `server_id`
                                    FROM `server_tbl`
                                    WHERE status = 1
                                    ORDER BY id DESC ");
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $server_list[] = array("server_id" => $row["server_id"], "server_domain" => $row["server_domain"]);
            }
        }

        $user_list = array();

        $stmt = $this->db->prepare("SELECT
                                      `id`,
                                      `user_id`
                                    FROM `user_tbl`
                                    WHERE status = 1
                                    ORDER BY id DESC ");
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $user_list[] = array("user_id" => $row["user_id"]);
            }
        }

        $this->view->body_bottom($user_list, $server_list);
    }

    /**
     * @return string
     */
    public function getUserInfo()
    {
        $request = $this->request;

        $stmt = $this->db->prepare("SELECT
                                      `id`,
                                      `user_id`,
                                      `password`,
                                      `recovery_contact`,
                                      `biometric_key`,
                                      `status`
                                    FROM `user_tbl`
                                    WHERE user_id = :user_id
                                    AND status = 1");

        $stmt->execute(array("user_id" => $request["id"]));

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                return json_encode(array("flag" => 1, "password" => $row["password"], "recovery_contact" => $row["recovery_contact"], "biometric_key" => $row["biometric_key"]));
            }

        } else {
            return json_encode(array("flag" => 2, "msg" => "No user info available!!"));
        }
    }

    /**
     * @return string
     */
    public function generateRequest()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["pwi"])) {
            return json_encode(array("flag" => 2, "msg" => "No password found!!"));
        }

        if (!isset($request["bi"])) {
            return json_encode(array("flag" => 2, "msg" => "No biometric key found!!"));
        }

        if (!isset($request["pwni"])) {
            return json_encode(array("flag" => 2, "msg" => "No new password found!!"));
        }

        /*$contents = $this->readCard($request["id"]);

        $items = explode("\n", $contents);

        $IDi = explode(":", $items[0]);
        $QXi = explode(":", $items[2]);*/

        $stmt = $this->db->prepare("SELECT server_id, user_id, QXi FROM card_tbl WHERE user_id = :user_id");
        $stmt->execute(array("user_id" => $request["id"]));

        $IDi = "";
        $QXi = "";

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $IDi = $row["user_id"];
                $QXi = $row["QXi"];
            }
        }

        if ($IDi !== $request["id"]) {
            return json_encode(array("flag" => 2, "msg" => "User id mismatch!!"));
        }

        $BPi = hash("sha256", $request["pwi"] . $request["bi"]);
        $aes = new AES($request["bi"]);
        $TCs = $aes->decrypt($QXi);
        $Xs = hash("sha256", $TCs . $BPi);
        $PBi = hash("sha256", $request["pwni"] . $request["bi"]);

        return json_encode(array("flag" => 1, "Xs" => $Xs, "PBi" => $PBi));
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

        if (!isset($request["CTs"])) {
            return json_encode(array("flag" => 2, "msg" => "No TCs found!!"));
        }

        if (!isset($request["TCs"])) {
            return json_encode(array("flag" => 2, "msg" => "No TCs found!!"));
        }

        if (!isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No status found!!"));
        }

        if ("Complete" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a smart card update request!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "CTs" => $request["CTs"], "TCs" => $request["TCs"]));
    }


    /**
     * @return string
     */
    public function updateSmartCard()
    {
        $request = $this->request;

        if (!isset($request["bi"])) {
            return json_encode(array("flag" => 2, "msg" => "No biometric key found!!"));
        }

        /*$contents = $this->readCard($request["id"]);

        $items = explode("\n", $contents);

        $IDi = explode(":", $items[0]);
        $SIDi = explode(":", $items[1]);
        $QXi = explode(":", $items[2]);*/

        $stmt = $this->db->prepare("SELECT server_id, user_id, QXi FROM card_tbl WHERE user_id = :user_id");
        $stmt->execute(array("user_id" => $request["id"]));

        $IDi = "";
        $SIDi = "";
        $QXi = "";

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $IDi = $row["user_id"];
                $SIDi = $row["server_id"];
                $QXi = $row["QXi"];
            }
        }

        if ($IDi !== $request["id"]) {
            return json_encode(array("flag" => 2, "msg" => "User ID mismatch!!"));
        }

        if ($SIDi !== $request["sid"]) {
            return json_encode(array("flag" => 2, "msg" => "Server ID mismatch!!"));
        }

        $aes = new AES($request["bi"]);

        if ($aes->decrypt($QXi) !== $request["TCs"]) {
            return json_encode(array("flag" => 2, "msg" => "Verification Failed!! TCs != Daes(QXi, Bi)"));
        }

        $XQi = $aes->encrypt($request["CTs"]);

        /*$this->deleteCard($request["id"]);

        $cardContent = $items[0] . "\n";
        $cardContent .= $items[1] . "\n";
        $cardContent .= "QXi:" . $XQi;*/

        $stmt = $this->db->prepare("UPDATE user_tbl SET password = :pwni, Rpassword = :pwi WHERE user_id = :user_id");
        $stmt->execute(array("pwni" => $request["pwni"], "pwi" => $request["pwi"], "user_id" => $request["id"]));


        $stmt = $this->db->prepare("UPDATE card_tbl SET RQXi = QXi, QXi = :XQi WHERE user_id = :user_id");
        $stmt->execute(array("XQi" => $XQi, "user_id" => $request["id"]));

        /*if (1 === $this->writeCard($request["id"], $cardContent)) {*/
        if ($stmt->rowCount() > 0) {
            return json_encode(array("flag" => 1, "msg" => "Smart Card Updated Successfully!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to write smart card!!"));
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

        $stmt = $this->db->prepare("UPDATE card_tbl SET RQXi = :RQXi WHERE user_id = :user_id");
        $stmt->execute(array("RQXi" => "", "user_id" => $request["id"]));

        $stmt = $this->db->prepare("UPDATE user_tbl SET Rpassword = :pwi WHERE user_id = :user_id");
        $stmt->execute(array("pwi" => "", "user_id" => $request["id"]));

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

        $stmt = $this->db->prepare("UPDATE card_tbl SET QXi = RQXi, RQXi = :RQXi WHERE user_id = :user_id");
        $stmt->execute(array("RQXi" => "", "user_id" => $request["id"]));

        $stmt = $this->db->prepare("UPDATE user_tbl SET password = Rpassword, Rpassword = :pwi WHERE user_id = :user_id");
        $stmt->execute(array("pwi" => "", "user_id" => $request["id"]));

        if ($stmt->rowCount() > 0) {
            return json_encode(array("flag" => 1, "msg" => "Revert Successful!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Nothing to Revert!!"));
        }
    }
}