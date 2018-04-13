# 概要
IPアドレスが定義したIPアドレスの範囲内にあるかを判定して、範囲内ならtrue、範囲外ならfalseを返す。
ブラックリストを登録しておけば、許容するIPアドレス範囲内の例外を管理できる。

# 使い方
```
// 判定範囲の定義
// IPアドレス/サブネットマスク の形式で定義する
// black_listに定義するIPアドレスの範囲はacceptに定義した範囲内でないと意味がありません。
$filters = [
  'accept_network_list' => [
    [
      'accept' => '172.16.0.0/16',
      'black_list' => [
        '172.16.0.0/24',
        '172.16.100.0/24',
      ],
    ],
    [
      'accept' => '10.10.10.0/24',
      'black_list' => [
        '10.10.10.5/32',
      ],
    ],
    [
      'accept' => '20.20.20.0/24',
      'black_list' => [
        // 例外がない場合は空配列
      ],
    ],
  ],
];
// インスタンス生成
$ip_filter = new IpFilter()
// IPアドレスを渡して判定
$result = $ip_filter->isAcceptIp('172.16.20.2'); // 範囲内なのでtrue
$result = $ip_filter->isAcceptIp('172.17.20.2'); // 範囲外なのでfalse
$result = $ip_filter->isAcceptIp('172.16.0.10'); // 例外なのでfalse
$result = $ip_filter->isAcceptIp('172.16.100.33'); // 例外なのでfalse
$result = $ip_filter->isAcceptIp('10.10.10.2'); // 範囲内なのでtrue
$result = $ip_filter->isAcceptIp('10.10.8.9'); // 範囲外なのでfalse
$result = $ip_filter->isAcceptIp('10.10.10.5'); // 例外なのでfalse
$result = $ip_filter->isAcceptIp('20.20.20.5'); // 範囲内なのでtrue
```

# IPアドレス範囲の定義詳細
- acceptに指定するサブネットマスクが8の場合  
`'accept' => '192.0.0.0/8'`  
許容するIPアドレスの範囲は、  
`192.0.0.0 ~ 192.255.255.255`  
- acceptに指定するサブネットマスクが16の場合  
`'accept' => '192.168.0.0/16'`  
許容するIPアドレスの範囲は、  
`192.168.0.0 ~ 192.168.255.255`  
- acceptに指定するサブネットマスクが24の場合  
`'accept' => '192.168.10.0/24'`  
許容するIPアドレスの範囲は、  
`192.168.10.0 ~ 192.168.10.255`  
- acceptに指定するサブネットマスクが32の場合  
`'accept' => '192.168.10.1/32'`  
許容するIPアドレスの範囲は、  
`192.168.10.1 ~ 192.168.10.1`（一つのみとなる）  

※ black_listの場合は許容するのではなく、拒否するようになるだけ

# 参考
- [IPアドレスが指定した範囲内にあるかどうか判別する-Qiita](https://qiita.com/ran/items/039706c93a8ff85a011a)

