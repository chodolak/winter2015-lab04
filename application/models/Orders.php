<?php

/**
 * Data access wrapper for "orders" table.
 *
 * @author jim
 */
class Orders extends MY_Model {

    // constructor
    function __construct() {
        parent::__construct('orders', 'num');
    }

    // add an item to an order
    function add_item($num, $code) {
        //If the list isn't empty then it adds it to the list
        //else it creates a new list
        if (($item = $this->orderitems->get($num, $code)) != null)
        {
            $item->quantity += 1;
            $this->orderitems->update($item);
        }
        else
        {
            $item = $this->orderitems->create();
            $item->order = $num;
            $item->item = $code;
            $item->quantity = 1;
            
            $this->orderitems->add($item);
        }
    }

    // calculate the total for an order
    function total($num) {
        //Gets the order
        $orderitems = $this->orderitems->some('order', $num);
        
        $total = 0.0;
        
        //Goes through the entire order adding up the total
        foreach($orderitems as $item)
        {
            $total += $item->quantity * $this->menu->get($item->item)->price;
        }
        
        return $total;
    }

    // retrieve the details for an order
    function details($num) {
        
    }

    // cancel an order
    function flush($num) {
        
    }

    // validate an order
    // it must have at least one item from each category
    function validate($num) {
        
        $items = $this->orderitems->group($num);
        $gotem = array();
        
        //Goes through all items and checks the catagory.
        //Sets the catagory to 1 if it has it.
        if (count($items) > 0)
        {
            foreach ($items as $item)
            {
                $menu = $this->menu->get($item->item);
                $gotem[$menu->category] = 1;
            }
        }
        
        //Returns true if all the catagories have a 1
        //else it returns false.
        return isset($gotem['m']) && isset($gotem['d']) && isset($gotem['s']);
    }

}
