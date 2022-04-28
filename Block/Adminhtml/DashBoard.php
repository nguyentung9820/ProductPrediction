<?php
namespace Magenest\ProductPrediction\Block\Adminhtml;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Model\Session;
use Phpml\Association\Apriori;
use Magenest\ProductPrediction\Model\ResourceModel\CustomerHistory\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Renaldy\PhpFPGrowth\FPGrowth;

class DashBoard extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param Template\Context $context
     * @param Session $session
     * @param ProductRepository $productRepository
     * @param Json $json
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        ProductRepository $productRepository,
        Json $json,
        CollectionFactory $collectionFactory,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->session = $session;
        $this->json = $json;
        $this->collection = $collectionFactory;
    }

    /**
     * @return array
     */
    public function getFpGrowthSampleData() {
        $transactions = [
            ['banh my', 'trung'],
            ['trung', 'sua', 'dau an', 'hoodie', 'quan'],
            ['banh my', 'sua', 'dau an', 'thit', 'ao'],
            ['banh my', 'dau an', 'thit'],
            ['trung', 'banh my', 'phu kien', 'sua'],
            ['sua', 'dau an', 'banh my', 'trung'],
            ['banh my', 'ca'],
            ['trung', 'sua', 'banh my'],
            ['banh my', 'trung', 'dau an'],
            ['trung', 'sua', 'thit'],
        ];;
        $support = 0.3;
        $confidence = 0.75;
        $fpgrowth = new FPGrowth($transactions, $support, $confidence);
        $start = microtime(true);
        $fpgrowth->run();
        $time_elapsed_secs = microtime(true) - $start;

        return [
            'time_execute' => $time_elapsed_secs,
            'rules' => $fpgrowth->getRules(),
            'memories' => memory_get_usage()
        ];
    }

    /**
     * @return array
     */
    public function getAprioriSampleData() {
        $transactions = [
            ['banh my', 'trung'],
            ['trung', 'sua', 'dau an', 'hoodie', 'quan'],
            ['banh my', 'sua', 'dau an', 'thit', 'ao'],
            ['banh my', 'dau an', 'thit'],
            ['trung', 'banh my', 'phu kien', 'sua'],
            ['sua', 'dau an', 'banh my', 'trung'],
            ['banh my', 'ca'],
            ['trung', 'sua', 'banh my'],
            ['banh my', 'trung', 'dau an'],
            ['trung', 'sua', 'thit'],
        ];
        $associator = new Apriori($support = 0.3, $confidence = 0.75);
        $start = microtime(true);
        $associator->train($transactions,[]);
        $associator->predict($transactions);

        $time_elapsed_secs = microtime(true) - $start;
        return [
            'time_execute' => $time_elapsed_secs,
            'rules' => $associator->getRules(),
            'memories' => memory_get_usage()
        ];

    }
}
