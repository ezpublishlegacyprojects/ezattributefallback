<?php

/*!
  \class   eZAttributeFallbackOperator ezattributefallbackoperator.php
  \ingroup eZTemplateOperators
  \brief   Handles template operator ezattributefallback. By using ezattributefallback you can get similar attribute from parent nodes.
  \version 0.5
  \date    Saturdat 31 January 2009 12:59:27
  \author  Bartek Modzelewski

  

  Example 1:
\code
{$node.data_map.banner|ezattributefallback()}
\endcode

  Example 2:
\code
{$node.data_map.banner|ezattributefallback( 2, null, array( 'banner', 'top_image' ) )}
\endcode

  Example 3:
\code
{ezattributefallback( false(), $node.node_id, array( 'banner' ) )}
\endcode

**/



class eZAttributeFallback
{
    /*!
      Constructor, does nothing by default.
    */
    function eZAttributeFallback()
    {
    }

    /*!
     \return an array with the template operator name.
    */
    function operatorList()
    {
        return array( 'ezattributefallback' );
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type,
             this is needed for operator classes that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    /*!
     See eZTemplateOperator::namedParameterList
    */
    function namedParameterList()
    {
        return array( 'ezattributefallback' => array( 'max_parents' => array( 'type' => 'integer',
                                                                              'required' => false,
                                                                              'default' => null ),
                                                      'node_id' => array( 'type' => 'integer',
                                                                              'required' => false,
                                                                              'default' => null ),
                                                      'attribute_identifiers' => array( 'type' => 'array',
					                                                                                'required' => false,
					                                                                                'default' => null ) ) );
	}


    /*!
     Executes the PHP function for the operator cleanup and modifies \a $operatorValue.
    */
    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement )
    {
      
        $maxParents			= $namedParameters['max_parents'];
        $nodeID 			= $namedParameters['node_id'];
        $attributeIdents	= $namedParameters['attribute_identifiers']; 
        // Example code. This code must be modified to do what the operator should do. Currently it only trims text.
        switch ( $operatorName )
        {
            case 'ezattributefallback':
            {
                if ( !is_object( $operatorValue ) )
                {
                	if ( $nodeID === null || empty( $attributeIdents ) )
                	{
                		break;
                	}
                }
                elseif ( $operatorValue->attribute( 'has_content' ) )
                {
                	// current attribute is ok, no need to continue search
                	break;
                }

                // find current node
                if ( $nodeID === null ) // if nodeID is not passed from tpl, find from passed attribute
                {
	                // attribute identifier to find
	                $attributeIdentArray = array( $operatorValue->attribute( 'contentclass_attribute_identifier' ) );
	                // fetch object
	                $contentObject = eZContentObject::fetch( $operatorValue->attribute( 'contentobject_id' ) );
	                // find main node, not necessary the proper one !!!
	                $node = $contentObject->attribute( 'main_node' );
                }
                else
                {
                	if ( is_object( $operatorValue ) )
                	{
                		$attributeIdentArray = array( $operatorValue->attribute( 'contentclass_attribute_identifier' ) );
                	}
                	else
                	{
                		$attributeIdentArray = $attributeIdents; // array passed from tpl
                	}
                	
                	$node = eZContentObjectTreeNode::fetch( $nodeID );
                }

                // if node can't be found, return null
                if ( !is_object( $node ) )
				{
					$operatorValue = null;
					break;
				}
                
                // get subtree path
                $path = $node->attribute( 'path' );
                $dataMap = $node->dataMap();
                $parentCounter = 0;
                
                // check current node for attributes
                foreach( $attributeIdentArray as $attributeIdent )
            	{
            		if ( is_object( $dataMap[$attributeIdent] ) && $dataMap[$attributeIdent]->attribute( 'has_content' )  )
            		{
						$operatorValue = $dataMap[$attributeIdent];
	                	break 2; // break foreach attrib, case 'ezattributefallback'	            			
            		}
            	}
                
                // loop subtree
                foreach ( array_reverse( $path ) as $parentNode )
                {
                	if ( $maxParents != null && $parentCounter >= $maxParents )
                	{
                		$operatorValue = null;
                		break 2; // break foreach parent, case 'ezattributefallback'		
                	}
                	//if ( $parentNode )
                	$dataMap = $parentNode->dataMap();
                	foreach ( $attributeIdentArray as $attributeIdent )
                	{
	                	//echo $attributeIdent;
	                	if ( is_object( $dataMap[$attributeIdent] ) && $dataMap[$attributeIdent]->attribute( 'has_content' ) )
	                	{
	                		$operatorValue = $dataMap[$attributeIdent];
	                		break 3; // break foreach attrib, foreach parent, case 'ezattributefallback'
	                	}
                	}
                	$parentCounter++;
                }

            } break;
        }
    }
}

?>