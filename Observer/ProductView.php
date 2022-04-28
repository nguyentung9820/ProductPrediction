<?php

namespace Magenest\ProductPrediction\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magenest\ProductPrediction\Logger\ProductPrediction;
use Magento\Setup\Exception;
use Magenest\ProductPrediction\Model\CustomerHistoryFactory;
use Magenest\ProductPrediction\Model\ResourceModel\CustomerHistory\CollectionFactory;
use Magenest\ProductPrediction\Model\ResourceModel\CustomerHistory as CustomerHistoryResource;
use Magento\Framework\Serialize\Serializer\Json;

class ProductView implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var CustomerHistoryFactory
     */
    protected $model;

    /**
     * @var ProductPrediction
     */
    protected $logger;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CustomerHistoryResource
     */
    protected $customerHistoryResource;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param CollectionFactory $collection
     * @param ProductPrediction $logger
     * @param Session $session
     * @param CustomerHistoryFactory $model
     * @param CustomerHistoryResource $customerHistoryResource
     * @param Json $json
     */
    public function __construct(
        CollectionFactory       $collection,
        ProductPrediction       $logger,
        Session                 $session,
        CustomerHistoryFactory  $model,
        CustomerHistoryResource $customerHistoryResource,
        Json                    $json
    ){
        $this->collection       = $collection;
        $this->model            = $model;
        $this->logger           = $logger;
        $this->session          = $session;
        $this->customerHistoryResource = $customerHistoryResource;
        $this->json             = $json;
    }


    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $data = $observer->getData('product');
        $productId = $data->getData('sku');
        $user = $this->session->getCustomerData();
        if(!empty($user)){
            $userId = $user->getId();
            $customerHistoryModel = $this->model->create();
            $collection = $this->collection->create();

            // add or update customer history
            if(!empty($collection->getData())){
                foreach($collection as $item) {
                    if($item->getData('user_id') == $userId){
                        $id = $item->getData('id');
                        $this->customerHistoryResource->load($customerHistoryModel, $id);
                        $products = $this->json->unserialize($item->getData('product_ids'));
                        if(!in_array($productId, $products)){
                            $products[] = $productId;
                            $customerHistoryModel->setData('product_ids', $this->json->serialize($products));
                        }
                    }else{
                        $customerHistoryModel = $this->addNewRecord($customerHistoryModel, $productId, $userId);
                    }
                }
            } else {
                $customerHistoryModel = $this->addNewRecord($customerHistoryModel, $productId, $userId);
            }

            try {
                $this->customerHistoryResource->save($customerHistoryModel);
                $debug = [
                    'Product Data' =>
                        [
                            'Product ID' => $productId,
                            'Product SKU' => $data->getData('sku'),
                            'Product name' => $data->getData('name'),
                            'Price' => $data->getData('price'),
                            'Category' => $data->getCategory() ? $data->getCategory()->getData('name') : null
                        ],
                    'User Data' =>
                        [
                            'User ID' => $user->getId(),
                            'User Name' => $user->getFirstname().' '.$user->getLastname(),
                            'Email' => $user->getEmail(),
                            'Gender' => $user->getGender(),
                            'Customer Group' => $this->session->getCustomerGroupId()
                        ]
                ];

                $this->logger->debug(print_r('Customer '. $user->getId() . ' view product', true));
                $this->logger->debug(print_r($debug, true));

            } catch (\Exception $e) {
                $error = [
                    'Message' => $e
                ];
                $this->logger->debug(print_r($error, true));
                throw new Exception;
            }
        }
    }

    /**
     * @param $model
     * @param $productId
     * @param $userId
     * @return mixed
     */
    public function addNewRecord($model, $productId, $userId) {
        $result = [
            'user_id' => $userId,
            'product_ids' => $this->json->serialize([$productId])
        ];
        $model->addData($result);
        return $model;
    }
}
