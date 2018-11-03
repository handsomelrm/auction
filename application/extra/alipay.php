<?php
/**
 * 支付宝支付
 */

return [
        //应用ID,您的APPID。
        'app_id' => "2016092200570309",

        //商户私钥, 请把生成的私钥文件中字符串拷贝在此
        'merchant_private_key' => "MIIEpAIBAAKCAQEAuczd7pNmwfAOIUld7pAk2FOY3LLheUVM4CQ4YmB2FeCtlSoCNxrrmhMaj4oaBu6Rw5OvPbCIQxpXEvHWr+JTTo2A9Ai62cJtFOJNE42+YKMUE+9UIQG0qIJ52gLXnBtLBHImxVu0Zcvkhisa9meZAJ+VEEupaehfoPQevqT+U2bzEj3iCL7RfMN05MONoAv+3zkcZSYbiR21WUPjh3DgTO7ewAvDN8nvD37vCu4MkRrbULCZbOYDkHKObyAWns3DlszNZOMP58mqUjIOwpn0P8hnLV8cc85ydmp/kIN5GcMHGJOJaF+scCzgyK8+jVETsUVhprKcpkqclIhlTdCvfQIDAQABAoIBAGWi5bw+MbXhJTmcMp/vhdg4UqRXzfNSr2zlI1rdPXtWPl4cbr3KIhtUW/EtMHOeSWpc/T2XtdJPNFaOqEvqWHvd+C6GIABC1PzZliQeI4glbTRCdRjhH/wV86YIa/1fCLhT4VWQhqwwzr9+EO+9V/r5UMdrPC9NCBK5t5++Dcl81SCj3GTnRJ9b4RbE/m3+m8FNSEBvxox8m10gddqfCjjecy5uIaE6fu3GR0JB+u9FQpXczRFdmX8ELf7qdNnAAGGnTc9oV4O8YviGFfjwIzw5BbGiCjpEjGeeDimHUqyqa9DahDwKnngiQ1GH5uCTRLAhh/B3OK2dQyusva1QwYECgYEA9frevAVgMVVYSfNMX8hKDYFA+GsRmubcdjCOIH5EOSD/wio/eoTKyFg94V8Fjq7nQX6DlhRi8Tdt3nmPAErfeNbdl02o/Y2cQTxCgj2tJyT5blM8JSdbk4TvnKCdMC5ixJgTpoe/nMuqcWzwcKlCUrwjIxMaZH0+y+Q3R+Uat20CgYEAwV5uNVT9/AnEI8Tn7L1X380aMP1SZHfmuu0oDTFxSPSosCbnv1BEJSg+FvcQZpEfb6OX3MFdGsxRKKV1EXEo0Uzlt7/dSn5A9QaeQPYp/0Yb3ZG+kQK8M+dzcoABgR49Xba9O5dwhyHpgpjrZzdRruU8lFaeHAxE1ibmEExJflECgYEA64MTFzqeax2ntsY37c06+cszhaYd6Q35L0A2FG5Qq9IojqMcvBXAginatZnhHEfiKxcSrfM++G11yHjirgkCBhTTfTZiAsI/RUaI92JOExiXqUYQZBJW39t7/57YYXVII6rnxZ5bm6h0vLztSTEFy2lOyxpTPrucEGOlejSla9ECgYBlMb7pKB523MGJmo1pBjnLhHGVe6y3qUvVPTE9nkdTz73L2cBkDWRsMaA0sn0hwAa8jNd3jrXjxVMfu18Fjru2tNBvFfh/+IyT7i+5fVnDHvSMQq6BAvZtD04KeNTQtnuU5IIpgnpALc4fK1nePUQBDDRkyOObeAw6Kkcx9kpr0QKBgQCciJ4usZ2DwBqvUfM85/zjOv1fFFa3bztmw72pe6hTZlmzOrbcPpe4U7X9jFqmEBRj40FsMxD9SASQziD4yp0T4sHbvVx7XBwCQG6hQvSdkIlHQfuJYO+zcHSUWbg43Hp7SjTyzQq4gbd17j7N+Kbvf1y/kakCpfmYhmTeRslP1A==",
        //异步通知地址
        'notify_url' => "http://".$_SERVER['HTTP_HOST']."/auction/public/index.php/index/order/notify_url",

        //同步跳转
        'return_url' => "http://".$_SERVER['HTTP_HOST']."/auction/public/index.php/index/order/return_url",

        //编码格式
        'charset' => "UTF-8",

        //签名方式
        'sign_type'=>"RSA2",

        //支付宝网关
        'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAq8Tzf+jbvkTosNYfwvJ7ur0c8vNqKLMycasfkGRjAvAOZPCYS3LTKoEV4eUnZDz8cN0PbhCmSt3JpBAGn65kjJt7XBCcsrt5qafwWKSGWI1HdEY6huB7fn++r1cVVS58U0vlCUHEQKVOh3q5ZIoFC11cnF73jsf2Iky6QH3cVlvWT+MgebWCQf5uodZT1HChu0j4FEJLS6pCvEVzr4agfR4+aeZtmpKymKuh/Z68yO96WqBo+ThNpyYRxnxRUA36JC7ArGCL4pE1YafyRox6G81G8/Ro0+nVe0X2n2bh7bkVoCyHtlCZ1v862LwFOkdXq0bLmdTimuPHJGxuFdz2nQIDAQAB",
];
