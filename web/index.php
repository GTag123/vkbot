<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Our web handlers

$app->get('/', function() use($app) {
  return "Hello world";
});

$app->post('/bot', function() use($app) {
  $data = json_decode(file_get_contents('php://input'));

  if(!$data)
    return "nioh";
  
  if( $data->secret !== getenv('VK_SECRET_TOKEN') && $data->type !== 'confirmation')
    return 'nioh';

  switch($data->type){
    case 'confirmation':
      return getenv('VK_CONFIRMATION_CODE');
      break;
    
    case 'message_new':
      if ( $data->object->text == '!Ку' || $data->object->text == '!ку' || $data->object->text == '!привет' || $data->object->text == '!Привет'){
        $request_params = array(
          'user_id' => "{$data->object->from_id}",
          'message'=>'привет',
          'access_token' => '18d28ce6782d1c964c4bac21f4fd054378c65e739089d1bcae856947b32657436f5c2d06faa5179289e08',
          'v' => '5.80'
        );
      } else {
        $request_params = array(
          'user_id' => "{$data->object->from_id}",
          'message'=>'Добро пожаловать! <br> Вот мои команды: <br> ;-P !привет - бот скажет тебе привет😜',
          'access_token' => '18d28ce6782d1c964c4bac21f4fd054378c65e739089d1bcae856947b32657436f5c2d06faa5179289e08',
          'v' => '5.80'
        );
      }

      file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
      return 'ok';

      break;
  }
  return "nioh";
});

$app->run();
