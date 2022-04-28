<?php

namespace Magenest\ProductPrediction\Model\ResourceModel;

class CustomerHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('customer_history', 'id');
    }
}
