<?php
// Routes
/*
$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});*/
header("Access-Control-Allow-Origin:*");


function connectDB(){

   return new PDO("mysql:host=localhost;dbname=jualbuku","root","root");

}
function check_boolean($sql){
if($sql){
		return "success";
	}else{
		return "failed";
	}
}
function check_count($param){
	if ($param) {
		return "success";
	}else{
		return "failed";
	}
}
function check_session($res,$page,$args){
$session = new \SlimSession\Helper;
if(!$session->get('username')){
	return $res->withHeader('Location','/admin/form_login');
}else{
	$this->view->render($res,$page,$args);
}

}

function check_session_buyer(){
$session = new \SlimSession\Helper;
$username = $session->get('username');
$paramsession = 0;

if ($username==""){
$paramsession=0;

}else{
$paramsession=1;
}
return $paramsession;
}
function logout($res){
	$session = new \SlimSession\Helper;
	$session::destroy();
	return $res->withHeader('Location','/admin/form_login');
}
function logout_buyer($res){
	$session = new \SlimSession\Helper;
	$session::destroy();
	return $res->withHeader('Location','/login');
}
function direct_to_dashboard(){
		echo "<meta http-equiv='refresh' content='3;url=dashboard'>";
}

/* =======================start web ====================== */

$app->get("/",function($req,$res,$args){
	$query = connectDB();
	$sql = $query->query("SELECT idbook,title_book,code_book,author, price, sinopsis, photos  FROM book where 1 limit 20")->fetchAll();
	$session = new \SlimSession\Helper;
	$username = $session->get('username');
	$args = Array('paramsession'=>check_session_buyer(),'books'=>$sql,'title'=>'Liber Store','username'=>$username);

	$this->view->render($res,"smartphone/index.phtml",$args);

});

$app->get("/login",function($req,$res,$args){

	$args = Array('paramsession'=>check_session_buyer(),'title'=>'Login');
	if (check_session_buyer()>0) {
		return $res->withHeader("Location","/buyer/dashboard");
	}

	$this->view->render($res,"smartphone/login.html",$args);
});

$app->get("/register",function($req,$res,$args){
	$args = Array('paramsession'=>check_session_buyer(),'title'=>'Register');
	if (check_session_buyer()>0) {
		return $res->withHeader("Location","/buyer/dashboard");
	}
	$this->view->render($res,"smartphone/register.html",$args);
});
$app->get("/product/{id}/{author}/{titlebook}",function($req,$res,$args){
	$id = $args['id'];
	$query = connectDB();
	$sql = $query->query("SELECT idbook,title_book,code_book,author, price, sinopsis, photos FROM book where idbook='$id'")->fetch();
	$args['book']=$sql;
	$args['paramsession']=check_session_buyer();
	$session = new \SlimSession\Helper;
	$username = $session->get('username');
	$args['username']=$username;
	$args['title'] = $args['titlebook'];
	$this->view->render($res,"smartphone/detail.html",$args);
});

$app->get("/author/{author}",function($req,$res,$args){
	$author = $args['author'];
	$args['title'] = $author;
	$query = connectDB();
	$sql = $query->query("SELECT idbook,title_book,code_book,author, price, sinopsis, photos FROM book where author='$author'")->fetchAll();
	$args['books'] =$sql;
	$this->view->render($res,"smartphone/index.phtml",$args);
});

$app->get("/search/book/{booktitle}",function($req,$res,$args){
	$title = $args['booktitle'];
	$connect = connectDB();
	$sql = $connect->query("SELECT idbook, title_book, code_book,author, price, sinopsis, photos FROM book where title_book like '$title'")->fetchAll();
	$args['title']=$title;
	if ($sql) {
	$args['books'] = $sql;
	}else{
	$args['books'] = "404";
	}
	$this->view->render($res,"smartphone/index.phtml",$args);
});
/* ======================= end web ======================= */ 

/* ======================= web user ====================== */

