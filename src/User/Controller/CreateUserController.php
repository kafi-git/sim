<?php
/**
 * Created by PhpStorm.
 * User: Kafi
 * Date: 12/24/2016
 * Time: 10:36 PM
 */

namespace User\Controller;


use Core\Controller;
use Home\Controller\HomeController;
use User\Resources\Views\CreateUserView;

/**
 * Class CreateUserController
 * @package User\Controller
 */
class CreateUserController extends Controller
{
    /**
     * @var
     */
    private $db;
    /**
     * @var HomeController
     */
    private $home;
    /**
     * @var CreateUserView
     */
    private $view;
    /**
     * @var
     */
    private $request;

    /**
     * CreateUserController constructor.
     * @param $db
     * @param $request
     */
    public function __construct($db, $request)
    {
        $this->db = $db;
        $this->request = $request;
        $this->home = new HomeController();
        $this->view = new CreateUserView();
    }

    /**
     *
     */
    public function index()
    {
        $this->home->head();
        $this->body();
        $this->userList();
        $this->body_bottom();
        $this->home->foot();
    }

    /**
     *
     */
    public function body()
    {
        $this->view->body($this->getUniqueString256());
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

        if (!isset($request["user_id"])) {
            return json_encode(array("flag" => 2, "msg" => "Please provide an user id."));
        }

        if (!isset($request["password"])) {
            return json_encode(array("flag" => 2, "msg" => "Please provide a password."));
        }

        if (!isset($request["recovery_contact"])) {
            return json_encode(array("flag" => 2, "msg" => "Please provide a recovery contact."));
        }

        if (!isset($request["biometric_key"])) {
            return json_encode(array("flag" => 2, "msg" => "Please provide a biometric key."));
        }

        $stmt = $this->db->prepare("SELECT
                                      `id`
                                    FROM `user_tbl`
                                    WHERE user_id = :user_id");
        $stmt->execute(array("user_id" => $request["user_id"]));

        if ($stmt->rowCount() > 0) {
            return json_encode(array("flag" => 2, "msg" => "User id already exists."));
        }

        $stmt = $this->db->prepare("INSERT INTO user_tbl
                                    (user_id,
                                     password,
                                     recovery_contact,
                                     biometric_key)
                                    VALUES (:user_id,
                                    :password,
                                    :recovery_contact,
                                    :biometric_key)");
        $stmt->execute(array("user_id" => $request["user_id"], "password" => $request["password"], "recovery_contact" => $request["recovery_contact"], "biometric_key" => $request["biometric_key"]));

        if ($this->db->lastInsertId() > 0) {
            return json_encode(array("flag" => 1, "msg" => "User added successfully.", "id" => $this->db->lastInsertId()));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Failed to add user."));
        }

    }

    /**
     * @return string
     */
    public function getUniqueBioKey()
    {
        return json_encode(array("bioKey" => $this->getUniqueString256()));
    }

    /**
     *
     */
    public function userList()
    {
        $request = $this->request;

        $id = isset($request["id"]) ? $request["id"] : "0";

        $stmt = $this->db->prepare("SELECT
                                      `id`,
                                      `user_id`,
                                      `password`,
                                      `recovery_contact`,
                                      `biometric_key`
                                    FROM `user_tbl`
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

        $stmt = $this->db->prepare("DELETE FROM user_tbl WHERE id = :id");
        $stmt->execute(array("id" => $id));

        if ($stmt->rowCount() == 1) {
            return json_encode(array("msg" => "Data deleted successfully."));
        }

        return json_encode(array("msg" => "Failed to delete data!!!"));
    }
}