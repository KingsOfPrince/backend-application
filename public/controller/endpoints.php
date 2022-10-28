<?php
    use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

    /**
     * @OA\Post(
     *     path="/Authenticate",
     *     summary="This is to authenticate and get an access token which will be sorted as cookies.",
     *     tags={"General"},
     *     requestBody=@OA\RequestBody(
     *         request="/Authenticate",
     *         required=true,
     *         description="The credentials are passed to the server via the request body.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="username", type="string", example="admin"),
     *                 @OA\Property(property="password", type="string", example="sec!ReT423*&")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Successful")),
     *     @OA\Response(response="401", description="Invalid information")),
     *     @OA\Response(response="500", description="Internal server error"))
     * )
	 */

    $app->post("/Authenticate", function (Request $request, Response $response, $args) {
		global $api_username;
		global $api_password;

		// request the body to input string
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string
		$request_data = json_decode($request_body_string, true);

		$username = $request_data["username"];
		$password = $request_data["password"];

		//If the information which is put in is wrong, it will respond with error
		if ($username != $api_username || $password != $api_password) {
			error("Invalid credentials.", 401);
		}

		//Generate the access token and store it in the cookies.
		$token = Token::create($username, $password, time() + 1800, "localhost");

		setcookie("token", $token);

		//succsessfully logged
		message("Authentication successful.", 200);

		return $response;
	});

	$app->get("/Category/{category_id}", function (Request $request, Response $response, $args) {
		//connect to the authentication
		require "controller/require_authentication.php";

		$category_id = $args["category_id"];

		$category = get_category($category_id);

		if ($category) {
			echo json_encode($category);
		}
		else if (is_string($category)) {
			error($category, 500);
		}
		else {
			error("The ID "  . $category_id . " was not found.", 404);
		}
		return $response;
	});

	$app->post("/Category", function (Request $request, Response $response, $args) {

		require "controller/require_authentication.php";

		$request_body_string = file_get_contents("php://input");

		$request_data = json_decode($request_body_string, true);

		$name = strip_tags(addslashes($request_data["name"]));
		$active = intval($request_data["active"]);

		//if the name field is empty, respond error
		if (empty($name)) {
			error("The (name) field must not be empty.", 400);
		}
		//Limit the length of the name.
		if (strlen($name) > 500) {
			error("The name is too long. Please enter less than 500 letters.", 400);
		}
		//The active have to be an integer
		if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
			error("Please provide an integer number for the (active) field.", 400);
		}

		if ($active < 0 || $active > 1) {
			error("The active must either 0 or 1.", 400);
		}
		//Checks if everything was successful
		if (create_new_category($active, $name) === true) {
			message("The Category was successfuly created.", 201);
		}
		//respones with server error
		else {
			error("An error while saving the category.", 500);
		}
		return $response;		
	});

	$app->put("/Category/{category_id}", function (Request $request, Response $response, $args) {

		require "controller/require_authentication.php";
		
		$category_id = intval($args["category_id"]);
		
		$category = get_category($category_id);
		
		if (!$category) {
			error("No category found for the ID " . $category_id . ".", 404);
		}
		
		$request_body_string = file_get_contents("php://input");
		
		$request_data = json_decode($request_body_string, true);
		
		if (isset($request_data["name"])) {
			$name = strip_tags(addslashes($request_data["name"]));
		
			$category["name"] = $name;
		}
		if (isset($request_data["active"])) {
		
			$category["active"] = $active;
		}
		
		if (update_category($category_id, $category["name"], $category["active"])) {
			message("The Categorydata were successfully updated", 200);
		}
		else {
			error("An error occurred while saving the category data.", 500);
		}
		
		return $response;
		});
		
		$app->delete("/Category/{category_id}", function (Request $request, Response $response, $args) {
		
		require "controller/require_authentication.php";
		
		$category_id = intval($args["category_id"]);
		
		$result = delete_category($category_id);
		
		if (!$result) {
			error("No category found for the ID " . $category_id . ".", 404);
		}
		else {
			message("The category was succsessfuly deleted.", 200);
		}
		
		return $response;
		});

?>