$app->post("/buyer/register",function($req,$res,$args){
	$query = connectDB();
	$data = $req->getParsedBody();
	$username = $data['username'];
	$email = $data['email'];
	$password = $data['password'];
	$token = substr(md5($username.$email.$password),1,10);
	$sqlstatement = "INSERT INTO account(username,email,password,token,date_account) values('$username','$email','$password','$token',current_date)";
	// echo $sqlstatement;
	$sql = $query->exec($sqlstatement);
	// echo $sql;
	// $session = new \SlimSession\Helper;
	// echo $sql;
	if($sql){
		return $res->withHeader('Location','/confirm-email');
	}else{
		return $res->withHeader('Location','/error-regis');
	}
});

$app->post("/buyer/login",function($req,$res,$args){
	$query = connectDB();
	$data = $req->getParsedBody();
	$username = $data['username'];
	$password = $data['password'];
	$sql = $query->query("SELECT token FROM account WHERE username='$username' and password='$password' and status=1")->fetchAll(PDO::FETCH_OBJ);	
	$session = new \SlimSession\Helper;
	if ($sql) {
		$session->set('username',$username);
		return $res->withHeader('Location','/buyer/dashboard');
	}else{
		return $res->withHeader('Location','/buyer/error-login');
	}
});

$app->get('/buyer/buy/{idbook}/{buyer}',function($req,$res,$args){
	$idbook = $args['idbook'];
	$buyer = $args['buyer'];
	$connect = connectDB();
	$checkprofile = $connect->query("SELECT idaccount,account_idaccount,idprofile FROM account,profile WHERE account.idaccount=profile.account_idaccount and account.username='$buyer'")->fetch(PDO::FETCH_OBJ);	
	if ($checkprofile) {
		$sql = $connect->exec("INSERT INTO mybook (date_buy,status,profile_idprofile,profile_account_idaccount,idbook) values(current_date(),0,$checkprofile->idprofile,$checkprofile->account_idaccount,$idbook)");
		if ($sql) {
			return $res->withHeader('Location','/buyer/dashboard');
		}else{
			return $res->withHeader('Location','/buyer/error-login');
		}
	}else{
		return $res->withHeader('Location','/buyer/form_update_profile');
	}
});


