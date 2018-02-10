<?php
namespace App\Http\Logic;

use App\Http\Entity\Publisher\PublisherAccountEntity;
use App\Model\Publisher;
use App\Model\PublisherAccount;

class PublisherLogic extends BasicLogic
{
    public function getPublisherList()
    {
        $model = new Publisher();
        $result = $model::query()->where('Status','Active')->get();
        $list = array();
        foreach ($result as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $id  = object_get($value, 'ID', 0);
            $name = object_get($value, 'Name', '');
            empty($name) && $name = object_get($value, 'Email', '');
            array_set($list, $name, ['ID' => $id, 'Name' => $name]);
        }
        ksort($list);
        return $list;
    }

    /**
     * 获取错误的SiteType与SiteTypeNew不一致的数据
     */
    public function getSiteTypeNonePublishers()
    {
        $pModel = new Publisher();
        $aModel = new PublisherAccount();
        $oKeys = array();
        $pKeys = $pModel::query()->where('Status','Active')->get();
        foreach ($pKeys as $pKey) {
            $id = object_get($pKey, 'ID');
            if (empty($id)) {
                continue;
            }
            $aKeys = $aModel::query()->where('PublisherId',$id)->get();
            foreach ($aKeys as $aKey) {
                if (empty($aKey)) {
                    continue;
                }
                $aKeyArray = $aKey->toArray();
                $entity = new PublisherAccountEntity($aKeyArray);
                $entity->merge(['Name' => object_get($pKey, 'Name')]);
                $entity->merge(['Manager' => object_get($pKey, 'Manager')]);
                $entity->merge(['Email' => object_get($pKey, 'Email')]);
                empty($entity->checkSiteType()) && array_set($oKeys, $id, $entity->toArray());
            }
        }
        
        return $oKeys;
    }

}