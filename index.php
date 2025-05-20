<?php

$file = 'test.log';

$error_msg = '';
$res = [];
$subdomain = 'permskyvovan';
$access_token = ''; // здесь запрос в хранилище бд или файл на получение токена и его экспирации для тестового задания пропускаю даный функционал

if (empty($access_token)){
    $link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса

    /** Соберем данные для запроса */
    $data = [
        'client_id' => 'b2a3f456-9e38-4503-b375-ad8f3a8ffb91',
        'client_secret' => 'bNSBxiawHR8EZcWYxOLbRfGl75wGB1Xd4nM24H6Z1w3FhsEymAtrwr6E7QMDJEcO',
        'grant_type' => 'authorization_code',
        'code' => 'def502004579ef695089a2b8659d576f826c21cdd39d1b78f5bae4b7e692bebcb755ef840ef398e6410379a1841f712986bdf4f5e50402226b72365c16722650e565e4413f0841dfaac31013f99e1163e95a535ee4fb6f0270d82e6a92387afc6b02cd5f820837878681c555e14672b7390707a6a1bbfbd0cd1e1c1f615037427966a2ac7754352df651412010c1a9e8666d206f71a7ecda3ba7badc2315e5935c0a299a2844f9e4e9cc0de11bf9c861c94727bced984a489b2e0f3b8fd47180d9731a4083530c0d6a3bc2d6b9036b1879c2952a177d3e10d5996bd214081d93f2157085afc4c8518496d91cd6abf5a1ab6db694d7539818cf974553afcb9ef064546eaf5f5a33f9a68f040c634cd6cce7bf524dc8d637ccc676423e2223bea22478f37b8503355c4e6ecc73715e055de754f772a022f7d362858504283cd7e8d0945d980c423880855837db298d12dbdbf23cb01d618dc93ee6975944050cbb20e62f2cc72c47532c5485e9ae7d728eb2fabd727f592d945439f2bbbcd7192f443879082b5279dc1a27dec0b3d79a7eb38c9f2ee3bb7adc384fc4bdc478688b8b8583cadcb055352a307329c04090bca5e4c9f81ccde8d4005d9bf27be4c3591edf25575780b04b848c502d5c5b4afc663cb89fa1a88252ab403bc1ee9185fd1b41c621ea736cb4f48c6c',
        'redirect_uri' => 'http://n91946p2.beget.tech',
    ];
    print_r(httpRequest('POST', '', $data , [] , $link));
}

function httpRequest($method, $params = '', $data = [], $headers = [],$link='' )
    {
        global $error_msg;

        $headers[] = 'Content-Type: application/json';
        if (!empty($access_token))$headers[] = "Authorization: Bearer ". $access_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link . $params );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (empty($access_token)) curl_setopt($ch,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');

        if($method == 'POST' && !empty($data))
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg .= curl_error($ch);
            throw new \Exception($error_msg);
            return false;
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];

        try
        {
            /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        }
        catch(\Exception $e)
        {
            die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }

        return $output ? json_decode($output, true) : null;
    }

function dataHelper($type, $event)
    {
        global $res;
        $link = "https://gmtrus.amocrm.ru/api/v4";
        $responsible_user = httpRequest('GET', '/users/' . $_POST[$type][$event][0]['responsible_user_id'],[],[],$link);
        $str = "name - " . $_POST[$type][$event][0]['name'] ;
        $str .= "\n responsible_user - " . $responsible_user['name'];
        if ($event == 'add')  $str .= "\n created_at - " . $_POST[$type][$event][0]['created_at'];
        if ($event == 'update')
        {
            $str .= "\n updated_at - " . $_POST[$type][$event][0]['updated_at'];
            if ($type == 'leads') $str .= "\n price - " . $_POST[$type][$event][0]['price'];
        }
        $data = [[
            'entity_id' => intval($_POST[$type][$event][0]['id']),
            'note_type' => "common",
            "params"=> ["text" => $str]
        ]];
        $res = httpRequest('POST', '/'.$type.'/notes', $data , [] , $link);
        return true;
    }

if (isset($_POST['leads'])){
    if (isset($_POST['leads']['add']))dataHelper('leads','add');
    if (isset($_POST['leads']['update']))dataHelper('leads','update');
}

if (isset($_POST['contacts'])){
    if (isset($_POST['contacts']['add'])) dataHelper('contacts','add');
    if (isset($_POST['contacts']['update'])) dataHelper('contacts','update');
}

//if ($error_msg!='' or !empty($res)){
    if ($logFileHandle = fopen($file, 'a')) {
        fwrite($logFileHandle, print_r($error_msg , true) . PHP_EOL);
        fwrite($logFileHandle, print_r($res , true) . PHP_EOL);
        fclose($logFileHandle);
//    }
}


?>
