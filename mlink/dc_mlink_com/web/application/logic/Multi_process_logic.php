<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use GuzzleHttp\Client as Client;

Class Multi_process_logic
{
    private $collection = array();
//    private $url = array();

    public function multi_process($process)
    {
        if (!is_array($process))
        {
            return false;
        }
        $client = new Client([
            // Base URI is used with relative requests
            'base_url' => 'http://dc.brandreward.com',
            'defaults' => [
                'timeout' => 60,
                'auth' => ['seandiao','Mega@12345']
            ],
        ]);
        /**  GuzzleHttp 6.X (> PHP 5.5)
        $requests = function ($process) use($client){
            foreach($process as $thread)
            {
                yield function() use ($client, $thread) {
                    return $client->postAsync($thread['url'],[
                        'form_params' => $thread['params']
                    ]);
                };
            }
        };

        $pool = new \GuzzleHttp\Pool($client, $requests($process), [
            'concurrency' => 4,
            'fulfilled' => function ($response, $index) {
                // this is delivered each successful response
                $content = $response->getBody()->getContents();
                $content_array = json_decode($content,true);
                !empty($content_array) && array_set($this->collection,$index,$content_array);
            },
            'rejected' => function ($reason, $index) {
                // this is delivered each failed request
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
        */
        //GuzzleHttp 5.3.X
        $requests = array();
        foreach($process as $thread)
        {
            array_push($requests, $client->createRequest('POST',$thread['url'],['body' => $thread['params']]));
        }

        $pool_option = [
            'pool_size' => 8,
            'before' => function (\GuzzleHttp\Event\BeforeEvent $event)
            {
                /**
                $content = $event->getRequest()->getBody()->getContents();
                $content = substr($content, 12,41);
                if (!isset($this->url[$content])) {
                    $this->url[$content] = array();
                }
                $this->url[$content]['SD'] = date('Y-m-d H:i:s');
                */
            },
            'complete' => function (GuzzleHttp\Event\CompleteEvent $event)
            {
                $content = $event->getResponse()->getBody()->getContents();

                $content_array = json_decode($content,true);
                !empty($content_array) && array_push($this->collection,$content_array);
                /**
                $this->url[$content_array[2]]['ED'] = date('Y-m-d H:i:s');
                $this->url[$content_array[2]]['SQL'] = $content_array[1];
                */
            }
        ];
        $pool = new \GuzzleHttp\Pool($client, $requests, $pool_option);
        $pool->wait();

        return $this->collection;
    }



    /**
     * @param int $start_date
     * @param int $end_date
     * @param int $date_jump 默认跳跃7，跳整月份
     */
    public function slice_date($start_date = 0, $end_date = 0, $date_jump = 0)
    {
        $start_date_timestamp = strtotime($start_date);
        $end_date_timestamp = strtotime($end_date);
        if (empty($start_date_timestamp) || empty($end_date_timestamp) || $start_date_timestamp > $end_date_timestamp) {
            return false;
        }
        //递归方法
        $jumps = $this->jump_control($start_date, $end_date, $date_jump);

        return $jumps;
    }

    /**
     * 分解日期
     * @param $start_date 开始日期
     * @param $end_date   结束日期
     * @param $date_jump  跳跃日期
     * @description
     * @return array|bool
     */
    public function jump_control($start_date, $end_date, $date_jump)
    {
        //3种类型：1.月内(头)，2.跨月不跳月(头尾)，3.跨月跳月(中尾、头中尾)
        //跳跃方式：1.月跳 2.N天跳
        if (strtotime($start_date) > strtotime($end_date)) {
            return false;
        }
        $jumps = array();
        //比较日期
        //A.常规值比较
        $scf = date_create($start_date);
        $ecf = date_create($end_date);
        $diff_o = date_diff($scf, $ecf);
        $month_o = $diff_o->format("%m");
        //B.临界值比较：加1(整月)
        date_add($ecf, date_interval_create_from_date_string('1 day'));
        $diff_1 = date_diff($scf, $ecf);
        $month_1 = $diff_1->format("%m");
        if (date_format($scf, "d") > 1 || (empty($month_o) && empty($month_1)))
        {
            $date_jump_t1 = $this->jump_date($start_date, $end_date, $date_jump . ' days');
            array_push($jumps, ['D', $start_date, $date_jump_t1]);
        } else {
            $date_jump_t1 = $this->jump_date($start_date, $end_date, '1 month');
            $md = date_create($start_date);
            array_push($jumps, ['M',date_format($md,'Y-m')]);
        }
        //新开始日期
        $date_jump_td = date_create($date_jump_t1);
        date_add($date_jump_td, date_interval_create_from_date_string('1 day'));
        $date_jump_ts = date_format($date_jump_td, 'Y-m-d');
        //第2次结果日期
        $date_jump_t2 = $this->jump_control($date_jump_ts, $end_date, $date_jump);
        !empty($date_jump_t2) && $jumps = array_merge($jumps, $date_jump_t2);

        return $jumps;
    }

    /**
     * 跳月份
     */
    public function jump_date($start_date, $end_date, $date_jump = '1 day')
    {
        //跳跃方式：月跳
        $start_date_d = date_create($start_date);
        $end_date_d = date_create($end_date);
        date_add($start_date_d, date_interval_create_from_date_string($date_jump));
        date_sub($start_date_d, date_interval_create_from_date_string('1 day'));
        //跳后日期与结束日期比较
        $end_diff = date_diff($start_date_d, $end_date_d);
        $end_diff_days = $end_diff->format("%R%a");
        if ($end_diff_days < 0) {
            return date_format($end_date_d, 'Y-m-d');
        }
        //跳后日期与月末日期比较
        $date_me_s = $this->date_month_end($start_date);
        $date_me = date_create($date_me_s);
        $me_diff = date_diff($start_date_d, $date_me);
        $me_diff_days = $me_diff->format("%R%a");

        if ($me_diff_days < 0) {
            return date_format($date_me, 'Y-m-d');
        }

        return date_format($start_date_d, 'Y-m-d');
    }

    /**
     * 获取给定日期的月末日期
     */
    private function date_month_end($date)
    {
        $date_d = date_create($date);
        $date_m_s = date_format($date_d, 'Y-m') . '-01';
        $date_m = date_create($date_m_s);
        date_add($date_m, date_interval_create_from_date_string('1 month'));
        date_sub($date_m, date_interval_create_from_date_string('1 day'));

        return date_format($date_m, 'Y-m-d');
    }
}