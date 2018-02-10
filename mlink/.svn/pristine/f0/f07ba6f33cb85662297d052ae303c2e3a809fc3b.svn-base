<?php
namespace App\Http\Logic\DealDataLogic;
use App\Http\Entity\BasicEntity;
use App\Model\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class DealStoreDataLogic
 * @package App\Http\Logic\DealDataLogic
 */
class DealStoreDataLogic
{
    private $domainId;
    private $storeId;
    private $query;
    protected $calKeyFields;

    //是否分页
    protected $pageable = false;
    //Pages
    protected $offset = 1;
    //Limit
    protected $limit = 20;
    //排序Key
    protected $orderKey;
    //排序方式：'ASC','DESC'
    protected $orderSeq;

    //统计字段名称1
    protected $cal1Keys = array(
        'CLS' => 'Clicks',
        'ROB' => 'Clicks_robot',
        'ROP' => 'Clicks_robot_p',
        'ORD' => 0,
        'SAL' => 'Sales',
        'CMS' => 'Commission',
        'SRV' => 0,
    );

    public function getCalculationData($type = 'STORE')
    {
        if ($type != 'STORE') {
            return false;
        }
        $this->getFilterCondition();
        $this->doInitCalKeyFields();
        if (!$this->query instanceof Builder) {
            return false;
        }
        if (!empty($this->calKeyFields)) {
            $this->query->select(DB::raw('`ID` AS `StoreId`'), DB::raw($this->calKeyFields));
        }

        $this->query->groupBy('ID');
        $pageable = $this->isPageable();
        if (!empty($pageable)) {
            $this->query->take($this->getLimit())->skip($this->getOffset() * $this->getLimit());
        }

        return $this->query->get();
    }
    
    public function __construct($entity)
    {
        $model = new Store();
        $this->query = $model::query();
        if ($entity instanceof BasicEntity)
        {
            //设置storeIds编号
            !$entity->isEmpty('storeIds') && $this->setStoreId($entity->storeIds);
            //设置domainIds编号(无storeIds备用方案)
            $entity->isEmpty('storeIds') && !$entity->isEmpty('domainIds') && $this->setDomainId($entity->domainIds);

        }
    }
    
    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId)
    {
        if (!is_array($storeId)) {
            $storeId = array($storeId);
        }
        $this->storeId = $storeId;
    }

    /**
     * @return mixed
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param mixed $domainId
     */
    public function setDomainId($domainId)
    {
        if (!is_array($domainId)) {
            $domainId = array($domainId);
        }
        $this->domainId = $domainId;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return boolean
     */
    public function isPageable()
    {
        return $this->pageable;
    }

    /**
     * @param boolean $pageable
     */
    public function setPageable($pageable)
    {
        $this->pageable = $pageable;
    }

    /**
     * @return mixed
     */
    public function getOrderKey()
    {
        return $this->orderKey;
    }

    /**
     * @param mixed $orderKey
     */
    public function setOrderKey($orderKey)
    {
        $this->orderKey = $orderKey;
    }

    /**
     * @return mixed
     */
    public function getOrderSeq()
    {
        return $this->orderSeq;
    }

    /**
     * @param mixed $orderSeq
     */
    public function setOrderSeq($orderSeq)
    {
        $this->orderSeq = $orderSeq;
    }




    public function getFilterCondition()
    {
        if ($this->query instanceof Builder) {
            $storeId = $this->getStoreId();
            if (!empty($storeId)) {
                $this->query->where(function ($query) use ($storeId) {
                    $sids = array_chunk($storeId, 100);
                    foreach ($sids as $sid) {
                        $query->orWhereIn('ID',$sid);
                    }
                });
            }
            $domainId = $this->getDomainId();
            if (!empty($domainId)) {
                $this->query->whereHas('domains', function ($query) use ($domainId) {
                    $dids = array_chunk($domainId, 100);
                    foreach ($dids as $did) {
                        $query->whereIn('DomainId',$did);
                    }
                });
            }
        }
    }


    protected function doInitCalKeyFields()
    {
        $calKeyFieldsArray = array();
        foreach ($this->cal1Keys as $calKey => $calValue) {
            array_push($calKeyFieldsArray, 'SUM(' . $calValue . ') AS `' . $calKey . '`');
        }

        if (empty($calKeyFieldsArray)) {
            return false;
        }
        $this->calKeyFields = implode(',', $calKeyFieldsArray);

        return true;
    }
}