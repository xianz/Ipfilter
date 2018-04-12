<?php

namespace App\Contracts;


class IpFilter
{

    // ログ取り込みするipアドレスの定義、blacklistがなければ空配列で可
    private $filters = [
        'accept_network_list' => [
            [
                'accept' => '172.16.0.0/16',
                'black_list' => [
                    '172.16.0.0/24',
                    '172.16.100.0/24',
                ],
            ],
//        [ // 追加する場合の例
//            'accept' => '10.10.10.0/24',
//            'black_list' => [
//                '10.10.10.5/32',
//            ],
//        ],
        ],
    ];

    /**
     * IpFilter constructor.
     * @param array $accept_network_list
     *
     * 許容するネットワーク部と、例外のネットワーク部をプロパティにセット
     */
    function __construct(array $accept_network_list)
    {
        // 受け取った許容するネットワークと例外から、サブネットマスク分ビットシフトしたものを作成
        foreach ($accept_network_list as $accept_network) {
            $filter = [
                'accept' => '',
                'black_list' => [],
            ];
            // 許容する元のIPアドレスをビットシフトしたもの
            $filter['accept'] = $this->getBitShiftIpByMask($accept_network['accept']);
            foreach ($accept_network['black_list'] as $not_accept) {
                // 許容しないIPアドレスをビットシフトしたもの
                $filter['black_list'][] = $this->getBitShiftIpByMask($not_accept);
            }

            $this->filters[] = $filter;
        }
    }

    /**
     * @param string $ip_mask
     * @return array
     *
     * ipとネットワークマスクから、filter格納用の配列にする
     */
    private function getBitShiftIpByMask(string $ip_mask)
    {

        $result = [];

        // IPとサブネットマスクを分ける
        list($ip, $mask) = explode('/', $ip_mask);
        // ビットシフトして、ネットワーク部のみを格納
        $result['network'] = ip2long($ip) >> (32 - $mask);
        // 比較するipアドレスも同じサブネットマスクでビットシフトするので格納
        $result['mask'] = $mask;

        return $result;
    }

    /**
     * @param string $check_ip
     * @return bool
     *
     * 受け取ったipアドレスが、$ip_check_listのいずれかの条件を満たすか判定する
     */
    public function isAcceptIp(string $check_ip)
    {

        // IPの文字列をlongに変換
        $check_ip_ip2long = ip2long($check_ip);

        foreach ($this->filters as $filter) {
            // チェックするIPを比較対象と同じサブネットマスクでビットシフト
            $check_network = $check_ip_ip2long >> (32 - $filter['accept']['mask']);

            // 同じネットワークなら、処理を続行
            if ($check_network == $filter['accept']['network']) {
                $check_result = true;

                // ブラックリストに含まれるネットワークでないかチェック
                foreach ($filter['black_list'] as $not_accept) {
                    // チェックするIPを比較対象と同じサブネットマスクでビットシフト
                    $check_network = $check_ip_ip2long >> (32 - $not_accept['mask']);
                    // ブラックリストのネットワークと一致したら、判定はfalseで、ループを抜ける
                    if ($check_network == $not_accept['network']) {
                        $check_result = false;
                        break;
                    }
                }

                // ブラックリストのネットワークと比較し終わってもtrueだったら、受け取ったIPは許容するのでtrueを返す
                if ($check_result) {
                    return true;
                }
            }
        }

        // $filtersの判定を通して、最終的に受け取ったIPが許容されなかった場合は、falseを返す
        return false;
    }
}