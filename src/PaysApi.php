<?php

namespace DavidNineRoc\Payment;

use Closure;
use DavidNineRoc\Payment\Foundation\Config;
use DavidNineRoc\Payment\Foundation\Http;
use DavidNineRoc\Payment\Foundation\Util;

class PaysApi
{
    use Config;

    /**
     * 支付接口.
     *
     * @var string
     */
    protected $payUrl = 'https://pay.paysapi.com/';

    /**
     * 异步支付接口.
     *
     * @var string
     */
    protected $syncPayUrl = 'https://pay.paysapi.com/?format=json';

    /**
     * 支付操作。
     * 首先会先从 Config 中获取支付所需的参数
     * 然后生成一个自动提交的表单
     * 最后把这些参数嵌入表单并提交到支付接口。
     *
     * @return string
     */
    public function pay()
    {
        return Util::buildFormHtml(
            $this->payUrl,
            $this->buildPayConfig()
        );
    }

    /**
     * 异步请求支付.
     * 首先会先从 Config 中获取支付所需的参数
     * 然后直接通过 post 方式请求支付接口
     * 通过支付接口返回 json 数据
     * @return string
     */
    public function syncPay()
    {
        return Http::post(
            $this->syncPayUrl,
            $this->buildPayConfig(),
            function ($error) {
                return [
                    'code' => 400,
                    'msg' => $error,
                ];
            }
        );
    }

    /**
     * 验证付款成功回调通知
     * @param Closure|null $matching
     * @param Closure|null $mismatching
     * @return bool|mixed
     */
    public function verify(Closure $matching = null, Closure $mismatching = null)
    {
        $verified = $this->verifyNotifyValidity();
        if (is_null($mismatching) && is_null($matching)) {
            return $verified;
        }

        return $verified ? $matching() : $mismatching();
    }
}
