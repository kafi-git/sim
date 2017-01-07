<?php
namespace Server\Controller;

use Core\Controller;
use Home\Controller\HomeController;
use Server\Resources\Views\CreateServerView;

/**
 * Class CreateServerController
 * @package Server\Controller
 */
class CreateServerController extends Controller
{
    /**
     * @var CreateServerView
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
     * CreateServerController constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->home = new HomeController();
        $this->view = new CreateServerView();
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
        $this->serverList();
        $this->body_bottom();
        $this->home->foot();
    }

    /**
     *
     */
    public function body()
    {
        $this->view->body($this->getUniqueString(), $this->getUniqueString256());
    }

    /**
     *
     */
    public function body_bottom()
    {
        $this->view->body_bottom();
    }

    /**
     * @return string
     */
    public function add()
    {
        $request = $this->request;

        if (empty($request)) {
            return json_encode(array("flag" => 2, "msg" => "No data has submitted."));
        }

        if (!isset($request["server_domain"])) {
            return json_encode(array("flag" => 2, "msg" => "Please provide a server domain."));
        }

        if (!isset($request["server_ip"])) {
            return json_encode(array("flag" => 2, "msg" => "Please provide a server ip."));
        }

        if (!isset($request["server_id"])) {
            return json_encode(array("flag" => 2, "msg" => "Please provide a server id."));
        }

        if (!isset($request["private_key"])) {
            return json_encode(array("flag" => 2, "msg" => "Please provide a private key."));
        }

        $stmt = $this->db->prepare("SELECT
                                      `id`
                                    FROM `server_tbl`
                                    WHERE server_id = :server_id");
        $stmt->execute(array("server_id" => $request["server_id"]));

        if ($stmt->rowCount() > 0) {
            return json_encode(array("flag" => 2, "msg" => "Server id already exists."));
        }

        $stmt = $this->db->prepare("INSERT INTO `server_tbl`
                                    (`server_domain`,
                                    `server_ip`,
                                     `server_id`)
                                    VALUES (:server_domain,
                                            :server_ip,
                                            :server_id)");
        $stmt->execute(array("server_domain" => $request["server_domain"], "server_ip" => $request["server_ip"], "server_id" => $request["server_id"]));

        if ($this->db->lastInsertId() > 0) {
            $this->writeServerKey($request["server_id"], $request["private_key"]);
            return json_encode(array("flag" => 1, "msg" => "Server added successfully.", "id" => $this->db->lastInsertId()));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to add server."));
        }

    }

    /**
     * @return string
     */
    public function getUniqueID()
    {
        return json_encode(array("uniqueID" => $this->getUniqueString()));
    }

    /**
     * @return string
     */
    public function getUniqueKey()
    {
        return json_encode(array("uniqueID" => $this->getUniqueString256()));
    }

    /**
     *
     */
    public function serverList()
    {
        $request = $this->request;

        $id = isset($request["id"]) ? $request["id"] : "0";

        $stmt = $this->db->prepare("SELECT
                                      `id`,
                                      `server_domain`,
                                      `server_ip`,
                                      `server_id`
                                    FROM `server_tbl`
                                    ORDER BY id DESC");
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->view->dataList($result, $id);
        }
    }

    /**
     * @return string
     */
    public function delete()
    {
        $request = $this->request;

        $id = isset($request["id"]) ? $request["id"] : "0";

        if (0 == $id) {
            return json_encode(array("msg" => "Invalid request."));
        }

        $stmt = $this->db->prepare("DELETE FROM server_tbl WHERE id = :id");
        $stmt->execute(array("id" => $id));

        if ($stmt->rowCount() == 1) {
            return json_encode(array("msg" => "Data deleted successfully."));
        }

        return json_encode(array("msg" => "Failed to delete data!!!"));
    }
}