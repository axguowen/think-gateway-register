<?php
// +----------------------------------------------------------------------
// | ThinkPHP Gateway Register [Gateway Register Service For ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP Gateway Register 服务
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

namespace think\gateway\register;

use think\App;
use think\console\Input;
use think\console\Output;
use Workerman\Worker;
use GatewayWorker\Register as GatewayRegister;

class Register
{
    /**
     * 配置参数
     * @var array
     */
	protected $options = [
        // Register进程名称, 方便status命令中查看统计
        'name' => 'think-gateway-register',
        // Register服务监听IP, 分布式部署时请填写本机内网IP地址
        'listen' => '127.0.0.1',
        // Register服务监听端口，Register端口千万不能开放给外网，否则可能遭受攻击。
        // 客户端不要连接Register服务的端口，Register服务是GatewayWorker内部通讯用的。
        'port' => 1236,
        // Gateway通讯密钥
        'secret_key' => '',
        // 是否允许reload
        'reloadable' => false,
        // 是否以守护进程启动
        'daemonize' => false,
	];

    /**
     * App实例
     * @var App
     */
    protected $app;

    /**
     * Input实例
     * @var Input
     */
    protected $input;

    /**
     * Output实例
     * @var Output
     */
    protected $output;

    /**
     * 架构函数
     * @access public
     * @return void
     */
    public function __construct(App $app, Input $input, Output $output)
    {
        $this->app = $app;
        $this->input = $input;
        $this->output = $output;
        // 合并配置
		$this->options = array_merge($this->options, $this->app->config->get('gatewayregister'));
        // 初始化
		$this->init();
    }

    /**
     * 初始化
     * @access protected
	 * @return void
     */
	protected function init()
	{
		// 实例化register进程, 必须是text协议
        $register = new GatewayRegister('text://' . $this->options['listen'] . ':' . $this->options['port']);
        // register名称，status方便查看
        $register->name = $this->options['name'];
        // Gateway通讯密钥
        $register->secretKey = $this->options['secret_key'];
        // 是否允许reload
        $register->reloadable = $this->options['reloadable'];
        // 如果指定以守护进程方式运行
        if ($this->input->hasOption('daemon') || true === $this->options['daemonize']) {
            Worker::$daemonize = true;
        }
	}

    /**
     * 启动
     * @access public
	 * @return void
     */
	public function start()
	{
        // 启动
		Worker::runAll();
	}

    /**
     * 停止
     * @access public
     * @return void
     */
    public function stop()
    {
        Worker::stopAll();
    }
}