$app->get('/confirm-email',function($req,$res,$args){
	$args = Array('title'=>'Confirm Email');
	return $this->view->render($res,"user/confirm-email.twig",$args);
});
$app->get('/error-regis',function($req,$res,$args){
	$args = Array('title'=>'Error');
	return $this->view->render($res,"user/error-regis.twig",$args);
});
$app->get('/buyer/dashboard',function($req,$res,$args){
	$session = new \SlimSession\Helper;
$username = $session->get('username');
$connect = connectDB();
$mybook = $connect->query("SELECT photos,idmy,mybook.idbook,title_book,price,profile_account_idaccount,mybook.status FROM account,book,mybook where book.idbook=mybook.idbook and account.idaccount=mybook.profile_account_idaccount and mybook.status=1 and mybook.profile_account_idaccount=(select idaccount from account where username='$username')")->fetchAll(PDO::FETCH_OBJ);
$bookorder = $connect->query("SELECT photos,idmy,mybook.idbook,title_book,price,profile_account_idaccount,mybook.status FROM account,book,mybook where book.idbook=mybook.idbook and account.idaccount=mybook.profile_account_idaccount and mybook.status=0 and mybook.profile_account_idaccount=(select idaccount from account where username='$username')")->fetchAll(PDO::FETCH_OBJ);
	// return json_encode($bookorder);
	// $args = Array('mybooks'=>$mybook,'books'=>$bookorder,'paramsession'=>check_session_buyer());
	$args['mybooks']=$mybook;
	$args['books']=$bookorder;
	$args['paramsession']=check_session_buyer();
	$args['title'] = 'Dashboard';
	if ($username==null) {
		return $res->withHeader('Location','/login');
	}
	return $this->view->render($res,"user/dashboard.twig",$args);
});
$app->get('/buyer/form_update_profile',function($req,$res,$args){
	$connect = connectDB();
	$session = new \SlimSession\Helper;
	$username = $session->get('username');
	$sql = $connect->query("SELECT * FROM profile where account_idaccount=(select idaccount from account where username='$username')")->fetch(PDO::FETCH_OBJ);
	$args = Array('paramsession'=>check_session_buyer(),'dataprofile'=>$sql,'title'=>'Update Profile');
	if ($username==null) {
		return $res->withHeader('Location','/login');
	}
	return $this->view->render($res,"user/update_profile.twig",$args);
});
$app->post('/buyer/update_profile',function($req,$res,$args){
	$connect = connectDB();
	$session = new \SlimSession\Helper;
	$username = $session->get('username');
	$data = $req->getParsedBody();
	$fullname = $data['fullname'];
	$place = $data['address1']." ".$data['address2'];
	$address1 = $data['address1'];
	$address2 = $data['address2'];
	$phone = $data['phone'];
	$postal_code = $data['postal_code'];
	$query = "SELECT idprofile FROM profile where account_idaccount=(select idaccount from account where username='$username')";
	$update = "UPDATE profile set full_name='$fullname',address1='$address1',address2='$address2',phone='$phone',postal_code='$postal_code',date_profile=current_date() where account_idaccount=(select idaccount from account where username='$username')";
	$insert = "INSERT INTO profile (full_name,address1,address2,phone,postal_code,account_idaccount,date_profile) values('$fullname','$address1','$address2','$phone','$postal_code',(select idaccount from account where username='$username'),current_date())";
	$checkprofile = $connect->query("SELECT idprofile FROM profile where account_idaccount=(select idaccount from account where username='$username')")->rowCount();
	$sql="";
	if ($checkprofile>0) {
		$sql = $connect->exec("UPDATE profile set full_name='$fullname',address1='$address1',address2='$address2',phone='$phone',postal_code='$postal_code',date_profile=current_date() where account_idaccount=(select idaccount from account where username='$username')");
	}else{
		$sql = $connect->exec("INSERT INTO profile (full_name,address1,address2,phone,postal_code,account_idaccount,date_profile) values('$fullname','$address1','$address2','$phone','$postal_code',(select idaccount from account where username='$username'),current_date())");
	}
	if ($sql) {
		return $res->withHeader('Location','/buyer/form_update_profile');
	}
	return $sql;
});
$app->get("/buyer/dashboard/book/detail/{idbook}",function($req,$res,$args){
		$id = $args['idbook'];
	$query = connectDB();
	$sql = $query->query("SELECT idbook,title_book,code_book,author, price, sinopsis, photos FROM book where idbook='$id'")->fetch();
	$args['book']=$sql;
	$args['paramsession']=check_session_buyer();
	$session = new \SlimSession\Helper;
	$username = $session->get('username');
	$args['username']=$username;
	$args['title'] = $args['titlebook'];
	if ($username==null) {
		return $res->withHeader('Location','/login');
	}
		return $this->view->render($res,"user/detail_book.twig",$args);
});

$app->get('/buyer/error-login',function($req,$res,$args){
	return $this->view->render($res,"user/error-login.twig",$args);
});
$app->get('/buyer/logout',function($req,$res,$args){
	return logout_buyer($res);
});

/* ======================= end web user ================== */





/*=======================Start Admin =====================*/

$app->get("/admin/form_login",function($req,$res,$args){
	$this->view->render($res,"login.html",$args);
});
$app->get("/admin",function($req,$res,$args){
	$this->view->render($res,"login.html",$args);
});

$app->get("/admin/dashboard",function($req,$res,$args){
$args['title']='Admin';
$args['admin']='Bill Tanthowi Jauhari';
$session = new \SlimSession\Helper;
if(!$session->get('usernameadmin')){
	return $res->withHeader('Location','/admin/form_login');
}else{
	$this->view->render($res,"dashboard.twig",$args);
}

});

$app->get("/admin/bookorder/delete/{idmy}",function($req,$res,$args){
	$connect = connectDB();
	$idmy = $args['idmy'];
	$sql = $connect->exec("DELETE FROM mybook where idmy='$idmy'");
	return $res->withHeader("Location",'/admin/book_verification');
});
$app->get("/admin/book/delete/{idbook}",function($req,$res,$args){
		$connect = connectDB();
	$idbook = $args['idbook'];
	$sql = $connect->exec("DELETE FROM book where idbook='$idbook'");
	return $res->withHeader("Location",'/admin/upload_book');
});

