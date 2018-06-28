<?php

namespace Krishaweb\Feedback\Controller\Feedback;

class Sendmail extends \Magento\Framework\App\Action\Action
{
	protected $storeManager;
	protected $transportBuilder;
	protected $inlineTranslation;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		array $data = []
	){
		parent::__construct($context, $data);
		$this->storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_scopeConfig = $scopeConfig;

        $this->order = $order;
	}
	public function execute(){
		

		die('in feedback execute');
		//get all orders for remainder

		$fromEmail = $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$fromName = $this->_scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

		$statusToRemind = $this->_scopeConfig->getValue('feedback/general/status_to_cron', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$dayToStartRemind = $this->_scopeConfig->getValue('feedback/general/days_to_start', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$from = array('email' => $fromEmail, 'name' => $fromName);
		
		$orderCollection = $this->_orderCollectionFactory->create()->addFieldToFilter('status', $statusToRemind);
		$ordersToRemaind = array();
		foreach ($orderCollection as $key => $order) {
			$orderDate = $order->getCreatedAt();
			$today = date("d/m/Y");
			$diff = abs(strtotime($today) - strtotime($orderDate));
			$years = floor($diff / (365*60*60*24));
			$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
			if($days >= $dayToStartRemind){
				foreach ($order->getItems() as $key => $item) {
					if($item->getFeedbackRating() == NULL || $item->getFeedbackRating() == ""){
						$ordersToRemaind[] = $order->getId();
					}
				}
			}
		}

		foreach ($ordersToRemaind as $key => $orderId) {
			
			

			$order = $this->order->load($orderId);
			$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
			$templateVars = array(
	            'store' => $this->storeManager->getStore(),
	            'order' => $order,
	        );
			
			$this->inlineTranslation->suspend();
			$to = array('sanket@localhost');
			/*$to = array($order->getCustomerEmail());*/
			$transport = $this->_transportBuilder->setTemplateIdentifier('feedback_remainder')
			                ->setTemplateOptions($templateOptions)
			                ->setTemplateVars($templateVars)
			                ->setFrom($from)
			                ->addTo($to)
			                ->getTransport();
			$transport->sendMessage();
			$this->inlineTranslation->resume();
		}
	}
}