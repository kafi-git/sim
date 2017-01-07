<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 01/06/2017
 * Time: 10:50 PM
 */
namespace Attacker\Controller;

use Core\AES;
use Core\Controller;
use Core\Converter;
use Core\ExclusiveOR;
use Home\Controller\HomeController;
use Attacker\Resources\Views\ReplyAttackView;

/**
 * Class ReplyAttackController
 * @package Attacker\Controller
 */
class ReplyAttackController extends Controller
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
     * @var ReplyAttackView
     */
    private $view;

    /**
     * ReplyAttackController constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->db = $db;
        $this->request = $request;
        $this->home = new HomeController();
        $this->view = new ReplyAttackView();
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


    public function getM1M2Set()
    {
        $request = $this->request;

        $m1m2_list = array();

        $stmt = $this->db->prepare("SELECT `id` FROM `attacker_tbl` WHERE user_id = :user_id AND server_id = :server_id ORDER BY id DESC");
        $stmt->execute(array("user_id" => $request["id"], "server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $m1m2_list[] = $row["id"];
            }
        }

        $this->view->m1m2($m1m2_list);
    }


    public function getM1M2()
    {
        $request = $this->request;

        $M1 = "";
        $M2 = "";

        $stmt = $this->db->prepare("SELECT M1, M2 FROM `attacker_tbl` WHERE id = :id");
        $stmt->execute(array("id" => $request["id"]));

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $M1 = $row["M1"];
                $M2 = $row["M2"];
            }
        }

        echo json_encode(array("m1" => $M1, "m2" => $M2));
    }

    /**
     * @return string
     */
    public function receiveRequest()
    {
        $request = $this->request;

        if (!isset($request["aid"])) {
            return json_encode(array("flag" => 2, "msg" => "No user id found!!"));
        }

        if ($request["id"] !== $request["aid"]) {
            return json_encode(array("flag" => 2, "msg" => "Login failed. User id mismatch!!"));
        }

        if (!isset($request["asid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        if ($request["sid"] !== $request["asid"]) {
            return json_encode(array("flag" => 2, "msg" => "Login failed. Server id mismatch!!"));
        }

        if (!isset($request["M4"])) {
            return json_encode(array("flag" => 2, "msg" => "No M4 found!!"));
        }

        if (!isset($request["M5"])) {
            return json_encode(array("flag" => 2, "msg" => "No M5 found!!"));
        }

        if (!isset($request["stat"]) || "Auth" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a authentication request!!"));
        }

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "M4" => $request["M4"], "M5" => $request["M5"]));
    }

    /**
     * @return string
     */
    public function mutualAuthenticationAndAcknowledgement()
    {
        $request = $this->request;

        $temp = hash("sha256", $request["id"] . $request["Xs"]);

        $converter = new Converter();
        $binary1 = $converter->setString($temp)->stringToBinary()->getBinary();
        $binary2 = $converter->setHexadecimal($request["M5"])->hexadecimalToBinary()->getBinary();

        $xor = new ExclusiveOR();
        $xored = $xor->set($binary1, $binary2)->bitwiseXor()->getXored();

        $Rn3 = $converter->setBinary($xored)->binaryToString()->getString();

        $M7 = hash("sha256", $request["Xs"] . $request["Rn2"] . $Rn3);

        return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "M7" => $M7, "Rn3" => $Rn3));
    }

    /**
     * @return string
     */
    /*public function sessionKeyGeneration()
    {
        $request = $this->request;

        if (!isset($request["Rn2"])) {
            return json_encode(array("flag" => 2, "msg" => "No Rn2 found!!"));
        }

        if (!isset($request["Rn3"])) {
            return json_encode(array("flag" => 2, "msg" => "No Rn3 found!!"));
        }

        $Kses = hash("sha256", $request["Rn2"] . $request["Rn3"]);

        if ("" != $Kses) {
            return json_encode(array("flag" => 1, "msg" => "Session Key Generated!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to Generate Session Key!!"));
        }
    }*/
}