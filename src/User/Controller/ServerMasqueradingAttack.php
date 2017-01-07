<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 1/7/2017
 * Time: 2:38 AM
 */

namespace User\Controller;


use Core\Controller;
use Home\Controller\HomeController;
use User\Resources\Views\ServerMasqueradingAttackView;

class ServerMasqueradingAttack extends Controller
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
     * @var ServerMasqueradingAttackView
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
        $this->view = new ServerMasqueradingAttackView();
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
}