$app->get("/admin/users_verification",function($req,$res,$args){
$args['title']='Admin';
$args['admin']='Bill Tanthowi Jauhari';
$query = connectDB();
$sql = $query->query("SELECT * FROM account where 1")->fetchAll(PDO::FETCH_OBJ);
$args['datausers']=$sql;
$session = new \SlimSession\Helper;
if($session->get('usernameadmin')==""){
	return $res->withHeader('Location','/admin/form_login');
}else{
	return $this->view->render($res,"table-users.twig",$args);
}

});


$app->get('/admin/verified/{username}', function($req,$res,$args){
	$username = $args['username'];
	$connect = connectDB();
	$sql = $connect->query("UPDATE account set status=1 where username='$username'");
	return $res->withHeader('Location','/admin/users_verification');
});

$app->get('/admin/unverified/{username}', function($req,$res,$args){
	$username = $args['username'];
	$connect = connectDB();
	$sql = $connect->query("UPDATE account set status=0 where username='$username'");
	return $res->withHeader('Location','/admin/users_verification');
});


$app->get("/admin/logout",function($req,$res,$args){
	return logout($res);
});

$app->get("/admin/upload_book[/{pagestart}/{pageend}]",function($req,$res,$args){
$query = connectDB();
$sq="";
	if (isset($args['pagestart']) && isset($args['pageend'])) {
		$pagestart = --$args['pagestart'];
		$pageend = $args['pageend'];
	$sql = "SELECT * FROM book WHERE 1 limit $pagestart,$pageend";
	}else{
	$sql = "SELECT * FROM book WHERE 1 limit 10";
	}
	
$args['title']='Admin';
$args['admin']='Bill Tanthowi Jauhari';
$sql = $query->query($sql);
$row = $sql->fetchAll(PDO::FETCH_OBJ);
$args['databook']=$row;
$session = new \SlimSession\Helper;
if(!$session->get('usernameadmin')){
	return $res->withHeader('Location','/admin/form_login');
}else{
	$this->view->render($res,"upload-book.twig",$args);
}

});


$app->get('/admin/editbook/{idbook}',function($req,$res,$args){
$query = connectDB();
$idbook = $args['idbook'];
$sql= "SELECT * FROM book where idbook='$idbook'";
$args['title']='Admin';
$args['admin']='Bill Tanthowi Jauhari';
$sql = $query->query($sql);
$row = $sql->fetch(PDO::FETCH_OBJ);
$args['databook']=$row;

$session = new \SlimSession\Helper;
if(!$session->get('usernameadmin')){
	return $res->withHeader('Location','/admin/form_login');
}else{
	$this->view->render($res,"editbook.twig",$args);
}
});

$app->get("/admin/book_verification",function($req,$res,$args){
$args['title']='Admin';
$args['admin']='Bill Tanthowi Jauhari';
$session = new \SlimSession\Helper;
$connect = connectDB();
$books = $connect->query("SELECT idmy,mybook.idbook,title_book,price,code_book,author,publisher,photos,profile_account_idaccount,mybook.status,username,date_release FROM account,book,mybook where book.idbook=mybook.idbook and account.idaccount=mybook.profile_account_idaccount")->fetchAll(PDO::FETCH_OBJ);
$args['books']=$books;
if(!$session->get('usernameadmin')){
	return $res->withHeader('Location','/admin/form_login');
}else{
	$this->view->render($res,"table-book.twig",$args);
}
});
$app->get("/admin/book_verification/verified/{idmy}",function($req,$res,$args){
	$connect = connectDB();
	$idmy = $args['idmy'];
	$sql = $connect->exec("UPDATE mybook SET status=1 where idmy='$idmy'");
	return $res->withHeader("Location","/admin/book_verification");
});
$app->get("/admin/book_verification/unverified/{idmy}",function($req,$res,$args){
	$connect = connectDB();
	$idmy = $args['idmy'];
	$sql = $connect->exec("UPDATE mybook SET status=0 where idmy='$idmy'");
	return $res->withHeader("Location","/admin/book_verification");
});
$app->get("/admin/book_verification/delete/{idmy}",function($req,$res,$args){
	$connect = connectDB();
	$idmy = $args['idmy'];
	$sql = $connect->exec("DELETE FROM mybook where idmy='$idmy'");
	return $res->withHeader("Location","/admin/book_verification");

});


/*=======================end Form========================*/

