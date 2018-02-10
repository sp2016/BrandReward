<?php
namespace App\Http\Entity\Statistics;
use App\Http\Entity\BasicEntity;

/**
 * Class StatisticsQueryEntity
 * @package App\Http\Entity\Statistics
 *
 * @property string $startDate
 * @property date $endDate
 * @property date $dateType
 * @property  array $publisherIds
 * @property  array $programIds
 * @property  array $domainIds
 * @property  array $affiliateIds
 *
 */
class StatisticsQueryEntity extends BasicEntity
{
    protected static $validatorRules = [
        'dateType' => 'integer|in:1,2',
        'startDate' => 'date',
        'endDate' => 'date',
        'publisherIds' => 'array',
        'domainIds' => 'array',
        'programIds' => 'array',
        'affiliateIds' => 'array'
    ];
    protected $customizeMessages = [
        'integer' => ':attribute非法数字',
        'date' => ':attribute日期格式不正确',
        'array' => ':attribute数组格式不正确'
    ];
    protected $customizeAttributes = [
        'dateType' => '日期类型',
        'startDate' => '起始日期',
        'endDate' => '结束日期',
        'publisherIds' => '发布者集合',
        'domainIds' => '域名集合',
        'programIds' => '程序集合',
        'affiliateIds' => '联盟集合'
    ];
}