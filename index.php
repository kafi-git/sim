<?php
include_once __DIR__ . "/vendor/autoload.php";

$route = isset($_REQUEST["route"]) ? trim($_REQUEST["route"]) : "";

$db = new \PDO("mysql:dbname=simulation;host=127.0.0.1", "root", "");

$subRoute = explode("/", $route);
$subRoute[1] = isset($subRoute[1]) ? $subRoute[1] : "/";

switch ($subRoute[0]) {
    case "home":
        $obj = new Home\Controller\HomeController();
        $obj->index();
        break;

    case "create-server":

        $obj = new Server\Controller\CreateServerController($db,$_REQUEST);

        switch ($subRoute[1]) {
            case "/":
                $obj->index();
                break;

            case "add":
                echo $obj->add();
                break;

            case "unique":
                echo $obj->getUniqueID();
                break;

            case "key":
                echo $obj->getUniqueKey();
                break;

            case "list":
                echo $obj->serverList();
                break;

            case "delete":
                echo $obj->delete();
                break;
        }


        break;

    case "register-server":

        $obj = new Server\Controller\RegistrationController($db, $_REQUEST);

        switch ($subRoute[1]) {
            case "/":
                $obj->index();
                break;

            case "receive-reply":
                echo $obj->receiveReply();
                break;

            case "secret-storage-and-ack":
                echo $obj->secretStorageAndAck();
                break;

            case "revert":
                echo $obj->revert();
                break;
        }


        break;

    case "server-registration-rc":

        $obj = new RegistrationCenter\Controller\ServerRegistrationController($db, $_REQUEST);

        switch ($subRoute[1]) {
            case "/":
                //$obj->index();
                break;

            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "secret-generation-and-reply":
                echo $obj->secretGenerationAndReply();
                break;

            case "receive-ack":
                echo $obj->receiveAck();
                break;

            case "finalize":
                echo $obj->finalize();
                break;
        }


        break;

    case "registered-servers":
        $obj = new RegistrationCenter\Controller\RegisteredServersController($db);
        $obj->index();
        break;

    case "create-user":

        $obj = new User\Controller\CreateUserController($db, $_REQUEST);

        switch ($subRoute[1]) {
            case "/":
                $obj->index();
                break;

            case "add":
                echo $obj->add();
                break;

            case "bio-key":
                echo $obj->getUniqueBioKey();
                break;

            case "list":
                echo $obj->userList();
                break;

            case "delete":
                echo $obj->delete();
                break;
        }


        break;

    case "user-registration":

        $obj = new User\Controller\RegistrationController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "/":
                $obj->index();
                break;

            case "get-info":
                echo $obj->getUserInfo();
                break;

            case "get-bpi":
                echo $obj->getBpi();
                break;

            case "receive-card":
                echo $obj->receiveCard();
                break;

            case "update-card-ack":
                echo $obj->cardUpdateAndAck();
                break;

            case "revert":
                echo $obj->revert();
                break;

        }

        break;

    case "user-registration-rc":

        $obj = new RegistrationCenter\Controller\UserRegistrationController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "receive-reply":
                echo $obj->receiveReply();
                break;

            case "prepare-card":
                echo $obj->prepareCard();
                break;

            case "receive-ack":
                echo $obj->receiveAck();
                break;

            case "store-data-final":
                echo $obj->storeDataAndFinalization();
                break;

            case "revert":
                echo $obj->revert();
                break;
        }

        break;

    case "user-registration-server":

        $obj = new Server\Controller\UserRegistrationController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "secret-generation":
                echo $obj->secretGeneration();
                break;

            case "secret-storage":
                echo $obj->secretStorage();
                break;

            case "revert":
                echo $obj->revert();
                break;
        }

        break;

    case "registered-users":
        $obj = new RegistrationCenter\Controller\RegisteredUsersController($db);
        $obj->index();
        break;

    case "login":

        $obj = new User\Controller\LoginController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "/":
                $obj->index();
                break;

            case "get-info":
                echo $obj->getUserInfo();
                break;

            case "generate-request":
                echo $obj->generateRequest();
                break;

            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "mua-auth-ack":
                echo $obj->mutualAuthenticationAndAcknowledgement();
                break;

            case "session-key-gen":
                echo $obj->sessionKeyGeneration();
                break;
        }

        break;

    case "authentication":

        $obj = new Server\Controller\AuthenticationController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "verification-mua-generation":
                echo $obj->verifyAndMuaGeneration();
                break;

            case "receive-ack":
                echo $obj->receiveAcknowledgement();
                break;

            case "verification-finalization":
                echo $obj->verificationAndFinalization();
                break;

            case "session-key-gen":
                echo $obj->sessionKeyGeneration();
                break;
        }

        break;


    case "data-collection-attacker":

        $obj = new Attacker\Controller\DataCollectionController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "collect-login-request":
                echo $obj->collectLoginRequest();
                break;

            case "collect-mua":
                echo $obj->collectMua();
                break;

            case "collect-ack":
                echo $obj->collectAcknowledgement();
                break;
        }

        break;

    case "data-center":
        $obj = new Attacker\Controller\DataCenterController($db);
        $obj->index();
        break;

    case "login-log":
        $obj = new Server\Controller\LoginLogController($db);
        $obj->index();
        break;

    case "password-change":

        $obj = new User\Controller\PasswordChangeController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "/":
                $obj->index();
                break;

            case "get-info":
                echo $obj->getUserInfo();
                break;

            case "generate-request":
                echo $obj->generateRequest();
                break;

            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "update-card":
                echo $obj->updateSmartCard();
                break;

            case "clear-cache":
                echo $obj->clearCache();
                break;

            case "revert":
                echo $obj->revert();
                break;
        }
        break;

    case "password-change-rc":

        $obj = new RegistrationCenter\Controller\PasswordChangeController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "verify-generate-request":
                echo $obj->verifyAndGenerateRequest();
                break;

            case "receive-reply":
                echo $obj->receiveReply();
                break;

            case "generate-scu-request":
                echo $obj->generateSmartCardUpdateRequest();
                break;

            case "receive-ack":
                echo $obj->receiveAcknowledgement();
                break;

            case "update-database":
                echo $obj->updateDatabase();
                break;
        }
        break;

    case "password-change-server":

        $obj = new Server\Controller\PasswordChangeController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "verify-secret-change":
                echo $obj->verifyAndSecretChange();
                break;

            case "clear-cache":
                echo $obj->clearCache();
                break;

            case "revert":
                echo $obj->revert();
                break;
        }
        break;

    case "impersonation-attack":

        $obj = new Attacker\Controller\ImpersonationAttackController($db, $_REQUEST);

        switch ($subRoute[1]){

            case "/":
                $obj->index();
                break;

            case "generate-request":
                echo $obj->generateRequest();
                break;
        }

        break;

    case "server-masquerading-attack":

        $obj = new User\Controller\ServerMasqueradingAttack($db, $_REQUEST);

        switch ($subRoute[1]){
            case "/":
                $obj->index();
                break;
        }

        break;

    case "server-masquerading-attack-attacker":

        $obj = new Attacker\Controller\ServerMasqueradingAttack($db, $_REQUEST);

        switch ($subRoute[1]){

            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "verification-mua-generation":
                echo $obj->verifyAndMuaGeneration();
                break;
        }

        break;

    case "reply-attack":

        $obj = new Attacker\Controller\ReplyAttackController($db, $_REQUEST);

        switch ($subRoute[1]){
            case "/":
                $obj->index();
                break;

            case "get-m1m2-set":
                $obj->getM1M2Set();
                break;

            case "get-m1m2":
                echo $obj->getM1M2();
                break;

            case "receive-request":
                echo $obj->receiveRequest();
                break;

            case "mua-auth-ack":
                echo $obj->mutualAuthenticationAndAcknowledgement();
                break;
        }

        break;

    default:
        $obj = new Home\Controller\HomeController();
        $obj->index();
        break;
}