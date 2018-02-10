<?php
namespace App\Http\Entity\Publisher;

use App\Http\Entity\Entity;

/**
 * Class PublisherAccountEntity
 * @package App\Http\Entity\Publisher
 * @property integer $ID
 * @property integer $PublisherId 发布者ID
 * @property string  $SiteTypeNew 发布者内容类型集合
 * @property string  $SiteOption  发布者内容类型
 */
class PublisherAccountEntity extends Entity
{
    public function checkSiteType()
    {
        $codeTypeArray = array("1_e","2_e");
        $siteTypeNew = $this->SiteTypeNew;
        $siteTypes = explode('+', $siteTypeNew);
        $siteOption = !empty($siteTypes) && !empty($siteTypeNew) ? 'Content' : 'None';
        foreach ($siteTypes as $siteType){
            if (empty($siteType)) {
                continue;
            }
            if (in_array($siteType, $codeTypeArray)) {
                $siteOption = 'Promotion';
            }
        }
        return $this->SiteOption != $siteOption ? false : true;
    }
}