/*=======================start route ADMIN ===============*/
$app->get("/admin/data_account",function($req,$res,$args){
	
$query = connectDB();
$sql = "SELECT email,username,password,token,activation_code FROM account, profile WHERE idaccount=account_idaccount";
$sql = $query->query($sql);
$row = $sql->fetchAll(PDO::FETCH_OBJ);
$num_row = $sql->rowCount();
$response = '{"result":"'.check_boolean($row).'","num_rows":'.$num_row.',"data_token":'.json_encode($row).'}';
	return $response;
});

$app->post("/admin/login_admin",function($req,$res,$args){
$data = $req->getParsedBody();
$username = $data['username'];
$password = $data['password'];
$query = connectDB();
$sql = "SELECT id_admin FROM admin WHERE username='$username' and password='$password'";
$execute = $query->query($sql)->fetch();
$session = new \SlimSession\Helper;
if($execute){
	$session->set('usernameadmin', $username);
	return $res->withHeader('Location','/admin/dashboard');
}else{
	return $res->withHeader('Location','/admin/form_login');
}


// echo json_encode($execute);
});

$app->get('/admin/get_book_data[/{id_book}]',function($req,$res,$args){
	$query = connectDB();
	$sql = null;
	if (isset($args['id_book'])) {
		$id = $args['id_book'];
		$sql = $query->query("SELECT idbook,title_book,code_book,price,date_release,author,stock,publisher,photos FROM book WHERE idbook=$id")->fetchAll(PDO::FETCH_OBJ);
	}else{
		$sql = $query->query("SELECT idbook,title_book,code_book,price,date_release,author,stock,publisher,photos FROM book order by date_release");
		$row = $sql->fetchAll(PDO::FETCH_OBJ);
		$numrow = $sql->rowCount();
	}
	$response = '{"result":"'.check_boolean($sql).'","num_rows":'.$numrow.',"data_token":'.json_encode($row).'}';
	return $response;

});
$app->get('/admin/get_bookorder',function($req,$res,$args){
	$query = connectDB();
	$sql = $query->query("SELECT mybook.code_book as code,title_book,price,idbook,idaccount,idmy,username,date_buy,status,publisher FROM account,book,mybook WHERE book.code_book=mybook.code_book and mybook.token=account.token");
	$row = $sql->fetchAll(PDO::FETCH_OBJ);
	$num_row = $sql->rowCount();
	$response = '{"result":"'.check_boolean($row).'","num_rows":'.$num_row.',"data_token":'.json_encode($row).'}';
	return $response;	
});
$app->post('/admin/update_status_book',function($req,$res,$args){
	$query = connectDB();
	$data = $req->getParsedBody();
	$id = $data['id'];
	$status = $data['status'];
	if ($status==0) {
		$status=1;
	}else{
		$status=0;
	}
	$sql = "UPDATE mybook SET status=$status WHERE idmy=$id";
	$sql = $query->exec($sql);
	return check_boolean($sql);
});
/*=======================end route ADMIN ===============*/



/*================ start rest ===========================*/
$app->post("/user/register",function($req,$res,$args){
	$query = connectDB();
	$data = $req->getParsedBody();
	$username = $data['username'];
	$email = $data['email'];
	$password = $data['password'];
	$token = substr(md5($username.$email.$password),1,10);
	$sqlstatement = "INSERT INTO account(username,email,password,token,date_account) values('$username','$email','$password','$token',current_date)";
	$sql = $query->exec($sqlstatement);
	return check_boolean($sqlstatement);
});


$app->post("/user/login",function($req,$res,$args){
	$query = connectDB();
	$data = $req->getParsedBody();
	$username = $data['username'];
	$password = $data['password'];
	$sql = $query->query("SELECT token FROM account WHERE username='$username' and password='$password'")->fetchAll(PDO::FETCH_OBJ);	
	$response = '{"result":"'.check_boolean($sql).'","data_token":'.json_encode($sql).'}';
	return $response;
	/*if($sql){
		return json_encode($sql);
	}else{
		return false;
	}*/
});
$app->get("/user/login/{username}/{password}",function($req,$res,$args){
	$query = connectDB();
	// $data = $req->getParsedBody();
	$username = $args['username'];
	$password = $args['password'];
	$sql = $query->query("SELECT token FROM account WHERE username='$username' and password='$password'")->fetchAll(PDO::FETCH_OBJ);
	$response = '{"result":"'.check_boolean($sql).'","data_token":'.json_encode($sql).'}';
	return $response;
	
});

