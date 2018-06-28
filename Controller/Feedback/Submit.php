<?php

namespace Krishaweb\Feedback\Controller\Feedback;

class Submit extends \Magento\Framework\App\Action\Action
{
	protected $storeManager;
	protected $transportBuilder;
	protected $inlineTranslation;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Item $item,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
	){
		parent::__construct($context, $data);
		$this->storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_messageManager = $messageManager;
		$this->item = $item;
	}
	public function execute(){
		$data = $this->getRequest()->getPost();
		$result = array();
		try{
			$result['status'] = 'fail';
			foreach ($data->ratings as $key => $rate_item) {
				$itemObj = $this->item->load($key);	
				$itemObj->setFeedbackRating($rate_item);
				$itemObj->save();
				$result['status'] = 'success';
				//$result['message'] = 'Thanks For Your Feedback';
				$this->_messageManager->addSuccess(__("Thanks For Your Feedback"));

			}
		}catch (\Exception $e) {
			$result['status'] = 'fail';
			$result['message'] = $e->getMessage();
		    $this->_messageManager->addError(__($e->getMessage()));

		}
		$this->getResponse()->setBody(json_encode($result));
	}
}