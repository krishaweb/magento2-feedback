<?php

namespace Krishaweb\Feedback\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

class Sendremaindermail{

	const XML_PATH_BACKUP_ENABLED = 'system/backup/enabled';

    const XML_PATH_BACKUP_TYPE = 'system/backup/type';

    const XML_PATH_BACKUP_MAINTENANCE_MODE = 'system/backup/maintenance';

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Backup data
     *
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backupData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Backup\Factory
     */
    protected $_backupFactory;

    /**
     * @var \Magento\Framework\App\MaintenanceMode
     */
    protected $maintenanceMode;

    /**
     * @param \Magento\Backup\Helper\Data $backupData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Backup\Factory $backupFactory
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     */
    public function __construct(
        \Magento\Backup\Helper\Data $backupData,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Backup\Factory $backupFactory,
        \Magento\Framework\App\MaintenanceMode $maintenanceMode,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->_backupData = $backupData;
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_filesystem = $filesystem;
        $this->_backupFactory = $backupFactory;
        $this->maintenanceMode = $maintenanceMode;
        $this->storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }


	public function execute()
    {
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