<?php

namespace Magenest\ProductPrediction\Model;

use Magenest\ProductPrediction\Model\ResourceModel\CustomerHistory as ResourceModel;

class CustomerHistory extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
