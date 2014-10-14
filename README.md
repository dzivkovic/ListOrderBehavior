ListOrderBehavior
=================

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