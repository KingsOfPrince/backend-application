<?php
	//Set the content to all the endpoints in application/json.
	header("Content-Type: application/json");

	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	require __DIR__ . "/../vendor/autoload.php";
	require "model/registration.php";
	require_once "config/config.php";

	$app = AppFactory::create();

	/** 
	 * @OA\Info(title="Backend", version="1") 
	 */

	function error($message, $code) {

		$error = array("message" => $message);
		echo json_encode($error);
		http_response_code($code);
		die();
	}

	require "controller/endpoints.php";

	$app->run();
?>

//-_-//