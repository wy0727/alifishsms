<?php

namespace AliSms;

use AliSms\Ali\TopClient;
use AliSms\Ali\AlibabaAliqinFcSmsNumSendRequest;

class Sms
{
    //定义appkey
    protected $aliSmsAppKey;
    //定义secret
    protected $aliSmsSecretKey;
    //定义产品
    protected $product;

    //初始化参数
    public function __construct($appKey,$secretKey,$product)
    {
        $this->aliSmsAppKey=$appKey;
        $this->aliSmsSecretKey=$secretKey;
        $this->product=$product;
    }


    /* *
     * 发送数字短信
     *
     * @param  string $to        收信人
     * @param  string $code      验证数字
     * @param  string $template  模板编号
     * @param  string $signName  功能签名
     * @return bool
     * */
    public function send($to, $code, $template, $signName)
    {
        /* *
         * SDK工作目录
         * 存放日志，TOP缓存数据
         * */
        if (!defined("TOP_SDK_WORK_DIR"))
        {
            define("TOP_SDK_WORK_DIR", "/tmp/");
        }
        /* *
         * 是否处于开发模式
         * 在你自己电脑上开发程序的时候千万不要设为false，以免缓存造成你的代码修改了不生效
         * 部署到生产环境正式运营后，如果性能压力大，可以把此常量设定为false，能提高运行速度（对应的代价就是你下次升级程序时要清一下缓存）
         * */
        if (!defined("TOP_SDK_DEV_MODE"))
        {
            define("TOP_SDK_DEV_MODE", true);
        }

        date_default_timezone_set('Asia/Shanghai');

        // 数据准备
        $appKey = $this->aliSmsAppKey;
        $secretKey = $this->aliSmsSecretKey;
        $product = $this->product;
        $data = '{"code":"' . $code . '","product":"' . $product . '"}'; //Json格式

        /**
         * 创建客户端实例
         **/
        $c = new TopClient;
        $c->appkey = $appKey;
        $c->secretKey = $secretKey;

        // 设置返回格式
        $c->format = 'json';

        /**
         * 创建请求实例
         **/
        $req = new AlibabaAliqinFcSmsNumSendRequest;

        // <必> 类型 短信写normal
        $req->setSmsType("normal");

        // <必> 短信收件人 支持单个或多个手机号码，传入号码为11位手机号码，不能加0或+86。群发短信需传入多个号码，以英文逗号分隔，一次调用最多传入200个号码。示例：18600000000,13911111111,13322222222
        $req->setRecNum($to);

        // <必> 短信模板变量和值(Json类型) 传参规则{"key":"value"}，key的名字须和申请模板中的变量名一致，多个变量之间以逗号隔开。示例：模板“验证码${code}，您正在进行${product}身份验证，打死不要告诉别人哦！”，传参时需传入{"code":"1234","product":"AlpFish"}
        $req->setSmsParam($data);     //Json原样: ('{"code":"18888","product":"乐活网"}');

        // <必> 模板代码
        $req->setSmsTemplateCode($template);

        // <必> 短信签名 传入的短信签名必须是在阿里大鱼“管理中心-短信签名管理”中的可用签名。如“注册验证”已在短信签名管理中通过审核，则可传入”注册验证“（传参时去掉引号）作为短信签名。短信效果示例：【注册验证】欢迎使用{$product}服务。
        $req->setSmsFreeSignName($signName);

        // [选] 回传参数 在“消息返回”中会传回该参数（实际中不会传回），；举例：可传入会员ID，在消息返回时，该会员ID会包含在内，根据该会员ID识别是哪位会员使用了应用
        $req->setExtend('');

        /**
         * 发送请求并传回响应(对象)
         **/
        $resp = $c->execute($req);


        /**
         * 处理发送结果
         **/
        if ( ! empty($resp))
        {
            // 失败
            if ( ! empty($resp->code)) {
                //self::handleErrorsResponse($resp);
                return false;
            }

            // 成功
            if ( ! empty($resp->result->success) || empty($resp->result->err_code) || empty($resp->code))
            {
                return true;
            }
        }

        return false;
    }

    /* *
     * 短信发送错误处理
     * */
    private static function handleErrorsResponse($resp)
    {
        $err_title = '【紧急】网站SMS模块错误!';
        $err_content = '<h1>短信发送失败！</h1>';
        $err_content .= '<h5>代码：' . $resp->code . '</h5>';
        $err_content .= '<h5>提示：' . $resp->msg . '</h5>';
        $err_content .= empty($resp->sub_code) ? '' : '<h5>子码：' . $resp->sub_code . '</h5>';
        $err_content .= empty($resp->sub_msg) ? '' : '<h5>提示：' . $resp->sub_msg . '</h5>';

        // 通知站长
//        if (config(conFile('sms') . 'sms.isSendErrEmail'))
//            Email::sendEmailForWebmaster($err_title, $err_content);
//
//        return false;
    }
}
