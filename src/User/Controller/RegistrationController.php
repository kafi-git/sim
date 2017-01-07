<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/25/2016
 * Time: 6:23 PM
 */

namespace User\Controller;


use Core\AES;
use Core\Controller;
use Home\Controller\HomeController;
use User\Resources\Views\RegistrationView;

/**
 * Class RegistrationController
 * @package User\Controller
 */
class RegistrationController extends Controller
{
    /**
     * @var \PDO
     */
    private $db;
    /**
     * @var HomeController
     */
    private $home;
    /**
     * @var RegistrationView
     */
    private $view;
    /**
     * @var
     */
    private $request;

    /**
     * RegistrationController constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->db = $db;
        $this->request = $request;

        $this->home = new HomeController();
        $this->view = new RegistrationView();
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
                                    WHERE status = 0
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
                                    AND status = 0");

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
    public function getBpi()
    {
        $request = $this->request;

        if (!isset($request["pwi"]) || !isset($request["bi"])) {
            return json_encode(array("flag" => 2, "msg" => "No password or biometric key found!!"));
        }

        return json_encode(array("flag" => 1, "bpi" => hash("sha256", $request["pwi"] . $request["bi"])));
    }

    /**
     * @return string
     */
    public function receiveCard()
    {
        $request = $this->request;

        if (!isset($request["rid"]) || !isset($request["rsid"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id or server id found!!"));
        }

        if ($request["id"] !== $request["rid"]) {
            return json_encode(array("flag" => 2, "msg" => "User id mismatch!!"));
        }

        if ($request["sid"] !== $request["rsid"]) {
            return json_encode(array("flag" => 2, "msg" => "Server id mismatch!!"));
        }

        $BPci = hash("sha256", $request["pwi"] . $request["bi"]);

        /*$contents = $this->readCard($request["id"]);

        $items = explode("\n", $contents);

        $BPi = explode(":", $items[2]);*/

        $stmt = $this->db->prepare("SELECT BPi FROM card_tbl WHERE user_id = :user_id");
        $stmt->execute(array("user_id" => $request["id"]));

        $BPi = "";

        if ($stmt->rowCount() > 0)
        {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row){
                $BPi = $row["BPi"];
            }
        }

        if ($BPci !== $BPi) {
            return json_encode(array("flag" => 2, "msg" => "Biometric Information mismatch!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"]));

    }

    /**
     * @return string
     */
    public function cardUpdateAndAck()
    {
        $request = $this->request;

        if (!isset($request["id"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if (!isset($request["bi"])) {
            return json_encode(array("flag" => 2, "msg" => "No biometric key found!!"));
        }

        /*$contents = $this->readCard($request["id"]);

        $items = explode("\n", $contents);

        $IDi = $items[0];
        $SIDi = $items[1];
        //$BPi = $items[2];
        $TCs = $items[3];

        $this->writeCard($request["id"]);

        $cardContent = $IDi . "\n";
        $cardContent .= $SIDi . "\n";*/

        $stmt = $this->db->prepare("SELECT server_id, TCs FROM card_tbl WHERE user_id = :user_id");
        $stmt->execute(array("user_id" => $request["id"]));

        $SIDi = "";
        $TCs = "";

        if ($stmt->rowCount() > 0)
        {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row){
                $SIDi = $row["server_id"];
                $TCs = $row["TCs"];
            }
        }

        $aes = new AES($request["bi"]);
        $QXi = $aes->encrypt($TCs);

        //$cardContent .= "QXi:" . $QXi;

        $stmt = $this->db->prepare("UPDATE card_tbl SET TCs = :TCs, BPi = :BPi, QXi = :QXi WHERE user_id = :user_id");
        $stmt->execute(array("TCs" => "", "BPi" => "", "QXi" => $QXi, "user_id" => $request["id"]));

        /*if (1 === $this->writeCard($request["id"], $cardContent)) {*/

        if ($stmt->rowCount() > 0){
            $stmt = $this->db->prepare("UPDATE user_tbl SET status = 1 WHERE user_id = :user_id AND status = 0");
            $stmt->execute(array("user_id" => $request["id"]));

            return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $SIDi));

        } else {
            return json_encode(array("flag" => 2, "msg" => "Can not write smart card!!"));
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

        $stmt = $this->db->prepare("SELECT id FROM user_tbl WHERE user_id = :user_id AND status = 1");
        $stmt->execute(array("user_id" => $request["id"]));

        if ($stmt->rowCount() > 0) {

            $stmt = $this->db->prepare("UPDATE user_tbl SET status = 0 WHERE user_id = :user_id AND status = 1");
            $stmt->execute(array("user_id" => $request["id"]));

            if ($stmt->rowCount() > 0) {
                //$this->deleteCard($request["id"]);
                $stmt = $this->db->prepare("DELETE FROM card_tbl WHERE user_id = :user_id");
                $stmt->execute(array("user_id" => $request["id"]));
                return json_encode(array("flag" => 1, "msg" => "User change reverted"));
            } else {
                return json_encode(array("flag" => 2, "msg" => "Can not be reverted!!"));
            }
        } else {
            return json_encode(array("flag" => 1, "msg" => "No User change found"));
        }
    }
}