<?php
namespace App\Http\Logic\DealDataLogic;
use App\Http\Entity\BasicEntity;
use App\Model\StatisAffiliateBr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:24
 */
class DealBasicDataLogic
{
    protected $query;
    protected $startDate;
    protected $endDate;
    protected $dateType = 1;//1:createDate;2:clickDate
    protected $site;
    protected $exceptSite;

    //允许统计方式
    protected $allowCalType = array();
    //查询条件
    protected $filter = array();

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
        'CLS' => 'clicks',
        'ROB' => 'clicks_robot',
        'ROP' => 'clicks_robot_p',
    );
    //统计字段名称2
    protected $cal2Keys = array(
        'ORD' => 'orders',
        'SAL' => 'sales',
        'CMS' => 'revenues',
        'SRV' => 'showrevenues',
    );
    protected $calKeyFields;
    //统计前缀
    private $calPreKey;


    public function __construct($entity)
    {
        if ($entity instanceof BasicEntity)
        {
            //设置查询日期类型;
            !$entity->isEmpty('dateType') ? $this->setDateType($entity->dateType) : $this->setDateType(1);
            //设置开始时间
            !$entity->isEmpty('startDate') && $this->setStartDate($entity->startDate);
            //设置结束时间
            !$entity->isEmpty('endDate') && $this->setEndDate($entity->endDate);
            //设置site
            !$entity->isEmpty('site') && $this->setSite($entity->site);
            //设置exceptSite
            !$entity->isEmpty('exceptSite') && $this->setExceptSite($entity->exceptSite);
        }
    }

    protected function doInitCalPreKey()
    {
        $this->calPreKey = $this->dateType != 2 ? '' : 'c_';
    }

    protected function doInitCalKeyFields()
    {
        $calKeyFieldsArray = array();
        foreach ($this->cal1Keys as $calKey => $calValue) {
            array_push($calKeyFieldsArray, 'SUM(`' . $calValue . '`) AS `' . $calKey . '`');
        }
        foreach ($this->cal2Keys as $calKey => $calValue) {
            array_push($calKeyFieldsArray, 'SUM(`' . $this->calPreKey . $calValue . '`) AS `' . $calKey . '`');
        }
        if (empty($calKeyFieldsArray)) {
            return false;
        }
        $this->calKeyFields = implode(',', $calKeyFieldsArray);

        return true;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return int
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * @param int $dateType
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
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
     * @return mixed
     */
    public function getExceptSite()
    {
        return $this->exceptSite;
    }

    /**
     * @param mixed $exceptSite
     */
    public function setExceptSite($exceptSite)
    {
        $this->exceptSite = $exceptSite;
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


    public function checkCalType($type)
    {
        if (!in_array($type, array_keys($this->allowCalType)))
        {
            return false;
        }
        
        return true;
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
    

    public function getFilterCondition()
    {
        if ($this->query instanceof Builder) {
            if (!empty($startDate)) {
                $this->query->where('createddate','>=',$startDate);
            }
            $startDate = $this->getStartDate();
            if (!empty($startDate)) {
                $this->query->where('createddate','>=',$startDate);
            }

            $endDate = $this->getEndDate();
            if (!empty($endDate)) {
                $this->query->where('createddate','<=',$endDate);
            }

            $sites = $this->getSite();
            if (!empty($sites)) {
                if (!is_array($sites)) {
                    $sites = array($sites);
                }
                $this->query->whereIn('site',$sites);
            }

            $exceptSites = $this->getExceptSite();
            if (!empty($exceptSites)) {
                if (!is_array($exceptSites)) {
                    $exceptSites = array($exceptSites);
                }
                $this->query->whereNotIn('site',$exceptSites);
            }
        }
    }


    public function getCalculationData($type = '')
    {
        $this->getFilterCondition();
        $this->doInitCalPreKey();
        $this->doInitCalKeyFields();
        if (!$this->checkCalType($type)) {
            return false;
        }
        if (!$this->query instanceof Builder) {
            return false;
        }
        if (!empty($this->calKeyFields)) {
            $this->query->select($this->allowCalType[$type],DB::raw($this->calKeyFields));
        }

        $this->query->groupBy($this->allowCalType[$type]);

        $pageable = $this->isPageable();
        if (!empty($pageable)) {
            $this->query->take($this->getLimit())->skip($this->getOffset() * $this->getLimit());
        }
 
        return $this->query->get();
    }
}