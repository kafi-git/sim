<?php
namespace RegistrationCenter\Controller;

use Core\AES;
use Core\Controller;

/**
 * Class ServerRegistrationController
 * @package RegistrationCenter\Controller
 */
class ServerRegistrationController extends Controller
{

    /**
     * @var string
     */
    private $basePath;
    /**
     * @var \PDO
     */
    private $db;
    /**
     * @var
     */
    private $request;

    /**
     * ServerRegistrationController constructor.
     * @param \PDO $db
     * @param $request
     */
    public function __construct(\PDO $db, $request)
    {
        $this->basePath = $this->getBasePath();
        $this->db = $db;
        $this->request = $request;
    }

    /**
     * @return string
     * @internal param $request
     */
    public function receiveRequest()
    {
        $request = $this->request;
        //check if request received
        if (!isset($request["sid"]) || !isset($request["stat"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id or status found!!"));
        }

        //check if state is register
        if ($request["stat"] != "Register") {
            return json_encode(array("flag" => 2, "msg" => "Not a server registration request!!"));
        }

        //check if server is already registered
        $stmt = $this->db->prepare("SELECT id FROM rc_server_tbl WHERE server_id = :server_id");

        $stmt->execute(array("server_id" => $request["sid"]));

        if ($stmt->rowCount() > 0) {

            return json_encode(array("flag" => 2, "msg" => "Server already registered!!"));
        }

        return json_encode(array("flag" => 1, "sid" => $request["sid"]));

    }


    /**
     * @return string
     * @internal param $request
     */
    public function secretGenerationAndReply()
    {
        $request = $this->request;
        //generate random string Rn1
        $Rn1 = $this->getUniqueString();
        //generate hash: h(SIDi||Rn1)
        $Ks = hash("sha256", $request["sid"] . $Rn1);

        //reply to server
        if ("" != $Ks) {
            return json_encode(array("flag" => 1, "sid" => $request["sid"], "Ks" => $Ks, "stat" => "Accept"));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Process server registration failed!!"));
        }
    }

    /**
     * @return string
     * @internal param $request
     */
    public function receiveAck()
    {
        $request = $this->request;

        //check if acknowledgement received
        if (!isset($request["sid"]) || !isset($request["asid"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id found!!"));
        }

        //check server id matches
        if ($request["sid"] !== $request['asid']) {
            return json_encode(array("flag" => 2, "msg" => "Server id does not match!!"));
        }

        //check if state is acknowledgement
        if (!isset($request["stat"]) || $request["stat"] != "Ack") {
            return json_encode(array("flag" => 2, "msg" => "Not a server acknowledgement!!"));
        }

        return json_encode(array("flag" => 1, "sid" => $request["sid"]));
    }

    /**
     * @return string
     * @internal param $request
     */
    public function finalize()
    {
        $request = $this->request;
        //check for sid and Ks
        if (!isset($request["sid"]) || !isset($request["Ks"])) {
            return json_encode(array("flag" => 2, "msg" => "No server id or Ks found!!"));
        }

        $aes = new AES($this->getRcPrivateKey());
        $HKs = $aes->encrypt($request["Ks"]);

        $stmt = $this->db->prepare("INSERT INTO rc_server_tbl(server_id, HKs) VALUES (:server_id, :HKs)");

        $stmt->execute(array("HKs" => $HKs, "server_id" => $request["sid"]));

        if ($this->db->lastInsertId() > 0) {
            return json_encode(array("flag" => 1, "msg" => "Server registration completed."));
        } else {
            return json_encode(array("flag" => 2, "msg" => "Registration center can not save HKs!!!"));
        }

    }

}