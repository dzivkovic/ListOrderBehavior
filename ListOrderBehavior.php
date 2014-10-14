<?php
/**
 * Created by PhpStorm.
 * User: Dragan Zivkovic <dzivkovic> <dragan.zivkovic.ts@gmail.com>
 * Date: 20.6.13.
 * Time: 19.28
 */

/**
 * ListOrderBehavior class file.
 *
 * @author Dragan Zivkovic <dragan.zivkovic.ts@gmail.com>
 *
 */

/**
 * ListOrderBehavior will automatically fill models list_order field to next one or if list_order is
 * already set it will update all records with larger list_order field to +1
 * If $filterAttribute is not empty, Owner class will be filtered by filterAttribute(s)
 *
 * You may specify an active record model to use this behavior like so:
 * <pre>
 * public function behaviors(){
 * 	    return [
 * 		    'ListOrderBehavior' => [
 * 			    'class' => 'path.to.ListOrderBehavior',
 * 			    'listOrderAttribute' => 'list_order_attributeName',
 * 			    'filterAttributes' => 'filter attribute names COMMA separated',
 * 		    ]
 * 	];
 * }
 * </pre>
 *
 * @author Dragan Zivkovic <dragan.zivkovic.ts@gmail.com>
 */
    class ListOrderBehavior extends CActiveRecordBehavior
    {

        /**
         * actual field name that holds list order info
         * @var string
         */
        public $listOrderAttribute = 'list_order';

        /**
         * attribute name to filer owners class, if more than
         * one separate them by COMMA
         * @var null | string
         */
        public $filterAttributes;

        /**
         * will hold models previous list_order
         * add in model class
         * @var integer
         */
        public $currentListOrder;


        public function beforeSave($event)
        {
            // if list_order is not set
            if(!isset($this->owner->{$this->listOrderAttribute}) || $this->owner->{$this->listOrderAttribute} == '')
            {
                if(!isset($this->filterAttributes)){
                    // filters not set finding record with biggest list order
                    $biggest = call_user_func([get_class($this->owner), 'model'])->find(['order'=>$this->listOrderAttribute.' DESC']);
                }else{
                    // filters are set forming array of filters
                    $filters = explode(',', $this->filterAttributes);
                    // initializing condition
                    $condition = '';
                    // populating condition
                    foreach($filters as $filter)
                    {
                        // trimming white space
                        $filter = trim($filter);
                        // adding filter to condition
                        if(is_null($this->owner->{$filter}))
                            $condition .= $filter .' IS NULL AND ';
                        else
                            $condition .= $filter . '=' . $this->owner->{$filter} .' AND ';
                    }
                    //removing last "AND"
                    $condition = substr($condition, 0, -4);
                    // finding record with biggest list_order, filtered by filters
                    $biggest = call_user_func([get_class($this->owner), 'model'])->find(['condition'=>$condition, 'order'=>$this->listOrderAttribute.' DESC']);
                }

                // record exist, owners list order is record->list_order + 1
                if($biggest)
                    $nextOrder = $biggest->{$this->listOrderAttribute} + 1;
                else
                    $nextOrder = 0; // no record found list_order is o
                // setting owners list_order value
                $this->owner->{$this->listOrderAttribute} = $nextOrder;

            } else {// owners list order is set
                // if list order is changed we need to update records with larger list_order
                if($this->owner->{$this->listOrderAttribute} != $this->currentListOrder){
                    // filters are not set, finding all records with list_order >= than owners list_order
                    if(!isset($this->filterAttributes)){
                        $largerOrderRecords = call_user_func([get_class($this->owner), 'model'])->findAll(['condition'=>$this->listOrderAttribute.' >='.$this->owner->{$this->listOrderAttribute}]);
                    }else{
                        // filters are set, forming filter array
                        $filters = explode(',', $this->filterAttributes);
                        // initializing condition, list_order should be >= than owners list_order
                        $condition = $this->listOrderAttribute.' >='.$this->owner->{$this->listOrderAttribute}.' AND ';
                        foreach($filters as $filter)
                        {
                            // trimming white space
                            $filter = trim($filter);
                            // adding filter to condition
                            if(is_null($this->owner->{$filter}))
                                $condition .= $filter .' IS NULL AND ';
                            else
                                $condition .= $filter . '=' . $this->owner->{$filter} .' AND ';
                        }
                        // removing last "AND"
                        $condition = substr($condition, 0, -4);
                        // finding all records with list_order >= than owners list_order, filtered by filters
                        $largerOrderRecords = call_user_func([get_class($this->owner), 'model'])->findAll(['condition'=>$condition, 'order'=>'list_order ASC']);
                    }

                    // if there are records with bigger list order than owners list order
                    //using owners list order as start for updating larger ones
                    $no = $this->owner->{$this->listOrderAttribute} + 1;
                    if(!empty($largerOrderRecords))
                    {
                        foreach ($largerOrderRecords as $row)
                        {
                            $row->{$this->listOrderAttribute} = $no;
                            $row->save();
                            $no ++;
                        }
                    }
                }
            }

            return parent::beforeSave($event);
        }

        public function afterFind($event){

            $this->currentListOrder = $this->owner->{$this->listOrderAttribute};

            return parent::afterFind($event);
        }

    }