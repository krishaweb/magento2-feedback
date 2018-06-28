<?php

namespace Krishaweb\Feedback\Controller\Feedback;

class Orderinfo extends \Magento\Framework\App\Action\Action
{
	protected $storeManager;
	
	protected $transportBuilder;
	
	protected $inlineTranslation;
	protected $resultPageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		array $data = []
	){
		parent::__construct($context, $data);
		$this->storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->resultPageFactory = $resultPageFactory;
       


	}
	public function execute(){
		$data = $this->getRequest()->getPost();
		$orderId = $data->orderdata;

		$resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__(' heading '));
		$block = $resultPage->getLayout()
        ->createBlock('Krishaweb\Feedback\Block\Orderinfo')
        ->setAttribute('order_id', $orderId)
        ->setTemplate('Krishaweb_Feedback::orderinfo.phtml')
        ->toHtml();
		
		$this->getResponse()->setBody($block);

	}
}