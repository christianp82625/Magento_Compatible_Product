<?php
    
/**
 * Shopping cart controller
 */
require_once 'Mage/Checkout/controllers/CartController.php';

class Georgi_Cart_CartController extends Mage_Checkout_CartController
{
    protected $_cookieCheckActions = array('add');


  

    /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {
        
        
        $productId=$this->getRequest()->getParam('product');
        
        $_product= Mage::getModel('catalog/product')->load($productId);        
        
        // check it has compatible_products attribute
        
        $compatible_sku= $_product->getData('compatible_product');
        
        if ($compatible_sku==null || $compatible_sku=="") {
             parent::addAction();
             return; 
        }
           
        

        $compatible_product_id = Mage::getModel("catalog/product")->getIdBySku( $compatible_sku);
                    
          
         if (!$compatible_product_id){
             parent::addAction();
             return;
         }
            
        $cart = Mage::getModel("checkout/cart");
        
        $compatible_product = Mage::getModel('catalog/product')->load($compatible_product_id);
                
        if (sizeof($compatible_product->getOptions())==0){
             parent::addAction();
             return;
        }    
        
        
        $optionId=null;
        
        foreach ($compatible_product->getOptions() as $o) {
          
            
            $optionTitle = $o->getTitle();
            

            if ($optionTitle == 'original_product') {
                  $optionId=$o->getId();                  
                  
            }
            
        }
        if ($optionId==null){
             parent::addAction();
             return;
        }
        
        
         $params = array(
            'product' => $compatible_product_id, // This would be $product->getId()
            'qty' => 1,
            'options' => array(
                $optionId =>  $_product->getSku()              
            )
        );

        
        $cart->addProduct($compatible_product, $params); 
        $cart->save();
   
       $this->_getSession()->setCartWasUpdated(true);

        /**
         * @Normal Dispatch
         */
        Mage::dispatchEvent('checkout_cart_add_product_complete',
            array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
        );

        if (!$this->_getSession()->getNoCartRedirect(true)) {
            if (!$cart->getQuote()->getHasError()) {
                $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($compatible_product->getName()));
                $this->_getSession()->addSuccess($message);
            }
            $this->_goBack();
        }
        
        
        
    }
        
   
}