$app->post("/admin/simpan_buku",function($req,$res,$args){
	$query = connectDB();
	$data = $req->getParsedBody();
	$title = $data['title_book'];
	$code = $data['code_book'];
	$price = $data['price'];
	$author = $data['author'];
	$publisher = $data['publisher'];
	$stock = $data['stock'];
	$sinopsis = $data['sinopsis'];
	$dir = 'assets/';
	$storage = new \Upload\Storage\FileSystem($dir);
$file = new \Upload\File('uploads', $storage);
$new_filename = uniqid();
$file->setName($new_filename);
$file->addValidations(array(
    // new \Upload\Validation\Mimetype('image/jpg'),
  new \Upload\Validation\Size('5M')
));
$datafile = array(
    'name'       => $file->getNameWithExtension(),
    'extension'  => $file->getExtension(),
    'mime'       => $file->getMimetype(),
    'size'       => $file->getSize(),
    'md5'        => $file->getMd5(),
    'dimensions' => $file->getDimensions()
);
try {
  
    $file->upload();
} catch (\Exception $e) {
    $errors = $file->getErrors();
}
	$link = $dir.$datafile['name'];
	$sql = "INSERT INTO book(title_book,code_book,price,date_release,author,publisher,stock,photos,sinopsis) values('$title','$code','$price',current_date,'$author','$publisher','$stock','$link','$sinopsis')";
	$sql = $query->exec($sql);
	// echo $link;
	// return check_boolean($sql);
if (check_boolean($sql)=="success") {
	echo "<meta http-equiv='refresh' content='1;url=upload_book'>";
	echo "<center><strong>Success! You Will Redirect in 1 second</strong></center>";
}else{
	echo "<meta http-equiv='refresh' content='1;url=upload_book'>";
	echo "<center><strong>Failed! Please try again</strong></center>";
}
	
	// return $sql;
});


$app->post("/admin/update_buku",function($req,$res,$args){
	$connect = connectDB();
	$data = $req->getParsedBody();
	$title_book = $data['title_book'];
	$code_book = $data['code_book'];
	$phone = $data['phone'];
	$price = $data['price'];
	$author = $data['author'];
	$publisher = $data['publisher'];
	$stock = $data['stock'];
	$sinopsis = $data['sinopsis'];
	$sql = "UPDATE book set title_book='$title_book',code_book='$code_book',phone='$phone',price='$price',author='$author',publisher='$publisher',stock='$stock',sinopsis='$sinopsis'";
	$sql = $connect->query($sql);
});


$app->get("/user/get_book_list",function($req,$res,$args){
	$query = connectDB();
	$sql = $query->query("SELECT title_book,code_book,price,sinopsis,photos FROM book WHERE 1")->fetchAll(PDO::FETCH_OBJ);
	return json_encode($sql);
});
$app->get("/user/get_data_profile",function($req,$res,$args){
	$query = connectDB();
	$sql = $query->query("SELECT full_name,date_profile,place,phone,postal_code,account_idaccount,token FROM profile,account WHERE idaccount=account_idaccount ");
});
$app->post("/user/get_detail_book",function($req,$res,$args){
	$query = connectDB();
	$data = $req->getParsedBody();
	$token = $data['token'];
	$code_book = $data['code_book'];
	$sql = $query->query("SELECT title_book,code_book,author,price,stock,photos,publisher FROM book WHERE code_book='$code_book'")->fetchAll(PDO::FETCH_OBJ);
	$response = '{"result":"'.check_boolean($sql).'","data_token":'.json_encode($sql).'}';
	return $response;
});
$app->get("/user/get_detail_book/{token}/{code_book}",function($req,$res,$args){
	$query = connectDB();
	// $data = $req->getParsedBody();
	$token = $args['token'];
	$code_book = $args['code_book'];
	$sql = $query->query("SELECT title_book,code_book,author,price,stock,photos FROM book,account WHERE token='$token' and code_book='$code_book'")->fetchAll(PDO::FETCH_OBJ);
	$response = '{"result":"'.check_boolean($sql).'","data_token":'.json_encode($sql).'}';
	return $response;
});

