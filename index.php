<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});
// ## Rota para a pagina de ADMIN
$app->get('/admin', function() {

	User::verifyLogin();
    
	$page = new PageAdmin();

	$page->setTpl("index");

});
// ## Rota para a tela de login
$app->get('/admin/login', function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login");
});
// ## POST do login
$app->post('/admin/login', function() {
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});
// ## Rota para o logout
$app->get('/admin/logout', function (){
	User::logout();
	header("Location: /admin/login");
	exit;
});
// ## Rota para a tela de usuarios
$app->get("/admin/users", function(){
	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl ("users", array(
	    "users"=>$users
	));

});
// ## Rota para a criação de usuarios
$app->get("/admin/users/create", function(){
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});
// ## Rota para a remoção de usuarios
$app->get("/admin/users/:iduser/delete", function($iduser) {
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});
// ## Rota para edição de usuarios
$app->get("/admin/users/:iduser", function($iduser){
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});
// ## POST de criação de usuarios
$app->post("/admin/users/create", function () {

	User::verifyLogin();

	$user = new User();
	
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

		"cost"=>12

	]);

	$user->setData($_POST);

    $user->save();

    header("Location: /admin/users");
	exit;

});
// ## POST de edição de usuarios
$app->post("/admin/users/:iduser", function($iduser) {
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");

	exit;

});

$app->get("/admin/forgot", function (){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");
});

$app->post("/admin/forgot", function(){
	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});


// Rota para envio do codigo de recuperação de senha
$app->get("/admin/forgot/sent", function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");
});

// Rota para recuperação de senha
$app->get("/admin/forgot/reset", function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset");
});

// ## Execução do SITE
$app->run();

 ?>