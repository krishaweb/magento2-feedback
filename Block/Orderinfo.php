<?php
namespace Krishaweb\Feedback\Block;


use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Address;

/**
 * Class Index
 * @package Panda\CustomQuote\Block
 */
class Orderinfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_categoryObject;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $_categoryRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_address;

    /**
     * Index constructor.
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\AddressFactory $address
     * @param \Magento\Catalog\Model\Category $categoryObject
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\AddressFactory $address,
        \Magento\Sales\Model\Order $order,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Sales\Model\Order\Item $orderitem,

        array $data = [])
    {
        $this->_categoryFactory = $categoryFactory;
        $this->order = $order;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->product = $product;
        $this->_categoryRepository = $categoryRepository;
        $this->customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        $this->_address = $address;
        $this->orderitem = $orderitem;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function getOrderData($order_id){
        $order=$this->order->load($order_id);
        $items = $order->getItems();
        $productData = array();
        foreach($items as $item){
            $product_id = $item->getProductId();
            $product = $this->product->load($product_id);
            //$product = $this->_productRepositoryFactory->create()->getById($product_id);
            $productData[$item->getId()]['product_id'] = $product_id;
            $productData[$item->getId()]['item_id'] = $item->getId();
            $productData[$item->getId()]['order_id'] = $item->getOrderId();
            $productData[$item->getId()]['product_name'] = $product->getName(); 
            $productData[$item->getId()]['feedback_rating'] = $item->getFeedbackRating(); 
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $imagehelper = $objectManager->create('Magento\Catalog\Helper\Image');
            $image = $imagehelper->init($product,'category_page_list')->constrainOnly(FALSE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(75)->getUrl();
            $productData[$item->getId()]['product_image'] = $image; 
            
        }
        return $productData;
    }
    public function getOrderItem($item_id){
        $ordrItem = $this->orderitem->load($item_id);
        return $ordrItem;
    }

}