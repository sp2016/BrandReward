<?php
namespace App\Http\Entity\Coupon;
use App\Http\Entity\BasicEntity;

/**
 * Class CouponQueryEntity
 * @package App\Http\Coupon\Entity
 *
 * @property string $order
 * @property integer $offset 页面编号
 * @property integer $limit  页内偏移
 * @property integer $id
 * @property integer $download 是否下载
 * @property array  $affiliate 联盟列表
 *                           
 */
class CouponQueryEntity extends BasicEntity
{
    
    public function _initialize()
    {
        if (isset($this->attributes['order'][0])) {
            $orderDirection = array_get($this->attributes['order'][0], 'dir', 'DESC');
            $orderIndex = array_get($this->attributes['order'][0], 'column', 9);
            $orderColumnName = array_get($this->attributes['columns'][$orderIndex], 'data', 'EndDate');
            unset($this->attributes['order']);
            $this->set('order', $orderColumnName . ' ' . strtoupper($orderDirection));
        }
        if (isset($this->attributes['data'])) {
            $dataJson = $this->attributes['data'];
            $data = json_decode($dataJson, true);
            unset($this->attributes['data']);
            $affiliate = [];
            foreach ($data as $key => $value) {
                $name = array_get($value, 'name', NULL);
                $value = array_get($value, 'name', NULL);
                if (empty($name) || empty($value)) {
                    continue;
                }
                $this->set($name, $value);
                if ($name == 'affiliate') {
                    array_push($affiliate, $value);
                }
            }
        }
        if (isset($this->affiliate['download'])) {
            
        }
        !empty($affiliate) && $this->set('affiliate', $affiliate);
        $this->isEmpty('download') && $this->set('download', 0);
        $this->isEmpty('offset') && $this->set('offset', 1);
        $this->isEmpty('limit') && $this->set('limit', 20);
    }
}

