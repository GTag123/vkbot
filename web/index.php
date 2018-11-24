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
return "тагир дебил";
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
      
 /*   case "wall_post_new":
      $request_params = array(
        'domain' => "gitpush",
        'access_token' => '18d28ce6782d1c964c4bac21f4fd054378c65e739089d1bcae856947b32657436f5c2d06faa5179289e08',
        'v' => '5.80',
        'message' => 'Новая запись'
      );
      file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
      return 'ok';

      break; */
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

      /* elseif ( $split[0] == '!дз' ){
        $request_params['message'] = $data->object->fwd_messages['id'];
      } */
      
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

      elseif ( $split[0] == "!перевод" ){
        if ( $split[1] == "" ){
          $request_params['message'] = "Чтобы перевести текст с какого-то языка пишите:<br>!перевод <id языков> <текст для перевода><br><br>ID языков:<br>1 - Русский - Англиский<br>2 - Английский - Русский<br>3 - Французский - Русский<br>4 - Русский - Французский<br>5 - Англ. - Фр.<br>6 - Фр. - Англ.";
        }
        else {
          $lang = array(
          'ru-en', // 1
          'en-ru', // 2
          'fr-ru', // 3
          'ru-fr', // 4
          'en-fr', // 5
          'fr-en', // 6
        );

        $split2 = explode(" ", $split[1], 2);
        $request_params['message'] = json_decode(file_get_contents('https://translate.yandex.net/api/v1.5/tr.json/translate?' . http_build_query(array(
          'key' => "trnsl.1.1.20181124T122415Z.1606631df5f130cf.d1a65715f0dd3d20cedfa225a1442f4d7ecc2082",
          'text' => $split2[1],
          'lang' => $lang[(int)$split2[0]-1]
        ))), true)['lang'];}
      }

      elseif ( $split[0] == '!анекдот' ){
        $request_params['message'] = json_decode(preg_replace("/[\r\n]+/", " ", file_get_contents('http://rzhunemogu.ru/RandJSON.aspx?CType=1')), true)['content'];
      }
      /* elseif ( $split[0] == '!бд'){
        $query = pg_query($con, "INSERT INTO messages (user_id, message) VALUES (22112, 'прив');");
        $request_params['message'] = 'gg';
      } */
      else {
        $request_params['message'] = 'Добро пожаловать!<br> Вот мои команды:<br>;-P !привет - бот скажет тебе привет😜<br>👏!скажи <фраза/текст> - бот повторит твою фразу👏<br>😎!реши <пример> - бот решит пример за тебя😎<br>😃!анекдот - бот расскажет смешной анекдот😃<br>&#9997;!перевод - перевод текста(подробнее в команде)&#9997;';
      }

      file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
      return 'ok';

      break;
  }
  return "nioh";
});

$app->run();