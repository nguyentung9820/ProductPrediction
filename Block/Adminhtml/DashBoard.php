<?php
namespace Magenest\ProductPrediction\Block\Adminhtml;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Model\Session;
use Phpml\Association\Apriori;
use Magenest\ProductPrediction\Model\ResourceModel\CustomerHistory\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Renaldy\PhpFPGrowth\FPGrowth;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;

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
     * @var Csv
     */
    protected $csv;

    protected $directoryList;

    /**
     * @param Template\Context $context
     * @param Session $session
     * @param ProductRepository $productRepository
     * @param Json $json
     * @param CollectionFactory $collectionFactory
     * @param Csv $csv
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        ProductRepository $productRepository,
        Json $json,
        CollectionFactory $collectionFactory,
        Csv $csv,
        DirectoryList $directoryList,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->session = $session;
        $this->json = $json;
        $this->collection = $collectionFactory;
        $this->csv = $csv;
        $this->directoryList = $directoryList;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFpGrowthSampleData() {
        $transactions = $this->getSampleData();
        $support = 0.2;
        $confidence = 0.2;

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
     * @throws \Exception
     */
    public function getAprioriSampleData() {
        $transactions = $this->getSampleData();
        $associator = new Apriori($support = 0.2, $confidence = 0.2);
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

    /**
     * @return array
     * @throws \Exception
     */
    public function getSampleData() {
        $users = [
            584885452,568821415,584898150,528706572,576852533
        ];
        $file = $this->directoryList->getRoot()."/app/code/Magenest/ProductPrediction/sample.csv";
        $this->csv->setDelimiter(',');
        $rows = $this->csv->getData($file);
        $header = array_shift($rows);
        $transactions = [];
        foreach ($rows as $row) {
            if (isset($row[2]) && $row[7] && in_array($row[7], $users)){
                $transactions[$row[7]][] = $row[2];
            }
        }
        return array_values($transactions);
    }
}
