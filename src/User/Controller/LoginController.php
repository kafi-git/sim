<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/30/2016
 * Time: 10:48 PM
 */
namespace User\Controller;

use Core\AES;
use Core\Controller;
use Core\Converter;
use Core\ExclusiveOR;
use Home\Controller\HomeController;
use User\Resources\Views\LoginView;

/**
 * Class LoginController
 * @package User\Controller
 */
class LoginController extends Controller
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
     * @var LoginView
     */
    private $view;

    /**
     * LoginController constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->db = $db;
        $this->request = $request;
        $this->home = new HomeController();
        $this->view = new LoginView();
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

        /*$cardContent = $this->readCard($request["id"]);

        $items = explode("\n", $cardContent);

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
            return json_encode(array("flag" => 2, "msg" => "User id mismatch. Smart card verification fails!!"));
        }

        $BPi = hash("sha256", $request["pwi"] . $request["bi"]);
        $aes = new AES($request["bi"]);
        $TCs = $aes->decrypt($QXi);

        $Xs = hash("sha256", $TCs . $BPi);
        $Rn2 = $this->getUniqueString();

        $M1 = hash("sha256", $Xs . $Rn2);
        $temp = hash("sha256", $request["id"] . $Xs);

        //xor with Rn2
        $convert = new Converter();
        $binary1 = $convert->setString($temp)->stringToBinary()->getBinary();
        $binary2 = $convert->setString($Rn2)->stringToBinary()->getBinary();

        $xor = new ExclusiveOR();
        $xored = $xor->set($binary1, $binary2)->bitwiseXor()->getXored();

        $M2 = $convert->setBinary($xored)->binaryToHexadecimal()->getHexadecimal();

        return json_encode(array("flag" => 1, "id" => $request["id"], "M1" => $M1, "M2" => $M2, "Xs" => $Xs, "Rn2" => $Rn2));
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

        if (!isset($request["Xs"])) {
            return json_encode(array("flag" => 2, "msg" => "No Xs found!!"));
        }

        if (!isset($request["Rn2"])) {
            return json_encode(array("flag" => 2, "msg" => "No Rn2 found!!"));
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

        $M6 = hash("sha256", $request["Xs"] . $Rn3);

        if ($request["M4"] === $M6) {
            $M7 = hash("sha256", $request["Xs"] . $request["Rn2"] . $Rn3);

            return json_encode(array("flag" => 1, "id" => $request["id"], "sid" => $request["sid"], "M7" => $M7));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Login Failed. M4 != M6!!"));
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

        $Kses = hash("sha256", $request["Rn2"] . $request["Rn3"]);

        if ("" != $Kses) {
            return json_encode(array("flag" => 1, "msg" => "Session Key Generated!!"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to Generate Session Key!!"));
        }
    }
}