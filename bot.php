<?php

	$var = file_get_contents('!!!!.txt');
	$array = (explode("$", $var));
if (!isset($_REQUEST)) { 
  return; 
} 
  $result = curl_exec($ch);
//Строка для подтверждения адреса сервера из настроек Callback API 
$confirmation_token = ''; 

//Ключ доступа сообщества 
$token = ''; 

//Получаем и декодируем уведомление 
$data = json_decode(file_get_contents('php://input')); 

//Проверяем, что находится в поле "type" 
switch ($data->type) { 
  //Если это уведомление для подтверждения адреса... 
  case 'confirmation': 
    //...отправляем строку для подтверждения 
    echo $confirmation_token; 
    break; 

//Если это уведомление о новом сообщении... 
  case 'message_new': 
  $message = "";
    $body = $data->object->body;
  //...получаем id его автора 
    $user_id = $data->object->user_id; 
    //затем с помощью users.get получаем данные об авторе 
    $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0"));
  // Коннектимся к базе
    $mysqli = new mysqli("server","login","password","database");
    $mysqli->set_charset("utf8");
    // Проверка на наличие в бд пользователя
    $res = $mysqli->query("SELECT `user_id` FROM `hackaton` WHERE user_id = $user_id");
    $count = mysqli_num_rows($res);
    if($count == 0){
      $createUser = $mysqli->query("INSERT INTO `hackaton`(`id`, `user_id`, `status`) VALUES (NULL, '$user_id', '0')");
      $message = "Здравствуйте, что у вас случилось?";
    }
    // Получаем статус и работаем с ним
    $status = checkStatus($mysqli,$user_id); 
    switch ($status) {
    	case '0':
    		$message = 'Здравствуйте, что у вас случилось?';
    		$sql = $mysqli->query("UPDATE `hackaton` SET `status`= 1 WHERE `user_id` = $user_id");
            //$sql = $mysqli->query("UPDATE `hackaton` SET `status`= 1 WHERE `user_id` = $user_id");  
    		break;
    	case '1':
    		$y = rand(1,27);
    		for ($i=1; $i <= 27; $i++) { 
    			$pos1 = stripos($array[$i], $body);
	    		if ($pos1 === false){ 
					// если нет
				}else{
					$y = $i;
				}
				$pos1 = stripos($array[$i], mb_strtoupper($body));
	    		if ($pos1 === false){ 
					// если нет
				}else{
					$y = $i;
				}
    		}
			$message = $array[$y];		
    		break;
    	default:
    		# code...
    		break;
    }
//и извлекаем из ответа его имя 
    $user_name = $user_info->response[0]->first_name; 

//С помощью messages.send отправляем ответное сообщение 
    $request_params = array( 
   	  'user_id' => $user_id, 
      'access_token' => $token, 
      'message' => $message, 
      'v' => '5.0' 
    ); 

$get_params = http_build_query($request_params); 

file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 

//Возвращаем "ok" серверу Callback API 

echo('ok'); 

break; 

} 
// Проверка статуса
function checkStatus($mysqli,$user_id){
  $sql = $mysqli->query("SELECT user_id, status FROM `hackaton`");
  if($sql->num_rows > 0) {
      while($row = $sql->fetch_assoc()) {
        if($row['user_id'] == $user_id){
          return $row['status'];
        }
      }
  }
}
