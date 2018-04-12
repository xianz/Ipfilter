<?php

namespace Tests\Unit;

use App\Contracts\IpFilter;
use Tests\TestCase;

class ipFilterTest extends TestCase
{

    private $ip_filter_list_1group = [
        [
            'accept' => '172.16.0.0/16',
            'black_list' => [
                '172.16.0.0/24',
                '172.16.100.0/24',
            ],
        ],
    ];

    private $ip_filter_list_2group = [
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
    ];

    private $ip_filter_list_empty_blacklist = [
        [
            'accept' => '172.16.0.0/16',
            'black_list' => [
                //
            ],
        ],
    ];

    private $ip_filter_list_network_octet1 = [
        [
            'accept' => '172.0.0.0/8',
            'black_list' => [
                '172.16.0.0/16'
            ],
        ],
    ];
    private $ip_filter_list_network_octet2 = [
        [
            'accept' => '172.16.0.0/16',
            'black_list' => [
                '172.16.10.0/24'
            ],
        ],
    ];
    private $ip_filter_list_network_octet3 = [
        [
            'accept' => '172.16.10.0/24',
            'black_list' => [
                '172.16.10.10/32'
            ],
        ],
    ];
    private $ip_filter_list_network_octet4 = [
        [
            'accept' => '172.16.10.3/32',
            'black_list' => [
                //
            ],
        ],
    ];

    /**
     * @test
     *
     * @group IpFilter
     *
     * @return void
     */
    public function 比較するネットワークが一つの場合()
    {

        $ip_filter = new IpFilter($this->ip_filter_list_1group);

        // 許可するIPテスト
        // 第1octetが違う NG
        $this->assertFalse($ip_filter->isAcceptIp("100.16.0.0"));
        // 第2octetが違う NG
        $this->assertFalse($ip_filter->isAcceptIp("172.1.0.0"));
        // 第3octetは任意 OK
        $this->assertTrue($ip_filter->isAcceptIp("172.16.60.0"));
        // 第4octetは任意 OK
        $this->assertTrue($ip_filter->isAcceptIp("172.16.60.111"));

        // ブラックリストに登録されているネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.16.0.0"));
        $this->assertFalse($ip_filter->isAcceptIp("172.16.0.99"));
        $this->assertFalse($ip_filter->isAcceptIp("172.16.100.0"));
        $this->assertFalse($ip_filter->isAcceptIp("172.16.100.104"));
    }

    /**
     * @test
     *
     * @group IpFilter
     *
     * @return void
     */
    public function 比較するネットワークが二つの場合()
    {
        $ip_filter = new IpFilter($this->ip_filter_list_2group);

        // 片方で許されているネットワーク
        $this->assertTrue($ip_filter->isAcceptIp("172.16.34.111"));
        $this->assertTrue($ip_filter->isAcceptIp("10.10.10.9"));

        // どちらにも登録してないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.1.17.1"));
        $this->assertFalse($ip_filter->isAcceptIp("10.10.9.9"));
        $this->assertFalse($ip_filter->isAcceptIp("10.9.10.1"));
        $this->assertFalse($ip_filter->isAcceptIp("100.10.10.1"));

        // 片方のブラックリストに登録してあるネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.16.0.1"));
        $this->assertFalse($ip_filter->isAcceptIp("172.16.100.1"));
        $this->assertFalse($ip_filter->isAcceptIp("10.10.10.5"));
    }

    /**
     * @test
     *
     * @group IpFilter
     *
     * @return void
     */
    public function ブラックリストが空の場合()
    {
        $ip_filter = new IpFilter($this->ip_filter_list_empty_blacklist);

        // 許されているネットワーク
        $this->assertTrue($ip_filter->isAcceptIp("172.16.34.111"));
        // 許されていないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.1.34.111"));
    }

    /**
     * @test
     *
     * @group IpFilter
     *
     * @return void
     */
    public function 第1octetのフィルタ()
    {
        $ip_filter = new IpFilter($this->ip_filter_list_network_octet1);

        // 許されているネットワーク
        $this->assertTrue($ip_filter->isAcceptIp("172.1.34.111"));
        // 許されていないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("100.16.34.111"));
        // ブラックリストで許されていないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.16.34.111"));
    }

    /**
     * @test
     *
     * @group IpFilter
     *
     * @return void
     */
    public function 第2octetのフィルタ()
    {
        $ip_filter = new IpFilter($this->ip_filter_list_network_octet2);

        // 許されているネットワーク
        $this->assertTrue($ip_filter->isAcceptIp("172.16.34.111"));
        // 許されていないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.1.34.111"));
        // ブラックリストで許されていないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.16.10.111"));
    }

    /**
     * @test
     *
     * @group IpFilter
     *
     * @return void
     */
    public function 第3octetのフィルタ()
    {
        $ip_filter = new IpFilter($this->ip_filter_list_network_octet3);

        // 許されているネットワーク
        $this->assertTrue($ip_filter->isAcceptIp("172.16.10.111"));
        // 許されていないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.16.34.111"));
        // ブラックリストで許されていないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.16.10.10"));
    }

    /**
     * @test
     *
     * @group IpFilter
     *
     * @return void
     */
    public function 第4octetのフィルタ()
    {
        $ip_filter = new IpFilter($this->ip_filter_list_network_octet4);

        // 許されているネットワーク
        $this->assertTrue($ip_filter->isAcceptIp("172.16.10.3"));
        // 許されていないネットワーク
        $this->assertFalse($ip_filter->isAcceptIp("172.16.10.2"));
        $this->assertFalse($ip_filter->isAcceptIp("172.16.10.4"));
    }
}
