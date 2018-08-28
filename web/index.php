<?php
use FormulaParser\FormulaParser;
require('../vendor/autoload.php');
$con = pg_connect(getenv("DATABASE_URL"));
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
      $request_params = array(
        'peer_id' => "{$data->object->peer_id}",
        'access_token' => '18d28ce6782d1c964c4bac21f4fd054378c65e739089d1bcae856947b32657436f5c2d06faa5179289e08',
        'v' => '5.80'
      );
      
      $split = explode(" ", $data->object->text, 2);

      if ( $split[0] == 'Ку' || $split[0] == 'ку' || $split[0] == '!привет' || $split[0] == '!Привет'){
        $request_params["message"] = '🎉Приветик🎉';
      } 

      elseif ( $split[0] == '!дз' ){
        $request_params['message'] = $data->object->fwd_messages['id'];
      }
      
      elseif ( $split[0] == "!скажи" ){
          $request_params['message'] = $split[1];
      } 
      
      elseif ( $split[0] == '!реши' ){
            $formula = $split[1];
            $precision = 2; // Number of digits after the decimal point

            $parser = new FormulaParser($formula, $precision);
            $result = $parser->getResult(); // [0 => 'done', 1 => 16.38]
            $request_params['message'] = "🍀Ответ: " . number_format($result[1], $precision, '.', ',');
      } 
      
      elseif ( $split[0] == '!анекдот' ){
          $anekdots = array(
            'Колобок повесился',
            'php норм язык',
            'Вася пошёл кодить на флеш',
            'Надел мужик шляпу, а она ему как раз😳😳😳',
            'Где коммит лебовски?'
            );
          $rand = $anekdots[rand(0, count($anekdots)-1)];
          $request_params['message'] = $rand;
          $request_params['attachment'] = 'photo-170236279_456239020';
      }
      
      elseif ( $split[0] == '!бд'){
        $query = pg_query($con, "INSERT INTO messages (user_id, message) VALUES (22112, 'бот лох');");
        $request_params['message'] = 'gg';
      }
      elseif ( $split[0] == '!помощь' ) {
        $request_params['message'] = 'Добро пожаловать!<br> Вот мои команды:<br>;-P !привет - бот скажет тебе привет😜<br>👏!скажи <фраза/текст> - бот повторит твою фразу👏<br>😎!реши <пример> - бот решит пример за тебя😎<br>😃!анекдот - бот расскажет смешной анекдот😃';
      }

      file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
      return 'ok';

      break;
  }
  return "nioh";
});

$app->run();