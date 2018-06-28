<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Krishaweb\Feedback\Controller\Adminhtml\System\Config\System\Feedback;

use Magento\Backend\App\Action\Context;


class Send extends \Magento\Backend\App\Action
{
    /**
     * Hello test controller page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected $storeManager;
    protected $transportBuilder;
    protected $inlineTranslation;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        array $data = []
    ){
        $this->storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->order = $order;
        $this->_jsonResultFactory = $jsonResultFactory;
        parent::__construct($context, $data);
    }

    public function execute(){

        
        try {
            
            $result = array();
            $result['status'] = 'fail';
            $resultF = $this->_jsonResultFactory->create();
            
            //get all orders for remainder
            $fromEmail = $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $fromName = $this->_scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $statusToRemind = $this->_scopeConfig->getValue('feedback/general/status_to_cron', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $dayToStartRemind = $this->_scopeConfig->getValue('feedback/general/days_to_start', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $from = array('email' => $fromEmail, 'name' => $fromName);
            
            $orderCollection = $this->_orderCollectionFactory->create()->addFieldToFilter('status', $statusToRemind);
            $ordersToRemaind = array();
            foreach ($orderCollection as $key => $order) {
                

                $datetime1 = date_create(date('Y-m-d'));
                
                $datetime2 = date_create(date('Y-m-d',strtotime($order->getCreatedAt())));
                $interval = date_diff($datetime2, $datetime1);
                $days = (int)$interval->format('%a');

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
                //$to = array('sanket@krishaweb.com');
                $to = array($order->getCustomerEmail());
                $transport = $this->_transportBuilder->setTemplateIdentifier('feedback_remainder')
                                ->setTemplateOptions($templateOptions)
                                ->setTemplateVars($templateVars)
                                ->setFrom($from)
                                ->addTo($to)
                                ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            }
            $result['status'] = 'success';
            $resultF->setData($result);
            return $resultF; 


        }
        catch(\Exception $e){
            $this->messageManager->addException($e, __('Something went wrong.'));
        }
    }
 
    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Krishaweb_Feedback::feedback');
    }
}
