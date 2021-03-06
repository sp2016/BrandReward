<?php
namespace App\Http\Entity\Store;
use App\Http\Entity\BasicEntity;

/**
 * Class StoreEntity
 * @package App\Http\Entity\Store
 * @property  array  $storeIds
 * @property  string $name
 * @property  array  $categories
 * @property  string $logoStatus
 * @property  string $ppcStatus
 * @property  string $categoryStatus
 * @property  string $nameStatus
 * @property  string $currentStatus
 * @property  array  $shippingCountry
 * @property  array  $network  
 * @property  string $cooperationStatus
 * @property  string  $display
 * @property  integer $offset
 * @property  integer $limit
 * @property  array   $order
 * @property  boolean $paginate
 */
class StoreEntity extends BasicEntity
{
    protected static $validatorRules = [
        'storeIds' => 'array',
        'name' => 'string',
        'categories' => 'array',
        'logoStatus' => 'string|in:YES,NO',
        'ppcStatus' => 'string|in:PPCAllowed,Mixed,NotAllow,UNKNOWN',
        'categoryStatus' => 'string|in:YES,NO',
        'display' => 'string|in:Active,Clicks',
        'nameStatus' => 'string|in:YES,NO',
        'currentStatus' => 'string|in:Content,Promotion,All,Mixed',
        'shippingCountry' => 'array',
        'network' => 'array',
        'cooperationStatus' => 'string|in:YES,NO'
    ];

    protected $customizeMessages = [
        'required' => ':attribute不能为空',
        'string' => ':attribute必须为字符串'
    ];
    protected $customizeAttributes = ['name' => '商家名称'];

    public function _initialize()
    {
        if ($this->isEmpty('paginate')) {
            $this->set('paginate', true);
            $this->isEmpty('offset') && $this->set('offset',0);
            $this->isEmpty('limit') && $this->set('limit',10);
        }
        if (!$this->isEmpty('shippingCountry')) {
            $shippingCountry = array();
            foreach ($this->shippingCountry as $country) {
                !empty($country) && array_push($shippingCountry, $country);
            }
            unset($this->shippingCountry);
            !empty($shippingCountry) && $this->shippingCountry = $shippingCountry;
        }
        if (!$this->isEmpty('network')) {
            $networks = array();
            foreach ($this->network as $network) {
                !empty($network) && array_push($networks, $network);
            }
            unset($this->network);
            !empty($networks) && $this->network = $networks;
        }
        //处理categories
        $this->validate();
    }
}