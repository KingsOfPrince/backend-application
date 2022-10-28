<?php
    function error($message, $code) {
		$error = array("message" => $message);

		echo json_encode($error);
        //Set the response code.
		http_response_code($code);
        //Ends all Script
		die();
	}

	function message($response_message, $code) {
        //Sets the error as a JSON object.
		$response_message = array("message" => $response_message);
		echo json_encode($response_message);
        //Set the response code.
		http_response_code($code);

        //Ends all Script
		die();
	}
?>