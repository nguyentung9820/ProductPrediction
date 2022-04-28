<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\ProductPrediction\Controller\Adminhtml\Service;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class DashBoard extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('DashBoard'));

        return $resultPage;
    }

}
