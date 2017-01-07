<?php
namespace Server\Controller;

use Core\AES;
use Core\Controller;
use Home\Controller\HomeController;
use Server\Resources\Views\RegistrationView;

/**
 * Class RegistrationController
 * @package Server\Controller
 */
class RegistrationController extends Controller
{
    /**
     * @var RegistrationView
     */
    private $view;
    /**
     * @var HomeController
     */
    public $home;
    /**
     * @var string
     */
    private $basePath;
    /**
     * @var string
     */
    private $cssPath;
    /**
     * @var string
     */
    private $jsPath;
    /**
     * @var string
     */
    private $vendorPath;
    /**
     * @var \PDO
     */
    private $db;
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
        $this->home = new HomeController();
        $this->view = new RegistrationView();
        $this->basePath = $this->getBasePath();
        $this->cssPath = $this->getCssPath();
        $this->jsPath = $this->getJsPath();
        $this->vendorPath = $this->getVendorPath();
        $this->db = $db;
        $this->request = $request;
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
                                    WHERE status = 0
                                    ORDER BY id DESC ");
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $server_list[] = array("server_id" => $row["server_id"], "server_domain" => $row["server_domain"]);
            }
        }

        $this->view->body_bottom($server_list);
    }

    /**
     * @return string
     */
    public function receiveReply()
    {
        $request = $this->request;
        //check server id
        if (!isset($request["sid"]) || !isset($request["rsid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        //check server id matches
        if ($request["sid"] !== $request['rsid']) {
            return json_encode(array("flag" => 2, "msg" => "Server id does not match!!"));
        }

        //check for Ks
        if (!isset($request["Ks"]) && "" != $request["Ks"]) {
            return json_encode(array("flag" => 2, "msg" => "No Ks found!!"));
        }

        //check status is Accept
        if ("Accept" !== $request["stat"]) {
            return json_encode(array("flag" => 2, "msg" => "Not a RC Reply!!"));
        }

        return json_encode(array("flag" => 1, "sid" => $request["sid"], "Ks" => $request["Ks"]));
    }


    /**
     * @return string
     * @internal param $request
     */
    public function secretStorageAndAck()
    {
        $request = $this->request;
        //check for sid and Ks
        if (!isset($request["sid"]) || !isset($request["Ks"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id or Ks found!!"));
        }

        $aes = new AES($this->getServerPrivateKey($request["sid"]));
        $EKs = $aes->encrypt($request["Ks"]);

        $stmt = $this->db->prepare("INSERT INTO server_storage(server_id, EKs) VALUES (:server_id, :EKs)");

        $stmt->execute(array("server_id" => $request["sid"], "EKs" => $EKs));

        if ($this->db->lastInsertId() > 0) {

            $stmt = $this->db->prepare("UPDATE server_tbl SET status = 1 WHERE server_id = :server_id");

            $stmt->execute(array("server_id" => $request["sid"]));

            return json_encode(array("flag" => 1, "sid" => $request["sid"], "stat" => "Ack"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to store secret!!"));
        }

    }

    /**
     * @return string
     */
    public function revert()
    {
        $request = $this->request;

        if (!isset($request["sid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        $stmt = $this->db->prepare("SELECT id FROM server_storage WHERE server_id = :server_id");
        $stmt->execute(array("server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {

            $stmt = $this->db->prepare("DELETE FROM server_storage WHERE server_id = :server_id");
            $stmt->execute(array("server_id" => $request["sid"]));

            if ($stmt->rowCount() > 0) {

                $stmt = $this->db->prepare("SELECT id FROM server_tbl WHERE server_id = :server_id AND status = 1");
                $stmt->execute(array("server_id" => $request["sid"]));

                if ($stmt->rowCount() > 0) {
                    $stmt = $this->db->prepare("UPDATE server_tbl SET status = 0 WHERE server_id = :server_id AND status = 1");
                    $stmt->execute(array("server_id" => $request["sid"]));
                }

                return json_encode(array("flag" => 1, "msg" => "User change reverted"));
            } else {
                return json_encode(array("flag" => 2, "msg" => "Can not be reverted!!"));
            }
        } else {
            return json_encode(array("flag" => 1, "msg" => "No User change found"));
        }
    }
}