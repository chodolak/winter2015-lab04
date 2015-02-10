<?php

/**
 * Order handler
 * 
 * Implement the different order handling usecases.
 * 
 * controllers/welcome.php
 *
 * ------------------------------------------------------------------------
 */
class Order extends Application {

    function __construct() {
        parent::__construct();
    }

    // start a new order
    function neworder() {
        $order_num = $this->orders->highest() + 1;
        $order = $this->orders->create();
        $order->num = $order_num;
        $order->date = date("y.m.d H:i:s");
        $order->status = "a";
        $this->orders->add($order);
        redirect('/order/display_menu/' . $order_num);
    }

    // add to an order
    function display_menu($order_num = null) {
        if ($order_num == null)
            redirect('/order/neworder');

        $this->data['pagebody'] = 'show_menu';
        $this->data['order_num'] = $order_num;
        
        $order = $this->orders->get($order_num);       
        $this->data['title'] = $order_num . " ($" . $this->orders->total($order_num) . ")";
        
        // Make the columns
        $this->data['meals'] = $this->make_column('m');
        $this->data['drinks'] = $this->make_column('d');
        $this->data['sweets'] = $this->make_column('s');
        
        foreach($this->data['sweets'] as &$item)
        {
            $item->order_num = $order_num;
        }
        
        foreach($this->data['drinks'] as &$item)
        {
            $item->order_num = $order_num;
        }
        
        foreach($this->data['meals'] as &$item)
        {
            $item->order_num = $order_num;
        }
        
        $this->render();
    }

    // make a menu ordering column
    function make_column($category) {
        return $this->menu->some('category', $category);
    }

    // add an item to an order
    function add($order_num, $item) {
        $this->orders->add_item($order_num, $item);
        redirect('/order/display_menu/' . $order_num);
    }

    // checkout
    function checkout($order_num) {
        $this->data['title'] = 'Checking Out';
        $this->data['pagebody'] = 'show_order';
        $this->data['order_num'] = $order_num;
        $this->data['total'] = "$" . $this->orders->total($order_num);
        
        //get the item list for that order
        $items = $this->orderitems->group($order_num);
        foreach($items as $item)
        {
            $menuitem = $this->menu->get($item->item);
            $item->code = $menuitem->name;
        }
        
        $this->data['items'] = $items;        
        
        //validate the items before render
        
        $this->data['okornot'] = $this->orders->validate($order_num) ? '' : 'disabled';
        $this->render();
    }

    function proceed($order_num) {
        //If cart isn't valide go back.
        if (!$this->orders->validate($order_num))
        {
            redirect('/order/display_menu/' . $order_num);
        }
        
        //else proceed
        $record = $this->orders->get($order_num);
        $record->date = date(DATE_ATOM);
        $record->status = 'c';
        $record->total = $this->orders->total($order_num);
        $this->orders->update($record);
        
        redirect('/');
    }

    // cancel the order
    function cancel($order_num) {
        
        $this->orderitems->delete_some($order_num);
        $record = $this->orders->get($order_num);
        $record->status = 'x';
        $this->orders->update($record);
        redirect('/');
    }

}