$app->get("/user/form",function($req,$res,$args){
	$this->renderer->render($res,"form.phtml",$args);
});
$app->post("/user/upload_photo",function($req,$res,$args){
$storage = new \Upload\Storage\FileSystem('assets/');
$file = new \Upload\File('foo', $storage);
$new_filename = uniqid();
$file->setName($new_filename);
$file->addValidations(array(
    new \Upload\Validation\Mimetype('image/png'),
  new \Upload\Validation\Size('5M')
));
$data = array(
    'name'       => $file->getNameWithExtension(),
    'extension'  => $file->getExtension(),
    'mime'       => $file->getMimetype(),
    'size'       => $file->getSize(),
    'md5'        => $file->getMd5(),
    'dimensions' => $file->getDimensions()
);
try {
  
    $file->upload();
} catch (\Exception $e) {
    $errors = $file->getErrors();
}
});
$app->post("/user/update_profile",function($req,$res,$args){
	$data = $req->getParsedBody();
	$query = connectDB();
	$fullname = $data['fullname'];
	$phone = $data['phone'];
	$place = $data['place'];
	$born = $data['born'];
	$postal_code = $data['postal_code'];
	$token = $data['token'];
	$activation_code=null;
	for ($i=0; $i <6 ; $i++) {
		$activation_code .= "".rand(1,9);
	}
	$sql = "INSERT INTO profile (full_name,phone,place,postal_code,account_idaccount,born,status,activation_code) values('$fullname','$phone','$place','$postal_code',(SELECT idaccount FROM account WHERE token='$token'),'$born',0,$activation_code)";
	$sql =  $query->exec($sql);
	// echo $sql;
	return check_boolean($sql);
});
$app->get("/user/check_profile/{token}",function($req,$res,$args){
	$query = connectDB();
	$token = $args['token'];
	$sql = $query->query("SELECT count(idprofile) as profilecolumn,status FROM profile,account WHERE idaccount=account_idaccount and token='$token'")->fetchAll(PDO::FETCH_OBJ);
	//butuh num rows
	$response = '{"result":"'.check_boolean($sql).'","data_token":'.json_encode($sql).'}';
	return $response;
});
$app->get("/user/find_profile/{token}",function($req,$res,$args){
	$query = connectDB();
	$token = $args['token'];
	$sql = $query->query("SELECT full_name,place,phone,postal_code,born FROM profile WHERE account_idaccount=(SELECT idaccount
		 FROM account WHERE token='$token')")->fetchAll(PDO::FETCH_OBJ);
	$response = '{"result":"'.check_boolean($sql).'","data_token":'.json_encode($sql).'}';
	return $response;
});
$app->post("/user/buy_book", function($req,$res,$args){
	$data = $req->getParsedBody();
	$code_book = $data['code_book'];
	$token = $data['token'];
	$query = connectDB();
	$sql = $query->exec("INSERT INTO mybook(code_book,date_buy,status,token) values('$code_book',CURRENT_TIMESTAMP,0,'$token')");
	$response = '{"result":"'.check_boolean($sql).'","data_token":'.json_encode($sql).'}';
	return $response;
});
$app->post("/user/activate_account",function($req,$res,$args){
	
	$data = $req->getParsedBody();
	$activation_code = $data['activation_code'];
	$query = connectDB();
	$sql = $query->exec("UPDATE profile SET status=1 WHERE activation_code='$activation_code'");
	$response = '{"result":"'.check_boolean($sql).'"}';
	return $response;

});

$app->get("/user/activation_code",function(){
	$activation_code=null;
	for ($i=0; $i <6 ; $i++) {
		$activation_code .= "".rand(1,9);
	}
	echo $activation_code;
});
$app->get("/user/get_mybook/{token}",function($req,$res,$args){
	$query = connectDB();
	$token = $args['token'];
	$sql = $query->query("SELECT mybook.code_book,title_book,price,idbook,idaccount,date_buy,status FROM account,book,mybook WHERE book.code_book=mybook.code_book and mybook.token=account.token and account.token='$token'")->fetchAll(PDO::FETCH_OBJ);
	$response = '{"result":"'.check_boolean($sql).'","data_token":'.json_encode($sql).'}';
	return $response;	
});

/*=========================end rest====================*/

