<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Controllers;

use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;
use Swoft\View\Bean\Annotation\View;
use Swoft\Http\Message\Cookie\Cookie;

use Carbon\Carbon;
use Swoft\Http\Message\Server\Response;
use Swoft\Http\Message\Server\Request;

use Swoft\App;
use App\Game\Core\Packet;
use App\Game\Conf\MainCmd;
use App\Game\Conf\SubCmd;
use App\Game\Core\Log;

/**
 * Class GameController
 * @Controller(prefix="/Game")
 * @package App\Controllers
 */
class GameController{
    /**
     * 用户信息
     * @var null
     */
    public $userinfo = array();

    /**
     * this is a example view, test view
     * @RequestMapping(route="/game", method={RequestMethod::GET})
     * @View(template="game/index")
     * @return array
     */
    public function index(Request $request, Response $response)
    {
        if(!$this->_isLogin($request)) {
            return $response->redirect('/login');
        }
        return [];
    }

    /**
     * this is a example view, test view
     * @RequestMapping(route="/login", method={RequestMethod::GET,RequestMethod::POST})
     * @View(template="game/login")
     * @return array
     */
    public function login(Request $request, Response $response)
    {
        $action = $request->post('action');
        $account = $request->post('account');
        $tips = '';
        if($action == 'login') {
            if(!empty($account)) {
                //注册登录
                $uinfo = array('account'=>$account);
                $response = $this->addCookie($request,  $response, 'USER_INFO', json_encode($uinfo), 60);
                return $response->redirect('/test');
            } else {
                $tips = '温馨提示：用户账号不能为空！';
            }
        }
        return ['tips'=>$tips];
    }

    /**
     * this is a example view, test view
     * @RequestMapping(route="/test", method={RequestMethod::GET,RequestMethod::POST})
     * @View(template="game/test")
     * @return array
     */
    public function test(Request $request, Response $response)
    {
        return [];
    }

    /**
     * 是否登录
     * @return bool
     */
    private function _isLogin(Request $request)
    {
        $cookie_info = $request->getCookieParams();
        if(isset($cookie_info['USER_INFO'])) {
            $this->userinfo = json_decode($cookie_info['USER_INFO']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * this is a example view, test view
     * @RequestMapping(route="/broadcast", method=RequestMethod::GET)
     * @return array
     */
    public function broadcast(Request $request)
    {
        $msg = $request->query('msg');
        //原封不动发回去
        if(!empty($msg)) {
           $msg = array('data' => $msg);
        } else {
            $msg = array('data' => 'this is a system msg');
        }
        $data = Packet::packFormat('OK', 0, $msg);
        $data = Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::BROADCAST_MSG_RESP);
        $serv = App::$server->getServer();
        $conn_client = $this->pushToAll($serv, $data);
        return $conn_client;
    }

    /**
     * set a cookie
     * @param Request $request
     * @param Response $response
     * @param string $key
     * @param string $value
     * @param int $expire(m)
     */
    protected function addCookie(Request $request, Response $response,  $key = '',  $value = '', $expire = 0)
    {
        $uri = $request->getUri();
        $path = '/';
        $domain = $uri->getHost();
        $secure = strtolower($uri->getScheme()) === 'https';
        $httpOnly = false;
        $expire = Carbon::now()->addMinutes($expire);
        $cookie = new Cookie($key, $value, $expire, $path, $domain, $secure, $httpOnly);
        $response = $response->withCookie($cookie);
        return $response;
    }

    /**
     * 当connetions属性无效时可以使用此方法，服务器广播消息， 此方法是给所有的连接客户端， 广播消息，通过方法getClientList广播
     * @param $serv
     * @param $data
     */
    protected function pushToAll($serv, $data)
    {
        $client = array();
        $start_fd = 0;
        while(true) {
            $conn_list = $serv->getClientList($start_fd, 10);
            if ($conn_list===false or count($conn_list) === 0) {
                Log::show('BroadCast finish');
                break;
            }
            $start_fd = end($conn_list);
            foreach($conn_list as $fd) {
                //获取客户端信息
                $client_info = $serv->getClientInfo($fd);
                $client[$fd] = $client_info;
                if(isset($client_info['websocket_status']) && $client_info['websocket_status'] == 3) {
                    $serv->push($fd, $data, WEBSOCKET_OPCODE_BINARY);
                }
            }
        }
        return $client;
    }
}