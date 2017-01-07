<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/7/2017
 * Time: 2:00 AM
 */

namespace Attacker\Controller;


use Attacker\Resources\Views\ImpersonationAttackView;
use Core\AES;
use Core\Controller;
use Core\Converter;
use Core\ExclusiveOR;
use Home\Controller\HomeController;

/**
 * Class ImpersonationAttackController
 * @package Attacker\Controller
 */
class ImpersonationAttackController extends Controller
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
     * @var ImpersonationAttackView
     */
    private $view;

    /**
     * ImpersonationAttackController constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->db = $db;
        $this->request = $request;
        $this->home = new HomeController();
        $this->view = new ImpersonationAttackView();
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

        $request["bi"] = $this->getUniqueString256();
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
}