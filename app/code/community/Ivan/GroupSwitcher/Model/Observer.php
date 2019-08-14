<?php
class Ivan_GroupSwitcher_Model_Observer
{	
	/**
	 *发票事件监听方法 
	 */
	public function switcherCustomer($observer){
		
		$enable=Mage::getStoreConfig('groupswitcher_options/messages/enable');
		if($enable){
			$event = $observer->getEvent();
			$invoice= $event->getInvoice();
			$order=$invoice->getOrder();
			$this->_switcherCustomer($order->getCustomerId());
		}
		
	}
	
	/**
	 * 修改用户组
	 */
	public function _switcherCustomer($customerid){
		$configArray=$this->getConfig();
		$grandTotal=$this->getCustomerOrderTotal($customerid);
		$upGroupId=0;
		foreach($configArray as $key=>$config){
			$switcherArray=explode(':',$config);
			
			if($switcherArray && $switcherArray[1]){
				$moneyStep1=explode('-',$switcherArray[1]);
				if($moneyStep1){
					
					$groupStart=(int)$moneyStep1[0];
					$groupEnd=(int)$moneyStep1[1];
					if($grandTotal>$groupStart && $grandTotal<$groupEnd ){
						$upGroupId=$switcherArray[0];
						break;
					}
				}
			}
		}
		if($upGroupId!=0){
			$customer=Mage::getModel("customer/customer")->load($customerid);
			$customer->setGroupId($upGroupId);
			$customer->save();
		}
	}
	
	
	/**
	 *获得 配置信息
	 */
	public function getConfig(){
		$configArray=array();
		$configStr=Mage::getStoreConfig('groupswitcher_options/messages/groupcoupon');
		$configArray=explode(",",$configStr);
		return $configArray;
	}
	
	/**
	 * 根据客户id获得历史订单总额
	 */
	public function getCustomerOrderTotal($customerid){
		$grandTotalColl = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('grand_total')
            ->addFieldToFilter('customer_id', $customerid)
            ->addFieldToFilter('state', array('in' => array('processing','complete')))
            ->setOrder('created_at', 'desc')
        ;
        $grandTotal=0;
        foreach($grandTotalColl as $total){
        	$grandTotal+=$total['grand_total'];
        }
        return $grandTotal;
	}
}