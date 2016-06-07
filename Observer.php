<?php
class Eshop_PromoNoIndex_Model_Observer 
{

    public function CategoryNoIndex(Varien_Event_Observer $observer)
    {   
        $category = $observer->getEvent()->getCategory(); 
        $ParentCategory = $category->getParentCategory()->getId(); //get parent of category

        if($ParentCategory == 3)
        {   
            $noIndex = '<reference name="head"><action method="setRobots"><meta>NOINDEX,NOFOLLOW</meta></action></reference>';
                if($category->getCustomLayoutUpdate()!= $noIndex)
                {
                    $category->setCustomLayoutUpdate($noIndex)->save();
                }   
        }  
        
    }

}
